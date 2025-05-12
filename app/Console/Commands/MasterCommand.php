<?php

namespace App\Console\Commands;

use App\Models\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MasterCommand extends Command
{
    protected $signature = 'app:master
                            {--dev : Inicializace pro vývojové prostředí}
                            {--prod : Inicializace pro produkční prostředí}';

    protected $description = 'Hlavní příkaz pro inicializaci projektu';

    public function handle()
    {
        if ($this->option('dev')) {

            $this->info('Inicializace ');

            // Spustí kompletní seed 
            Artisan::call('db:seed');
            $this->info(Artisan::output());

            Artisan::call('app:calculate-trends');
            $this->info(Artisan::output());
        } elseif ($this->option('prod')) {
            // Produkční prostředí jen s nutnými daty
            $this->info('Inicializace produkčního prostředí...');

            // Import základních témat
            if (Theme::count() === 0) {
                $this->info('Importuji základní témata...');
                Artisan::call('import:lego-data', ['dataType' => 'themes']);
                $this->info(Artisan::output());
            }

            // Spustí pouze EssentialsSeeder
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\EssentialsSeeder']);
            $this->info(Artisan::output());

            // Dodatečné produkční nastavení
            /* $this->info('Nastavuji produkční prostředí...'); */

            // Import základních LEGO dat
            $this->info('Importuji základní LEGO data...');
            Artisan::call('app:import', ['--type' => 'products']);
            $this->info(Artisan::output());
            $this->info('Přiřazuji témata k minifigurkám...');
            Artisan::call('app:assign-themes-to-minifigs');
        } else {
            $this->error('Musíte zadat --dev nebo --prod volbu');
            return self::FAILURE;
        }

        $this->info('Inicializace dokončena');
        return self::SUCCESS;
    }
}
