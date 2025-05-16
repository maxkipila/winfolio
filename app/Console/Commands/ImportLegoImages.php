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
    protected $signature = 'import:lego-images {--force : Prepise existujici images}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stahovani images pro LEGO produkty z CSV souboru';

    public function handle()
    {
        $force = $this->option('force');

        $batchSize = 200;

        $this->info("Začínám import obrázků");
        ini_set('memory_limit', '1536M');

        $query = Product::query();

        if (!$force) {
            $query->whereDoesntHave('media', function ($q) {
                $q->where('collection_name', 'images');
            });
        }

        $totalProducts = $query->count();
        $this->info("Nalezeno {$totalProducts} produktů ke zpracování");

        if ($totalProducts === 0) {
            $this->info("Žádné produkty ke zpracování");
            return 0;
        }

        $progress = $this->output->createProgressBar($totalProducts);
        $progress->start();

        // Davkovani
        $query->chunk($batchSize, function ($products) use ($progress, $force) {
            foreach ($products as $product) {

                DownloadProductImageJob::dispatch($product->id, null, $force);
                $progress->advance();
            }
            gc_collect_cycles();
        });

        $progress->finish();
        $this->newLine();
        $this->info("Import dokončen, joby byly zařazeny do fronty");

        return 0;
    }
}
