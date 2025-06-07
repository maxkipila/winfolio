<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScrapedBrickEconomyPages;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ReprocessAllScraped extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:scraped-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1G');
        $this->withProgressBar(Product::orderBy('id')->pluck('id'), function ($product_id) {
            try {
                //code...
                ProcessScrapedBrickEconomyPages::dispatchSync("storage/app/html/tmp_scraped_{$product_id}.html", $product_id);     
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
    }
}
