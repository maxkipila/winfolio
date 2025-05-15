<?php

namespace App\Console\Commands;

use App\Jobs\DownloadProductImage;
use App\Jobs\DownloadProductImageJob;
use App\Models\Product;
use Illuminate\Console\Command;

class ImportLegoImages extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'import:lego-images 
                           {--force : Overwrite existing images}
                           {--direct : Download directly instead of using queue}
                           {--delay=15 : Delay in minutes before the jobs start}
                           {--batch-size=200 : Number of products to process in one batch}
                           {--type= : Filter products by type (set or minifig)}
                           {--limit=0 : Limit the number of products to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stahovani images pro LEGO produkty z CSV souboru';

    public function handle()
    {
        $force = $this->option('force');
        $direct = $this->option('direct');
        $delay = (int)$this->option('delay');
        $batchSize = (int)$this->option('batch-size');
        $limit = (int)$this->option('limit');
        $type = $this->option('type');

        // mozna zakomentovat
        $this->info("Starting import");
        ini_set('memory_limit', '1536M');

        // Vytvoření query, ted uz bez img_url
        $query = Product::query();

        // pokud neni force, preskocime
        if (!$force) {
            $query->whereDoesntHave('media', function ($q) {
                $q->where('collection_name', 'images');
            });
        }

        // Custom: Filtrování podle typu produktu 
        if ($type) {
            $query->where('product_type', $type);
        }

        // Custom: Limit poctu produktu
        if ($limit > 0) {
            $query->limit($limit);
        }

        $totalProducts = $query->count();
        $this->info("Found {$totalProducts} products to process");

        if ($totalProducts === 0) {
            $this->info("No products to process");
            return 0;
        }

        $progress = $this->output->createProgressBar($totalProducts);
        $progress->start();

        // set delay 
        $delayTime = now()->addMinutes($delay);

        if ($direct) {
            // direct download
            $query->chunk($batchSize, function ($products) use ($progress, $force) {
                foreach ($products as $product) {
                    try {
                        // If exist skipp 
                        if (!$force && $product->getMedia('images')->count() > 0) {
                            $progress->advance();
                            continue;
                        }

                        // If exist skipp
                        if ($force && $product->getMedia('images')->count() > 0) {
                            // Force
                            $product->clearMediaCollection('images');
                        }

                        // Generování URL podle product_num
                        $imageUrl = $this->getImageUrl($product);

                        if (!$imageUrl) {
                            $this->warn("Could not determine image URL for product {$product->product_num}");
                            $progress->advance();
                            continue;
                        }

                        $product->addMediaFromUrl($imageUrl)
                            ->withResponsiveImages()
                            ->toMediaCollection('images');

                        $this->line("Image added for product {$product->product_num}");
                    } catch (\Exception $e) {
                        $this->error("Error {$product->product_num}: " . $e->getMessage());
                    }

                    $progress->advance();
                }

                gc_collect_cycles();
            });
        } else {
            // Queue zpracování
            $processedCount = 0;

            $query->chunk($batchSize, function ($products) use ($progress, $force, $delayTime, &$processedCount) {
                foreach ($products as $product) {
                    // Vytvoříme job s nastavením zpoždění
                    DownloadProductImageJob::dispatch($product->id, $force)
                        ->delay($delayTime);

                    $progress->advance();
                    $processedCount++;
                }

                gc_collect_cycles();
            });

            $this->info("\nQueued {$processedCount} jobs to start at {$delayTime->format('Y-m-d H:i:s')}");
        }

        $progress->finish();
        $this->newLine();
        $this->info("Import done");

        return 0;
    }

    /**
     * Získá URL obrázku pro produkt
     */
    protected function getImageUrl(Product $product): ?string
    {
        $productNum = $product->product_num;

        if ($product->product_type === 'set') {
            return "https://cdn.rebrickable.com/media/sets/{$productNum}.jpg";
        } elseif ($product->product_type === 'minifig') {
            return "https://cdn.rebrickable.com/media/minifigs/{$productNum}.jpg";
        }

        return null;
    }
}

    /*   public function handle()
    {
        $this->info("Start importing");
        ini_set('memory_limit', '1536M');
        Product::whereNotNull('img_url')
            ->when($this->option('skip-existing'), function ($query) {
                $query->whereDoesntHave('media', function ($q) {
                    $q->where('collection_name', 'images');
                });
            })
            ->chunk(200, function ($products) {
                foreach ($products as $product) {
                    try {
                        $product->images['name.png']
                           
                        // $product->addMediaFromUrl($product->img_url)
                        //     ->withResponsiveImages()
                        //     ->toMediaCollection('images'); 
                        // $this->info("Image add for product {$product->product_num}");
                    } catch (\Exception $e) {
                        $this->error("Error {$product->product_num}: " . $e->getMessage());
                    }
                }
                gc_collect_cycles();
            });

        $this->info("Import done");
        return 0;
    } */
