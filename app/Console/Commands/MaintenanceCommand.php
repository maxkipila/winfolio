<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MaintenanceCommand extends Command
{
    protected $signature = 'app:maintenance
                           {--action=check : Akce (check, fix-prices, fill-missing, assign-themes)}
                           {--limit=1000 : Limit počtu záznamů}';

    protected $description = 'Příkaz pro údržbu dat v aplikaci';

    public function handle()
    {
        $action = $this->option('action');
        $limit = $this->option('limit');

        switch ($action) {
            case 'check':
                $this->checkData();
                break;

            case 'fix-prices':
                $this->fixPrices();
                break;

            case 'fill-missing':
                $this->fillMissingData($limit);
                break;

            case 'assign-themes':
                $this->assignThemes();
                break;

            default:
                $this->error("Neznámá akce: {$action}");
                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkData(): void
    {
        $this->info('Kontrola dat v systému...');

        // Kontrola produktů
        $productCount = \App\Models\Product::count();
        $this->info("Celkem produktů: {$productCount}");
        $this->info("- Setů: " . \App\Models\Product::where('product_type', 'set')->count());
        $this->info("- Minifigurek: " . \App\Models\Product::where('product_type', 'minifig')->count());

        // Kontrola cen
        $priceCount = \App\Models\Price::count();
        $this->info("Celkem cenových záznamů: {$priceCount}");

        // Kontrola uživatelů
        $userCount = \App\Models\User::count();
        $this->info("Celkem uživatelů: {$userCount}");

        // Kontrola produktů bez cen
        $productsWithoutPrices = \App\Models\Product::whereDoesntHave('prices')->count();
        $this->info("Produkty bez cen: {$productsWithoutPrices}");
    }

    private function fixPrices(): void
    {
        $this->info('Oprava cenových údajů...');

        // Oprava agregovaných cen
        Artisan::call('prices:fix-aggregated');
        $this->info(Artisan::output());

        // Audit růstu cen
        Artisan::call('app:audit-product-growth');
        $this->info(Artisan::output());

        $this->info('Oprava cenových údajů dokončena');
    }

    private function fillMissingData(int $limit): void
    {
        $this->info('Doplňování chybějících dat...');

        // Doplnění chybějících cen
        Artisan::call('prices:ensure-all', ['--chunk' => min($limit, 500)]);
        $this->info(Artisan::output());

        // Historické ceny
        Artisan::call('prices:update-historical');
        $this->info(Artisan::output());

        $this->info('Doplňování chybějících dat dokončeno');
    }

    private function assignThemes(): void
    {
        $this->info('Přiřazování témat minifigurkám...');

        // Přiřazení témat podle setů
        Artisan::call('app:assign-themes-to-minifigs');
        $this->info(Artisan::output());

        $this->info('Přiřazování témat dokončeno');
    }
}
