<?php

namespace App\Console\Commands;

use App\Jobs\DownloadProductImage;
use App\Jobs\DownloadProductImageJob;
use App\Jobs\ScrapeBrickEconomyImages;
use App\Models\Product;
use App\Traits\HasUserAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportLegoImages extends Command
{
    use HasUserAgent;
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
        ini_set('memory_limit', '512M');
        $force = $this->option('force');


        // $response = $this->proxyRequest("https://whatleaks.site/");

        // dd($response->body());

        $this->info("Začínám import obrázků");

        $query = Product::orderBy('id');

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

        $this->withProgressBar($query->pluck('id'), function ($product_id) use ($force) {
            ScrapeBrickEconomyImages::dispatch($product_id);
        });

        $this->newLine();
        $this->info("Import dokončen, joby byly zařazeny do fronty");

        return 0;
    }
}
