<?php

namespace App\Jobs;

use App\Models\Product;
use App\Traits\HasUserAgent;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeRebrickableForIDs implements ShouldQueue
{
    use Batchable, Queueable, HasUserAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(public $product_id)
    {
        //
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this?->batch()?->cancelled()) {
            return;
        }

        $product_id = $this->product_id;
        $product = Product::find($product_id);

        if (!$product || !$product->product_num) {
            Log::error("Product {$product_id} doesn't exist.", ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
            return;
        }

        $rebrickableId = $product->product_num;

        try {
            // Sestavení URL pro scraping
            $url = "https://rebrickable.com/minifigs/{$rebrickableId}/";

            try {
                $response = $this->proxyRequest()->get($url);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $this->fail($e);
            }

            $html = $response->body();

            $crawler = new Crawler($html);

            // Získání BrickLink ID
            $brickLinkId = $this->extractBrickLinkId($crawler);

            if ($brickLinkId) {
                // prevod na malá písmena
                $product->brickeconomy_id = strtolower($brickLinkId);
                $product->save();
            } else {
                Log::warning("Didn't find brickLinkId for {$product->id}");
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function extractBrickLinkId(Crawler $crawler): ?string
    {
        // Find the section with External Sites
        $section = $crawler->filter('section')->reduce(function (Crawler $node) {
            return $node->filter('h4')->count() && trim($node->filter('h4')->text()) === 'External Sites';
        });

        $brickLinkId = null;

        if ($section->count()) {
            // Find the BrickLink row in the table
            $section->filter('table tr')->each(function (Crawler $tr) use (&$brickLinkId) {
                $tds = $tr->filter('td');
                if ($tds->count() >= 2 && trim($tds->eq(0)->text()) === 'BrickLink') {
                    // Get the text inside the <a> tag (the ID)
                    $link = $tds->eq(1)->filter('a');
                    if ($link->count()) {
                        $brickLinkId = trim($link->text());
                    }
                }
            });
        }

        return $brickLinkId;
    }
}
