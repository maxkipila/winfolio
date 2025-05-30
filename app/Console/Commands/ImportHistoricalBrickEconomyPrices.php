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

class ImportHistoricalBrickEconomyPrices extends Command
{
    protected $signature = 'import:historical {--force : Přepsat existující záznamy}';
    protected $description = 'Scrape historical price data from BrickEconomy';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::disableQueryLog();
        $this->info("Scraping historických cen z BrickEconomy");

        $force = $this->option('force');
        $lastProcessedId = 0;

        if (!$force) {
            $lastProcessedId = Price::where('type', PriceType::SCRAPED->value)
                ->max('product_id') ?? 0;
            $this->info("Pokračuji od ID > {$lastProcessedId}");
        }

        $products = Product::where('id', '>', $lastProcessedId)->pluck('id');

        $this->withProgressBar($products, function ($product_id) {
            ScrapeBrickEconomyPrices::dispatch($product_id);
        });
    }
}
