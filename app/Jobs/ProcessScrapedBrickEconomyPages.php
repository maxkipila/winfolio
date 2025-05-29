<?php

namespace App\Jobs;

use App\Enums\PriceType;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ProcessScrapedBrickEconomyPages implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $file_path, public $product_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $html = file_get_contents($this->file_path);
        $this->parsePrices($html, $this->product_id);
        $this->parseImages($html, $this->product_id);

        if (file_exists($this->file_path))
            unlink($this->file_path);
    }


    public function parseImages($html, $product_id)
    {
        $regex = '/<ul id="setmediagallery"[^>]*>(.*?)<\/ul>/s';
        // First, extract the content of the <ul> block
        if ($html && preg_match($regex, $html, $ulMatch)) {
            $ulContent = $ulMatch[1];
            // Now, extract all src attributes from the <img> tags within the <ul>
            $imgRegex = "/\\.attr\\('src',\\s*'([^']+)'\\)/";
            if (preg_match_all($imgRegex, $ulContent, $imgMatches)) {
                // Array of all src attributes
                $srcs = collect($imgMatches[1])->map(fn($url) => "https://www.brickeconomy.com{$url}");
                Log::info("Dispatching download of {$srcs->count()} images", ['urls' => $srcs]);
                DownloadProductImageJob::dispatch($product_id, $srcs)/* ->onQueue('images') */;
                return;
            }
        }

        $imgRegex = '/<img[^>]+id="image_modal_xlarge"[^>]+src="([^"]+)"[^>]*>/i';

        if ($html && preg_match($imgRegex, $html, $imgMatch)) {
            $src = $imgMatch[1];
            // Optionally prepend the domain if needed
            $fullUrl = str_starts_with($src, 'http') ? $src : "https://www.brickeconomy.com{$src}";
            Log::info("Dispatching download of modal image $fullUrl", ['url' => $fullUrl]);
            DownloadProductImageJob::dispatch($product_id, collect([$fullUrl]))/* ->onQueue('images') */;
        }
    }

    public function parsePrices($html, $product_id)
    {
        $product = Product::find($product_id);

        $has_daily = $product->prices()->where('type', PriceType::SCRAPED)->where('date', now()->format('Y-m-d'))->exists();
        $has_historical = $product->prices()->where('type', PriceType::AGGREGATED)->exists();

        if (!$has_daily) {
            $crawler = new Crawler($html);
            Log::info("product {$product_id}");

            if ($product->product_type == 'set') {

                // Find the div with the desired ID
                $pricingDiv = $crawler->filter('#ContentPlaceHolder1_PanelSetPricing');

                if ($pricingDiv->count() > 0) {
                    // Extract Retail price
                    $retailPrice = $pricingDiv->filter('.row.rowlist')->reduce(function (Crawler $node) {
                        return $node->filter('.col-xs-5.text-muted')->text('') === 'Retail price';
                    })->filter('.col-xs-7')?->text('');

                    // Extract Value under New/Sealed
                    $newSealedSection = $pricingDiv->filter('.semibold.bdr-b-l.pb-2')->reduce(function (Crawler $node) {
                        return stripos($node->text(''), 'New/Sealed') !== false;
                    });

                    if ($newSealedSection->count() > 0) {
                        $sealedValue = $newSealedSection->nextAll()->filter('.row.rowlist')->reduce(function (Crawler $node) {
                            return $node->filter('.col-xs-5.text-muted')->text('') === 'Value';
                        })->filter('.col-xs-7 b')->text('');

                        if ($sealedValue != '') {
                            $product->prices()->create([
                                'date' => now()->format('Y-m-d'),
                                'value' =>  (float) preg_replace('/[^\d.]/', '', $sealedValue),
                                'retail' => $retailPrice != '' ? ((float) preg_replace('/[^\d.]/', '', $retailPrice)) : NULL,
                                'currency' => 'USD',
                                'type' => PriceType::SCRAPED
                            ]);
                        }
                    }
                }
            } else if ($product->product_type == 'minifig') {
                $pricingDiv = $crawler->filter('#ContentPlaceHolder1_PanelMinifigPricing');

                if ($pricingDiv->count() > 0) {
                    // Extract the "Value" row
                    $value = $pricingDiv->filter('.row.rowlist')->reduce(function (Crawler $node) {
                        return stripos($node->filter('.col-xs-5.text-muted')->text(''), 'Value') !== false;
                    })->filter('.col-xs-7 b')->text('');

                    if ($value != '') {
                        $product->prices()->create([
                            'date' => now()->format('Y-m-d'),
                            'value' => (float) preg_replace('/[^\d.]/', '', $value),
                            'currency' => 'USD',
                            'type' => PriceType::SCRAPED
                        ]);
                    }
                }
            }
        }

        if (!$has_historical) {

            preg_match_all('/data\.addRows\(\[\s*(.*?)\s*\]\);/s', $html, $rowsMatches, PREG_SET_ORDER);
            $dataRows = '';

            foreach ($rowsMatches as $match) {
                $rowsContent = $match[1];
                if (strpos($rowsContent, "'Released'") !== false) {
                    $dataRows = $rowsContent;
                    break;
                }
            }

            if (!$dataRows && !empty($rowsMatches)) {
                $dataRows = $rowsMatches[0][1] ?? '';
            }

            if ($dataRows && preg_match_all('/\[new Date\((\d+),\s*(\d+),\s*(\d+)\),\s*([\d.]+)/s', $dataRows, $matches, PREG_SET_ORDER)) {
                $minYear = 1900;
                $maxYear = now()->year;
                foreach ($matches as $match) {
                    $year = (int)$match[1];
                    $month = (int)$match[2];
                    $day = (int)$match[3];
                    $price = (float)$match[4];

                    if ($year >= $minYear && $year <= $maxYear) {
                        $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                        $product->prices()->create([
                            'date' => $date,
                            'value' => $price,
                            'currency' => 'USD',
                            'type' => PriceType::AGGREGATED
                        ]);
                    }
                }
            }
        }
    }
}
