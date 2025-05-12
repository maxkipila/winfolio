<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportCommand extends Command
{
    protected $signature = 'app:import
                           {--type=all : Typ importu (all, products, prices, images, mappings)}
                           {--limit=0 : Omezení počtu záznamů}';

    protected $description = 'Jednotný příkaz pro import dat';

    public function handle()
    {
        $type = $this->option('type');
        $limit = $this->option('limit');

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
        $this->info('Generování mapování ID...');
        Artisan::call('lego:generate-mappings');
        $this->info(Artisan::output());

        $this->info('Oprava mapování minifigurek...');
        Artisan::call('lego:fix-minifig-mappings');
        $this->info(Artisan::output());
    }

    private function importPrices(): void
    {
        $this->info('Import cen...');

        // Nejprve zajistíme, aby každý produkt měl cenu
        Artisan::call('prices:ensure-all');
        $this->info(Artisan::output());

        // Pak vygenerujeme agregované ceny
        Artisan::call('prices:generate-history', ['--months' => 12]);
        $this->info(Artisan::output());

        $this->info('Ceny byly úspěšně importovány');
    }
}
