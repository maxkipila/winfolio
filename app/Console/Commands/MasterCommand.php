<?php

namespace App\Console\Commands;

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

            $this->info('Inicializace vývojového prostředí...');

            // Spustí kompletní seed 
            Artisan::call('db:seed');
            $this->info(Artisan::output());

            Artisan::call('app:calculate-trends');
            $this->info(Artisan::output());
        } elseif ($this->option('prod')) {
            // Produkční prostředí jen s nutnými daty
            $this->info('Inicializace produkčního prostředí...');

            // Spustí pouze EssentialsSeeder
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\EssentialsSeeder']);
            $this->info(Artisan::output());

            // Dodatečné produkční nastavení
            $this->info('Nastavuji produkční prostředí...');

            // Import základních LEGO dat
            $this->info('Importuji základní LEGO data...');
            Artisan::call('app:import', ['--type' => 'products']);
            $this->info(Artisan::output());

            // Zajištění základních cen
            $this->info('Zajišťuji základní cenové údaje...');
            Artisan::call('prices:ensure-all', ['--chunk' => 500]);
            $this->info(Artisan::output());

            // Agregace cen
            Artisan::call('prices:aggregate');
            $this->info(Artisan::output());
        } else {
            $this->error('Musíte zadat --dev nebo --prod volbu');
            return self::FAILURE;
        }

        $this->info('Inicializace dokončena');
        return self::SUCCESS;
    }
}
