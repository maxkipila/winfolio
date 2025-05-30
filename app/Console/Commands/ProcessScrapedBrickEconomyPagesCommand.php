<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScrapedBrickEconomyPages;
use Illuminate\Console\Command;

class ProcessScrapedBrickEconomyPagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:scraped-pages {file} {productId}';

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
        $file = $this->argument('file');
        $productId = $this->argument('productId');

        // Dispatch your job with the HTML
        ProcessScrapedBrickEconomyPages::dispatch($file, $productId);
    }
}
