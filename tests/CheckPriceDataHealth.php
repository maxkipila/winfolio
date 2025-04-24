<?php

namespace App\Console\Commands;

use App\Models\Price;
use App\Models\Product;
use App\Models\Trend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPriceDataHealth extends Command
{
    protected $signature = 'app:check-price-data';
    protected $description = 'Zkontroluje stav dat souvisejících s cenami a trendy';

    public function handle()
    {
        $this->info('=== Kontrola dat cen v databázi ===');

        // Základní statistiky
        $totalProducts = Product::count();
        $this->info("Celkový počet produktů: {$totalProducts}");

        $totalPrices = Price::count();
        $this->info("Celkový počet cenových záznamů: {$totalPrices}");

        // Rozdělení cen podle typu
        $priceTypes = Price::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        $this->info("\nCeny podle typu:");
        foreach ($priceTypes as $type) {
            $typeName = $type->type ?? 'bez typu';
            $this->info("- {$typeName}: {$type->count}");
        }

        // Rozdělení cen podle stavu
        $priceConditions = Price::select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')
            ->get();

        $this->info("\nCeny podle stavu:");
        foreach ($priceConditions as $condition) {
            $this->info("- {$condition->condition}: {$condition->count}");
        }

        // Potenciální problémy
        $productsWithoutPrices = Product::whereDoesNotHave('prices')->count();
        $this->info("\nPotenciální problémy:");
        $this->info("- Produkty bez cen: {$productsWithoutPrices}");

        $productsWithoutAggregatedPrices = Product::whereDoesNotHave('prices', function ($q) {
            $q->where('type', 'aggregated');
        })->count();
        $this->info("- Produkty bez agregovaných cen: {$productsWithoutAggregatedPrices}");

        // Statistiky trendů
        $trendCount = Trend::count();
        $this->info("\nStatistiky trendů:");
        $this->info("- Celkový počet trendů: {$trendCount}");

        $trendingCount = Trend::where('type', 'trending')->count();
        $this->info("- Trendující produkty: {$trendingCount}");

        $topMoversCount = Trend::where('type', 'top_mover')->count();
        $this->info("- Top movers produkty: {$topMoversCount}");

        return Command::SUCCESS;
    }
}
