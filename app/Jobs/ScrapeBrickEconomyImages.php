<?php

namespace App\Jobs;

use App\Models\Product;
use App\Traits\HasUserAgent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeBrickEconomyImages implements ShouldQueue
{
    use Queueable, HasUserAgent;

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
        $product_id = $this->product_id;

        $product = Product::find($product_id);
        if (!$product || !$product->brickeconomy_id) {
            Log::error("Couldn't import images for {$product_id}", ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
            return;
        }

        $url = "https://www.brickeconomy.com/{$product->product_type}/{$product->brickeconomy_id}/";

        $response = NULL;
        try {
            $response = $this->proxyRequest()->get($url);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
            $this->fail($e);
        }

        $html = $response?->body();

        $regex = '/<ul id="setmediagallery"[^>]*>(.*?)<\/ul>/s';

        // First, extract the content of the <ul> block
        if ($html && preg_match($regex, $html, $ulMatch)) {
            $ulContent = $ulMatch[1];
            // Now, extract all src attributes from the <img> tags within the <ul>
            $imgRegex = '/<img[^>]*src="([^"]+)"[^>]*>/';
            if (preg_match_all($imgRegex, $ulContent, $imgMatches)) {
                // Array of all src attributes
                $srcs = collect($imgMatches[1])->map(fn($url) => "https://www.brickeconomy.com{$url}");
                DownloadProductImageJob::dispatch($product_id, $srcs);
            }
        }
    }
}
