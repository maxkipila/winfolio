<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;
    protected $force;

    /**
     * Create a new job instance.
     */
    public function __construct($productId, $force = false)
    {
        $this->productId = $productId;
        $this->force = $force;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $product = Product::find($this->productId);

            if (!$product || empty($product->img_url)) {
                Log::info("Product {$this->productId} not found or has no image URL");
                return;
            }

            if (!$this->force && $product->getMedia('images')->count() > 0) {
                Log::info("Product {$product->product_num} already has images, skipping");
                return;
            }
            if ($this->force && $product->getMedia('images')->count() > 0) {
                $product->clearMediaCollection('images');
            }

            $product->addMediaFromUrl($product->img_url)
                ->withResponsiveImages()
                ->toMediaCollection('images');

            Log::info("Image added for product {$product->product_num}");
        } catch (\Exception $e) {
            Log::error("Error downloading image for product {$this->productId}: " . $e->getMessage());

            if ($this->attempts() < 3) {
                $this->release(30);
            }
        }
    }
}
