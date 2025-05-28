<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeBrickEconomy as JobsScrapeBrickEconomy;
use App\Models\Product;
use Illuminate\Console\Command;

class ScrapeBrickEconomy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:brickeconomy';

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
        $products = Product::pluck('id');

        $chunkSize = 10; // Number of products per batch
        $chunks = $products->chunk($chunkSize); // Split products into chunks
        $length = $chunks->count() - 1;

        foreach ($chunks as $key => $chunk) {
            JobsScrapeBrickEconomy::dispatch($chunk, true, true, true);
            $this->info("Chunk $key/{$length} dispatched"); 
        }

        $this->info("All chunks dispatched");
    }
}
