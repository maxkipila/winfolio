<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScrapedBrickEconomyPages;
use App\Jobs\ProcessScrapedBrickEconomyThemes;
use Illuminate\Console\Command;

class ProcessScrapedBrickEconomyThemesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:scraped-themes {file} {theme}';

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
        $theme = $this->argument('theme');

        // Dispatch your job with the HTML
        ProcessScrapedBrickEconomyThemes::dispatchSync($file, $theme);
    }
}
