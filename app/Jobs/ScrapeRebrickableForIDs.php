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
        //Hledání BL id v URL
        $brickLinkId = null;
        $crawler->filter('a')->each(function (Crawler $link) use (&$brickLinkId) {
            if ($brickLinkId) return;

            $href = $link->attr('href');

            // Hledáme specificky v URL části bricklink.com/v2/catalog/catalogitem.page?
            if (preg_match('/bricklink\.com\/v2\/catalog\/catalogitem\.page\?([^&]*)/', $href, $matches)) {

                parse_str($matches[1], $params);
                if (isset($params['M'])) {
                    $brickLinkId = $params['M'];
                    return;
                }
            }
            $text = trim($link->text());
            if (stripos($text, 'bricklink') !== false && preg_match('/([a-z0-9]{3,10})/i', $text, $matches)) {
                $brickLinkId = $matches[1];
            }
        });

        if ($brickLinkId) return $brickLinkId;

        $crawler->filter('h4, h3, .external-ids, .external-links')->each(function (Crawler $section) use (&$brickLinkId) {
            if ($brickLinkId) return;

            $section->filter('table tr')->each(function (Crawler $row) use (&$brickLinkId) {
                if ($brickLinkId) return;

                if ($row->filter('td')->count() >= 2) {
                    $label = trim($row->filter('td')->eq(0)->text());
                    if (stripos($label, 'BrickLink') !== false) {
                        $valueText = trim($row->filter('td')->eq(1)->text());
                        $cleanId = preg_replace('/^(ID|No|Number):\s*/i', '', $valueText);
                        $brickLinkId = trim($cleanId);
                    }
                }
            });

            if (!$brickLinkId) {
                $section->filter('a, span, div')->each(function (Crawler $element) use (&$brickLinkId) {
                    if ($brickLinkId) return;

                    $text = $element->text();
                    if (preg_match('/(frnd|sw|hp|col|sh|bat|njo|lor|cty|poc)(\d+)/i', $text, $matches)) {
                        $brickLinkId = $matches[0];
                    }
                });
            }
        });

        if ($brickLinkId === 'v2') {
            return null;
        }

        return $brickLinkId;
    }
}
