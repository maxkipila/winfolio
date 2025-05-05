<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateMinifigMappingsCommand extends Command
{
    protected $signature = 'lego:generate-minifig-mappings
                           {--prefix=sw : Jaký prefix používat pro automatické mapování (např. sw, hp)}
                           {--batch=50 : Kolik minifigurek zpracovat najednou}
                           {--force : Přepsat existující mapování}';

    protected $description = 'Vytvoří automatické mapování pro minifigurky podle pravidel';

    protected $seriesPrefixes = [
        'Star Wars' => 'sw',
        'Harry Potter' => 'hp',
        'Batman' => 'bat',
        'Marvel' => 'sh',
        'Super Heroes' => 'sh',
        'Ninjago' => 'njo',
        'Lord of the Rings' => 'lor',
        'City' => 'cty',
        'Pirates of the Caribbean' => 'poc',
    ];

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $prefix = $this->option('prefix');
        $batch = (int) $this->option('batch');
        $force = $this->option('force');

        $this->info("Generuji mapování pro minifigurky s prefixem {$prefix}...");

        // Vybrat minifigurky bez mapování nebo kde můžeme přepsat
        $query = Product::where('product_type', 'minifig')
            ->whereNotNull('product_num')
            ->where('product_num', 'LIKE', 'fig-%');

        if (!$force) {
            // Pouze ty, které ještě nemají BrickEconomy mapování
            $mappedIds = LegoIdMapping::whereNotNull('brickeconomy_id')
                ->pluck('rebrickable_id')
                ->toArray();

            $query->whereNotIn('product_num', $mappedIds);
        }

        $totalCount = $query->count();
        $this->info("Nalezeno {$totalCount} minifigurek ke zpracování");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $query->chunk($batch, function ($products) use (&$created, &$updated, &$skipped, $bar, $prefix, $force) {
            foreach ($products as $product) {
                // Získat číselný ID z product_num (např. z fig-12345 získáme 12345)
                preg_match('/fig-(\d+)/', $product->product_num, $matches);
                $numericId = $matches[1] ?? null;

                if (!$numericId) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Vytvoříme BrickEconomy ID ve formátu sw0001
                $brickEconomyId = $prefix . str_pad($numericId, 4, '0', STR_PAD_LEFT);

                // Zkusit najít existující mapování
                $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->first();

                if ($mapping) {
                    if ($force || !$mapping->brickeconomy_id) {
                        $mapping->brickeconomy_id = $brickEconomyId;
                        $mapping->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    // Vytvořit nové mapování
                    LegoIdMapping::create([
                        'rebrickable_id' => $product->product_num,
                        'brickeconomy_id' => $brickEconomyId,
                        'name' => $product->name,
                        'notes' => 'Auto-generated with prefix: ' . $prefix,
                    ]);
                    $created++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info("Vytvořeno: {$created}");
        $this->info("Aktualizováno: {$updated}");
        $this->info("Přeskočeno: {$skipped}");

        return 0;
    }
}
