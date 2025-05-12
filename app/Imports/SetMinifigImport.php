<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetMinifigImport
{
    public function import($inventoriesPath, $inventoryMinifigsPath)
    {
        echo "Načítám mapování inventories na sety...\n";
        $setNums = Product::where('product_type', 'set')->pluck('product_num')->toArray();
        $inventoryToSetMap = $this->loadInventoriesToSetsMapping($inventoriesPath, $setNums);
        echo "Načteno " . count($inventoryToSetMap) . " mapování inventories na sety.\n";

        unset($setNums);

        echo "Načítám minifigurky z databáze...\n";
        $minifigsMap = Product::where('product_type', 'minifig')
            ->select('id', 'product_num')
            ->get()
            ->keyBy('product_num')
            ->map(function ($item) {
                return $item->id;
            })
            ->toArray();
        echo "Načteno " . count($minifigsMap) . " minifigurek.\n";

        $this->processInventoryMinifigs($inventoryMinifigsPath, $inventoryToSetMap, $minifigsMap);

        return true;
    }

    private function loadInventoriesToSetsMapping($path, $setNums)
    {
        $mapping = [];
        $setNumMap = array_flip($setNums);

        if (($handle = fopen($path, 'r')) !== false) {
            fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 3) {
                    $inventoryId = $data[0];
                    $setNum = $data[2];

                    if (isset($setNumMap[$setNum])) {
                        $set = Product::where('product_num', $setNum)
                            ->where('product_type', 'set')
                            ->select('id')
                            ->first();

                        if ($set) {
                            $mapping[$inventoryId] = $set->id;
                        }
                    }
                }
            }

            fclose($handle);
        }

        return $mapping;
    }

    private function processInventoryMinifigs($path, $inventoryToSetMap, $minifigsMap)
    {
        $batchSize = 1000;
        $batch = [];
        $count = 0;
        $totalCount = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            fgetcsv($handle);

            echo "Zpracovávám vazby mezi sety a minifigurkami...\n";

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 3) {
                    $inventoryId = $data[0];
                    $figNum = $data[1];
                    $quantity = intval($data[2]);

                    if (!isset($inventoryToSetMap[$inventoryId])) {
                        continue;
                    }

                    $setId = $inventoryToSetMap[$inventoryId];

                    if (!isset($minifigsMap[$figNum])) {
                        continue;
                    }

                    $minifigId = $minifigsMap[$figNum];

                    $batch[] = [
                        'parent_id' => $setId,
                        'minifig_id' => $minifigId,
                        'quantity' => $quantity,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $count++;

                    if (count($batch) >= $batchSize) {
                        $this->insertBatch($batch);
                        $totalCount += count($batch);
                        echo "Importováno $totalCount vazeb...\n";
                        $batch = [];

                        gc_collect_cycles();
                    }
                }
            }

            if (!empty($batch)) {
                $this->insertBatch($batch);
                $totalCount += count($batch);
            }

            echo "Import dokončen. Celkem importováno $totalCount vazeb.\n";

            fclose($handle);
        }
    }

    private function insertBatch($batch)
    {
        try {
            $chunks = array_chunk($batch, 100);
            foreach ($chunks as $chunk) {
                DB::table('set_minifigs')->insertOrIgnore($chunk);
            }
        } catch (\Exception $e) {
            echo 'Chyba při vkládání vazeb set-minifig: ' . $e->getMessage() . "\n";
            Log::error('Chyba při vkládání vazeb set-minifig: ' . $e->getMessage());
        }
    }
}
