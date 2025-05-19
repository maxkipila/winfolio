<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;


class InitialProductImport extends Command
{
    protected $signature = 'app:import';

    protected $description = 'Import dat';

    public function handle()
    {

        $this->importProducts();
        $this->importImages();
        /*  $this->importThemes(); */
        $this->importMappings();
        return self::SUCCESS;
    }


    private function importProducts(): void
    {

        Artisan::call('import:lego-data');
    }
    private function importImages(): void
    {
        $this->info('Import images...');

        Artisan::call('import:lego-images');
    }

    private function importMappings(): void
    {
        $this->info('Generování mapování pro sety a minifigurky...');
        Artisan::call('lego:generate-mappings');

        $this->info('Scraping Bricklink...');
        Artisan::call('lego:scrape-bricklink');
    }


    /*   private function importPrices(): void
    {
        $this->info('Import cen...');
        Artisan::call('actual:prices-brickeconomy'); // limit 100, delay 3, throttle 5
        $this->info(Artisan::output());
        $this->info('Ceny byly úspěšně importovány');
    } */
}
