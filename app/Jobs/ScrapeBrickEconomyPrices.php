<?php

namespace App\Jobs;

use App\Enums\PriceType;
use App\Models\Product;
use App\Traits\HasUserAgent;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeBrickEconomyPrices implements ShouldQueue
{
    use Queueable, HasUserAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(public $product_id, public $daily = false, public $historical = true)
    {
        //
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $product_id = $this->product_id;
        $product = Product::find($this->product_id);

        if (!$product)
            return;

        $url = "https://www.brickeconomy.com/{$product->product_type}/{$product->brickeconomy_id}/";

        try {
            // $response = Http::withCookies([
            //     'Region' => 'US',
            // ], 'www.brickeconomy.com')
            //     ->withHeaders([
            //         'User-Agent' => $this->userAgents[rand(0, count($this->userAgents) - 1)],
            //         'Accept' => 'text/html,application/xhtml+xml,application/xml',
            //         'Accept-Language' => 'en-US,en;q=0.9',
            //     ])
            //     ->get($url);

            $response = $this->proxyRequest()
                ->withCookies([
                    'Region' => 'US',
                ], 'www.brickeconomy.com')
                ->get($url);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
            $this->fail($e);
        }

        $html = $response->body();

        if (($this->daily ?? false)) {

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

        if (($this->historical ?? false)) {

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
