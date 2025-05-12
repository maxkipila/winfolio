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
        $limit = $this->option('limit');

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
                $this->error("Neznámý režim aktualizace: {$mode}");
                return self::FAILURE;
        }

        $this->info('Aktualizace byla úspěšně dokončena');
        return self::SUCCESS;
    }

    private function dailyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím denní aktualizaci...');

        // Prioritně aktualizovat produkty s chybějícími cenami
        Artisan::call('scrape:brickeconomy-bulk', [
            '--type' => $type,
            '--limit' => $limit,
            '--only-without-price' => true
        ]);
        $this->info(Artisan::output());

        // Aktualizace uživatelských rekordů
        Artisan::call('app:update-user-records');
        $this->info(Artisan::output());

        // Kontrola odznaků
        Artisan::call('app:check-awards');
        $this->info(Artisan::output());
    }

    private function weeklyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím týdenní aktualizaci...');

        // Aktualizace cen pro větší množství produktů
        Artisan::call('scrape:brickeconomy-bulk', [
            '--type' => $type,
            '--limit' => $limit * 3
        ]);
        $this->info(Artisan::output());

        // Agregace cen
        Artisan::call('prices:aggregate', ['--force' => true]);
        $this->info(Artisan::output());

        // Výpočet trendů
        Artisan::call('app:calculate-trends');
        $this->info(Artisan::output());
    }

    private function monthlyUpdate(string $type, int $limit): void
    {
        $this->info('Spouštím měsíční aktualizaci...');

        // Import nových produktů
        Artisan::call('import:lego-data', ['--truncate' => false]);
        $this->info(Artisan::output());

        // Aktualizace vazeb
        Artisan::call('import:lego-data', ['dataType' => 'relationships']);
        $this->info(Artisan::output());

        // Agregace cen pro celý měsíc
        Artisan::call('prices:aggregate', [
            '--date' => now()->startOfMonth()->format('Y-m-d'),
            '--days' => 31,
            '--force' => true
        ]);
        $this->info(Artisan::output());

        // Výpočet trendů
        Artisan::call('app:calculate-trends');
        $this->info(Artisan::output());
    }
}
