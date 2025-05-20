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
    protected $imageUrls;
    protected $force;

    /**
     * Create a new job instance.
     */
    /*   public function __construct($productId, $force = false)
    {
        $this->productId = $productId;
        $this->force = $force;
    } */
    public function __construct($productId, $imageUrls = [], $force = false)
    {
        $this->productId = $productId;
        $this->imageUrls = $imageUrls;
      
        $this->force = $force;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $product = Product::find($this->productId);
            Log::info("Downloading Produkt s ID {$this->productId}");
            if (!$product) {
                Log::info("Produkt s ID {$this->productId} nenalezen");
                return;
            }

            // Vymazat existující obrázky, pokud je vyžadováno
            if ($this->force && $product->getMedia('images')->count() > 0) {
                $product->clearMediaCollection('images');
            } else if (!$this->force && $product->getMedia('images')->count() > 0) {
                Log::info("Produkt {$product->product_num} již má obrázky, přeskakuji");
                return;
            }

            Log::info("Saving Produkt s ID {$this->productId}", ['urls' => $this->imageUrls]);
            $product->images = $this->imageUrls;
            
        } catch (\Exception $e) {
            Log::error("Chyba při stahování obrázků pro produkt {$this->productId}: " . $e->getMessage());
        }
    }

    /**
     * Získá výchozí URL obrázku pro produkt podle jeho typu
     */
    protected function getDefaultImageUrl(Product $product): ?string
    {
        // Pokud má produkt definované img_url, použijeme ho
        if (!empty($product->img_url)) {
            return $product->img_url;
        }

        // Jinak vygenerujeme URL podle typu produktu
        $productNum = $product->product_num;

        if ($product->product_type === 'set') {
            return "https://cdn.rebrickable.com/media/sets/{$productNum}.jpg";
        } elseif ($product->product_type === 'minifig') {
            return "https://cdn.rebrickable.com/media/minifigs/{$productNum}.jpg";
        }

        return null;
    }
}
