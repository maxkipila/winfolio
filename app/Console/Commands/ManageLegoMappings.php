<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\GenerateLegoIdMappingsCommand;
use App\Console\Commands\FixMinifigMappingsCommand;
use App\Console\Commands\UpdateMinifigMappingsCommand;
use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ManageLegoMappings extends Command
{
    protected $signature = 'lego:mappings 
                            {--generate : Vygeneruje základní mapování pro produkty}
                            {--fix : Opraví a doplní mapování}
                            {--update : Aktualizuje mapování}
                            {--sync : Synchronizuje mapping s produkty}
                            {--type=all : Typ produktů (all, set, minifig)}
                            {--limit=100 : Limit zpracovaných záznamů}
                            {--force : Přepíše existující mapování}';

    protected $description = 'Komplexní nástroj pro správu mapování LEGO produktů';

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        // Připravíme parametry pro předání existujícím příkazům
        $type = $this->option('type');
        $limit = $this->option('limit');
        $force = $this->option('force') ? '--force' : '';

        if ($this->option('generate')) {
            // Voláme existující příkaz s parametry
            $this->info('Spouštím generování základních mapování...');
            Artisan::call('lego:generate-mappings', [
                '--type' => $type,
                '--limit' => $limit,
                '--force' => $force
            ]);
            $this->info(Artisan::output());
        }

        if ($this->option('fix')) {
            $this->info('Spouštím opravy mapování...');
            Artisan::call('lego:fix-minifig-mappings', [
                '--batch' => min(50, $limit),
                '--force' => $force
            ]);
            $this->info(Artisan::output());
        }

        if ($this->option('update')) {
            $this->info('Spouštím aktualizaci mapování...');
            Artisan::call('lego:update-minifig-mappings', [
                '--chunk' => min(500, $limit),
                '--limit' => $limit
            ]);
            $this->info(Artisan::output());
        }

        if ($this->option('sync')) {
            $this->syncProductsWithMappings();
        }

        if (
            !$this->option('generate') && !$this->option('fix') &&
            !$this->option('update') && !$this->option('sync')
        ) {
            $this->showStats();
        }

        return 0;
    }

    // Synchronizace produktů s mapováními - toto je nová funkce
    protected function syncProductsWithMappings()
    {
        $this->info('Synchronizace produktů s mapováními...');

        $type = $this->option('type');
        $limit = (int) $this->option('limit');

        $query = Product::query();

        if ($type !== 'all') {
            $query->where('product_type', $type);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $updated = 0;

        $query->chunk(100, function ($products) use (&$updated, $bar) {
            foreach ($products as $product) {
                $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->first();

                if ($mapping) {
                    // Zde můžeme aktualizovat produkt podle mapování
                    // Například kopírovat BrickEconomy ID jako vlastnost produktu, pokud takový sloupec máte
                    $updated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Synchronizace dokončena. Aktualizováno {$updated} produktů.");
    }

    // Statistiky mapování - můžeme použít logiku z ManageLegoMappingsCommand::showStats()
    protected function showStats()
    {
        $totalCount = LegoIdMapping::count();
        $completeCount = LegoIdMapping::whereNotNull('brickeconomy_id')->count();
        $brickLinkCount = LegoIdMapping::whereNotNull('bricklink_id')->count();

        $this->info("Statistiky mapování ID LEGO produktů:");
        $this->table(
            ['Typ', 'Počet'],
            [
                ['Celkem mapování', $totalCount],
                ['S BrickEconomy ID', $completeCount],
                ['S BrickLink ID', $brickLinkCount],
                ['Pouze Rebrickable ID', $totalCount - $completeCount],
            ]
        );

        // Několik posledních přidaných mapování
        $latestMappings = LegoIdMapping::orderBy('created_at', 'desc')->limit(5)->get();

        if ($latestMappings->isNotEmpty()) {
            $this->info("Posledních 5 přidaných mapování:");

            $headers = ['Rebrickable ID', 'BrickEconomy ID', 'BrickLink ID', 'Name', 'Přidáno'];
            $rows = [];

            foreach ($latestMappings as $mapping) {
                $rows[] = [
                    $mapping->rebrickable_id,
                    $mapping->brickeconomy_id,
                    $mapping->bricklink_id,
                    $mapping->name,
                    $mapping->created_at->format('Y-m-d H:i'),
                ];
            }

            $this->table($headers, $rows);
        }
    }
}
