<?php

namespace App\Console\Commands;

use App\Models\Price;
use Illuminate\Console\Command;

class FixAggregatedPrices extends Command
{
    protected $signature = 'prices:fix-aggregated';
    protected $description = 'Fix missing retail and wholesale values in aggregated price records';

    public function handle()
    {
        $this->info('Fixing aggregated price records...');
        ini_set('memory_limit', '1G');

        // Najít všechny agregované záznamy s chybějícími retail/wholesale hodnotami
        $count = Price::where('type', 'aggregated')
            ->whereNull('retail')
            ->orWhere(function ($query) {
                $query->where('type', 'aggregated')
                    ->whereNull('wholesale');
            })
            ->count();

        $this->info("Found {$count} records to fix");

        if ($count === 0) {
            $this->info("No records need fixing");
            return Command::SUCCESS;
        }

        // Aktualizovat záznamy po částech
        Price::where('type', 'aggregated')
            ->whereNull('retail')
            ->orWhere(function ($query) {
                $query->where('type', 'aggregated')
                    ->whereNull('wholesale');
            })
            ->chunkById(1000, function ($prices) {
                foreach ($prices as $price) {
                    $price->retail = round($price->value * 1.3, 2);
                    $price->wholesale = round($price->value * 0.7, 2);
                    $price->save();
                }
                $this->info("Processed chunk of records");
                gc_collect_cycles();
            });

        $this->info("Fixed all aggregated price records");
        return Command::SUCCESS;
    }
}
