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

class ScrapeBrickEconomy implements ShouldQueue
{
    use Queueable, HasUserAgent;

    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public $products = [], public $daily = false, public $historical = false, public $images = false)
    {
        //
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $urls = [];

        foreach ($this->products as $key => $product_id) {

            $product = Product::find($product_id);

            if (!$product || !$product->brickeconomy_id)
                continue;

            $urls[$product_id] =  "https://www.brickeconomy.com/{$product->product_type}/{$product->brickeconomy_id}/";
        }

        $pages = [];

        try {
            $pages = $this->proxyRequest($urls);
            Log::info('finished the scraping', ['pages' => ($pages ?? 0)]);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    
}
