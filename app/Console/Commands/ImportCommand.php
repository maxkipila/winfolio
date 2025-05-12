<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;


class ImportCommand extends Command
{
    protected $signature = 'app:import {--all : Import vše (alias pro --type=all)} {--type=all : Typ importu (all, products, prices, images, mappings)} {--limit=0 : Omezení počtu záznamů}';

    protected $description = 'Jednotný příkaz pro import dat';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $type = $this->option('all') ? 'all' : $this->option('type');

        switch ($type) {
            case 'all':
                $this->importProducts();
                $this->importImages();
                $this->importMappings();
                $this->importPrices();
                break;

            case 'products':
                $this->importProducts();
                break;

            case 'images':
                $this->importImages();
                break;

            case 'mappings':
                $this->importMappings();
                break;

            case 'prices':
                $this->importPrices();
                break;

            default:
                $this->error("Neznámý typ importu: {$type}");
                return self::FAILURE;
        }

        $this->info('Import byl úspěšně dokončen');
        return self::SUCCESS;
    }

    private function importProducts(): void
    {
        $this->info('Import produktů...');
        Artisan::call('import:lego-data');
        $this->info(Artisan::output());
    }

    private function importImages(): void
    {
        $this->info('Import obrázků...');
        Artisan::call('import:lego-images', ['--skip-existing' => true]);
        $this->info(Artisan::output());
    }


    private function importMappings(): void
    {
        $this->info('Oprava mapování minifigurek...');
        Artisan::call('lego:fix-minifig-mappings');
        $this->info(Artisan::output());

        $this->info('Mapování pro sety...');
        Artisan::call('lego:generate-mappings', ['--type' => 'set']);
        $this->info(Artisan::output());

        $this->info('Mapování pro minifigurky...');
        Artisan::call('lego:generate-mappings', ['--type' => 'minifig']);
        $this->info(Artisan::output());

        $this->info('Scraping Bricklink...');
        Artisan::call('lego:scrape-bricklink', [
            '--delay' => 2,
            '--batch' => 20,
            '--offset' => 0,
        ]);
        $this->info(Artisan::output());
    }

    /*   private function importPrices(): void
    {
        $this->info('Import cen...');
        Artisan::call('actual:prices-brickeconomy'); // limit 100, delay 3, throttle 5
        $this->info(Artisan::output());
        $this->info('Ceny byly úspěšně importovány');
    } */
}
