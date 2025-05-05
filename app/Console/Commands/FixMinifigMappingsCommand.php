<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMinifigMappingsCommand extends Command
{
    protected $signature = 'lego:fix-minifig-mappings
                            {--check : Pouze vypíše statistiky bez provedení změn}';

    protected $description = 'Zkontroluje a opraví mapování minifigurek';

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $this->info('Kontroluji stav mapování minifigurek...');

        // 1. Zkontrolujme minifigurky v produktech, které nemají mapování
        $minifigProducts = Product::where('product_type', 'minifig')->get();
        $this->info("Celkem nalezeno minifigurek v tabulce products: " . $minifigProducts->count());

        // 2. Kolik z nich má mapování?
        $mappedCount = 0;
        $unmappedProductNums = [];

        foreach ($minifigProducts as $product) {
            $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->first();
            if ($mapping) {
                $mappedCount++;
            } else {
                $unmappedProductNums[] = $product->product_num;
            }
        }

        $this->info("- Minifigurek s mapováním: {$mappedCount}");
        $this->info("- Minifigurek bez mapování: " . ($minifigProducts->count() - $mappedCount));

        // 3. Kontrola formátu product_num pro minifigurky
        $nonFigFormat = 0;
        $wrongFormatNums = [];

        foreach ($minifigProducts as $product) {
            if (!preg_match('/^fig-\d+$/', $product->product_num)) {
                $nonFigFormat++;
                $wrongFormatNums[] = $product->product_num;
            }
        }

        $this->info("- Minifigurek s nesprávným formátem product_num: {$nonFigFormat}");

        // 4. Zkontrolujme existující mapování
        $mappings = LegoIdMapping::where('rebrickable_id', 'LIKE', 'fig-%')->get();
        $this->info("Celkem nalezeno záznamů v tabulce mapování pro minifigurky: " . $mappings->count());

        $withBrickEconomyCount = $mappings->whereNotNull('brickeconomy_id')->count();
        $withBrickLinkCount = $mappings->whereNotNull('bricklink_id')->count();

        $this->info("- S BrickEconomy ID: {$withBrickEconomyCount}");
        $this->info("- S BrickLink ID: {$withBrickLinkCount}");
        $this->info("- Bez žádného mapování: " . $mappings->whereNull('brickeconomy_id')->whereNull('bricklink_id')->count());

        // Pokud nechceme jen zkontrolovat, ale i opravit
        if (!$this->option('check')) {
            $this->info("\nProvádím opravy...");

            // 5. Vytvoříme chybějící mapování pro minifigurky
            $created = 0;
            foreach ($unmappedProductNums as $productNum) {
                $product = $minifigProducts->where('product_num', $productNum)->first();

                if ($product) {
                    $mapping = LegoIdMapping::create([
                        'rebrickable_id' => $product->product_num,
                        'name' => $product->name,
                        'notes' => 'Auto-generated: minifig without mapping (fix script)',
                    ]);

                    $created++;
                    $this->line("Vytvářím mapování pro: {$product->product_num} - {$product->name}");
                }
            }

            $this->info("Vytvořeno {$created} nových mapování.");

            // 6. Oprava nesprávných formátů (pouze pokud je to žádoucí)
            if (count($wrongFormatNums) > 0 && $this->confirm('Opravit nesprávné formáty product_num? To může způsobit nekonzistence!', false)) {
                $fixed = 0;

                foreach ($wrongFormatNums as $wrongNum) {
                    $product = $minifigProducts->where('product_num', $wrongNum)->first();

                    if ($product) {
                        // Vytvoření správného formátu fig-XXXX
                        $figId = preg_replace('/[^0-9]/', '', $product->product_num);
                        $newProductNum = 'fig-' . $figId;

                        // Kontrola, zda už neexistuje
                        $exists = Product::where('product_num', $newProductNum)->exists();

                        if (!$exists) {
                            $oldNum = $product->product_num;
                            $product->product_num = $newProductNum;
                            $product->save();

                            // Vytvořit mapování i pro tento produkt
                            LegoIdMapping::create([
                                'rebrickable_id' => $newProductNum,
                                'name' => $product->name,
                                'notes' => "Auto-fixed from: {$oldNum}",
                            ]);

                            $fixed++;
                            $this->line("Opraveno: {$oldNum} -> {$newProductNum}");
                        }
                    }
                }

                $this->info("Opraveno {$fixed} produktů s nesprávným formátem.");
            }
        }

        $this->info("\nHotovo!");
        return 0;
    }
}
