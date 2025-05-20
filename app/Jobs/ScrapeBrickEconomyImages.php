<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeBrickEconomyImages implements ShouldQueue
{
    use Queueable;

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

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->get($url);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
        }

        $html = $response->body();

        $regex = '/<ul id="setmediagallery"[^>]*>(.*?)<\/ul>/s';

        // First, extract the content of the <ul> block
        if (preg_match($regex, $html, $ulMatch)) {
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
