<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ImportLegoImages extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'import:lego-images {--skip-existing : Skip products that already have images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Lego product images from URLs';

    public function handle()
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
                        $product->addMediaFromUrl($product->img_url)
                            ->withResponsiveImages()
                            ->toMediaCollection('images');
                        $this->info("Image add for product {$product->product_num}");
                    } catch (\Exception $e) {
                        $this->error("Error {$product->product_num}: " . $e->getMessage());
                    }
                }
                gc_collect_cycles();
            });

        $this->info("Import done");
        return 0;
    }
}
