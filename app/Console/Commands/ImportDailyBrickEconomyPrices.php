<?php

namespace App\Console\Commands;

use App\Enums\PriceType;
use App\Jobs\ScrapeBrickEconomyPrices;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ImportDailyBrickEconomyPrices extends Command
{
    protected $signature = 'import:daily';
    protected $description = 'Scrape daily price median from BrickEconomy';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $this->info("Scraping median cen z BrickEconomy");

        ini_set('memory_limit', '512M');

        $products = Product::orderBy('id')->pluck('id');

        $this->withProgressBar($products, function ($product_id) {
            ScrapeBrickEconomyPrices::dispatch($product_id, true, false);
        });
    }
}
