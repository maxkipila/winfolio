<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLegoIdMappingsCommand extends Command
{
    protected $signature = 'lego:generate-mappings
                          {--chunk=100 : Počet produktů zpracovávaných v jedné dávce}
                          {--type=all : Typ produktů pro zpracování (all, set, minifig)}
                          {--limit=0 : Omezení počtu zpracovaných produktů}
                          {--offset=0 : Začít od určitého offsetu}';

    protected $description = 'Automatically generate mappings between Rebrickable and BrickEconomy IDs';

    public function handle()
    {
        // Nastavení
        $chunkSize = (int) $this->option('chunk');
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        // Zvýšení limitu paměti
        ini_set('memory_limit', '1G');

        // Vypnutí query logu pro úsporu paměti
        DB::disableQueryLog();

        // Definice query
        $query = Product::query();

        // Filtrování podle typu
        if ($type === 'set') {
            $query->where('product_type', 'set');
        } elseif ($type === 'minifig') {
            $query->where('product_type', 'minifig');
        }

        // Aplikace offsetu
        if ($offset > 0) {
            $query->skip($offset);
        }

        // Aplikace limitu
        if ($limit > 0) {
            $query->take($limit);
        }

        // Počet produktů ke zpracování
        $totalCount = $query->count();

        if ($limit > 0 && $totalCount > $limit) {
            $totalCount = $limit;
        }

        $this->info("Celkem ke zpracování: {$totalCount} produktů");
        $this->info("Zpracovávám po {$chunkSize} produktech");

        // Progress bar
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $createdCount = 0;
        $skippedCount = 0;

        // Zpracování po dávkách
        $query->orderBy('id')->chunk($chunkSize, function ($products) use (&$createdCount, &$skippedCount, $bar) {
            $mappingsToInsert = [];

            foreach ($products as $product) {
                // Přeskočit, pokud už mapování existuje
                $existingMapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->exists();

                if ($existingMapping) {
                    $skippedCount++;
                } else {
                    if ($product->product_type === 'set') {
                        // Pro sety předpokládáme stejné ID
                        $mappingsToInsert[] = [
                            'rebrickable_id' => $product->product_num,
                            'brickeconomy_id' => $product->product_num,
                            'name' => $product->name,
                            'notes' => 'Auto-generated: set ID mapping',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $createdCount++;
                    } elseif ($product->product_type === 'minifig') {
                        // Pro minifigurky vytvoříme záznam bez BrickEconomy ID, jen pro evidenci
                        $mappingsToInsert[] = [
                            'rebrickable_id' => $product->product_num,
                            'name' => $product->name,
                            'notes' => 'Auto-generated: minifig without mapping',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $createdCount++;
                    }
                }

                $bar->advance();
            }

            // Hromadné vložení nasbíraných mapování
            if (!empty($mappingsToInsert)) {
                // Vkládáme po menších dávkách pro lepší stabilitu
                foreach (array_chunk($mappingsToInsert, 500) as $chunk) {
                    LegoIdMapping::insert($chunk);
                }
            }

            // Vyčištění paměti
            unset($mappingsToInsert);
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();

        // Přidáme ručně mapované minifigurky
        if ($type !== 'set') {
            $this->info('Přidávám základní mapování pro populární minifigurky...');

            $minifigMappings = [
                // Star Wars minifigurky (jen několik příkladů)
                'fig-015085' => 'sw0632', // Magister_Shimshard
                'fig-015087' => 'sw0633', // Scout_Trooper
                'fig-015089' => 'sw0635', // Ender_Explorer
                'fig-015091' => 'sw0637', // Gabby_Party_Hats
                // Harry Potter minifigurky
                'fig-015111' => 'hp0149', // Ron Weasley
                'fig-015112' => 'hp0150', // Harry Potter
                'fig-015113' => 'hp0151', // Leanne
                // Přidejte další známé mapování
            ];

            $customCreated = 0;

            foreach ($minifigMappings as $rebrickableId => $brickEconomyId) {
                $mapping = LegoIdMapping::updateOrCreate(
                    ['rebrickable_id' => $rebrickableId],
                    [
                        'brickeconomy_id' => $brickEconomyId,
                        'notes' => 'Manual mapping: popular minifig'
                    ]
                );

                if ($mapping->wasRecentlyCreated) {
                    $customCreated++;
                }
            }

            $this->info("Přidáno {$customCreated} mapování pro populární minifigurky.");
        }

        // Statistika
        $this->info("============================================");
        $this->info("VYTVOŘENO: {$createdCount}");
        $this->info("PŘESKOČENO (již existuje): {$skippedCount}");

        $total = LegoIdMapping::count();
        $withBE = LegoIdMapping::whereNotNull('brickeconomy_id')->count();

        $this->info("CELKEM MAPOVÁNÍ: {$total}");
        $this->info("S BRICKECONOMY ID: {$withBE}");
        $this->info("POKRYTÍ: " . round(($withBE / max(1, $total)) * 100, 2) . "%");

        return 0;
    }
}
