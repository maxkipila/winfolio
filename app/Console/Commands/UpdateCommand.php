<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateCommand extends Command
{
    protected $signature = 'app:update
                          {--mode=daily : Režim aktualizace (daily, weekly, monthly)}
                          {--type=all : Typ produktů (all, set, minifig)}
                          {--limit=1000 : Limit počtu produktů}';

    protected $description = 'Jednotný příkaz pro aktualizaci dat';

    public function handle()
    {
        $mode = $this->option('mode');
        $type = $this->option('type');
        $limit = (int) $this->option('limit');

        switch ($mode) {
            case 'daily':
                $this->dailyUpdate($type, $limit);
                break;

            case 'weekly':
                $this->weeklyUpdate($type, $limit);
                break;

            case 'monthly':
                $this->monthlyUpdate($type, $limit);
                break;

            default:
                $this->error("Neznámý režim aktualizace: {$mode}. Povolené hodnoty: daily, weekly, monthly");
                return self::FAILURE;
        }

        $this->info("Aktualizace pro režim {$mode} byla úspěšně dokončena");
        return self::SUCCESS;
    }

    private function dailyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím denní aktualizaci...');

        // Scraping dennich dat
        $this->info('Spouštím actual:prices-brickeconomy...');
        Artisan::call('actual:prices-brickeconomy');
        $this->info(Artisan::output());

        // Aktualizace uživatelských rekordů
        $this->info('Spouštím app:update-user-records...');
        Artisan::call('app:update-user-records');
        $this->info(Artisan::output());

        // Kontrola odznaků
        $this->info('Spouštím app:check-awards...');
        Artisan::call('app:check-awards');
        $this->info(Artisan::output());
    }

    private function weeklyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím týdenní aktualizaci...');

        $this->info('Spouštím prices:aggregate...');
        Artisan::call('prices:aggregate', ['--force' => true]);
        $this->info(Artisan::output());
    }

    private function monthlyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím měsíční aktualizaci...');

        // Aktualizace vazeb
        $this->info('Spouštím app:import');
        Artisan::call('app:import');
        $this->info(Artisan::output());

        /* // Aktualizace vazeb
        $this->info('Spouštím import:lego-data pro vazby...');
        Artisan::call('import:lego-data', ['dataType' => 'relationships']);
        $this->info(Artisan::output()); */

        // Agregace cen pro celý měsíc
        $this->info('Spouštím prices:aggregate pro měsíční data...');
        Artisan::call('prices:aggregate', [
            '--date' => now()->startOfMonth()->format('Y-m-d'),
            '--days' => 31,
            '--force' => true
        ]);
        $this->info(Artisan::output());
        // Vypocet trendu
        $this->info('Spouštím app:calculate-trends...');
        Artisan::call('app:calculate-trends');
        $this->info(Artisan::output());
    }
}
