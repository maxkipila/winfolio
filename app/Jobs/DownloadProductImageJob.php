<?php

namespace App\Jobs;

use App\Models\Product;
use App\Traits\HasUserAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DownloadProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasUserAgent;

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

            $API_CREDENTIALS = env('PROXY_CREDENTIALS');

            if (!$product) {
                Log::info("Produkt s ID {$this->productId} nenalezen");
                return;
            }

            //Vymazat existující obrázky, pokud je vyžadováno
            if ($this->force && $product->getMedia('images')->count() > 0) {
                $product->clearMediaCollection('images');
            } else if (!$this->force && $product->getMedia('images')->count() > 0) {
                Log::info("Produkt {$product->product_num} již má obrázky, přeskakuji");
                return;
            }

            foreach ($this->imageUrls as $imageUrl) {
                try {
                    // Download image through proxy
                    $response = Http::withOptions([
                        'timeout' => 600,
                        'proxy' => "http://{$API_CREDENTIALS}@217.30.10.33:43587"
                    ])
                        ->withHeaders([
                            'User-Agent' => $this->userAgents[rand(0, count($this->userAgents) - 1)],
                        ])
                        ->get($imageUrl);

                    if ($response->successful()) {
                        // Save to temp file
                        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $tmpPath = storage_path('app/tmp_' . uniqid() . '.' . $extension);
                        file_put_contents($tmpPath, $response->body());
                        Log::info("adding $tmpPath");
                        // Add to media library
                        $product->addMedia($tmpPath)
                            ->withResponsiveImages()
                            ->toMediaCollection('images');
                    } else {
                        Log::error("Nepodařilo se stáhnout obrázek {$imageUrl} pro produkt {$this->productId}: HTTP " . $response->status());
                        $this->fail("Nepodařilo se stáhnout obrázek {$imageUrl} pro produkt {$this->productId}: HTTP " . $response->status());
                    }
                } catch (\Exception $e) {
                    Log::error("Chyba při stahování nebo ukládání obrázku {$imageUrl} pro produkt {$this->productId}: " . $e->getMessage());
                    $this->fail($e);
                }
            }


            $product?->update([
                'media_count' => $product->media()->count(),
            ]);
        } catch (\Exception $e) {
            Log::error("Chyba při stahování obrázků pro produkt {$this->productId}: " . $e->getMessage());
            $this->fail($e);
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
