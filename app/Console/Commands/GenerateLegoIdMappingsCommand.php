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
                          {--offset=0 : Začít od určitého offsetu}
                          {--force : Přepsat existující mapování}';

    protected $description = 'Vytvoření mapování LEGO ID pro produkty';

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $chunkSize = (int) $this->option('chunk');
        $force = $this->option('force');

        if ($type === 'all') {
            $this->info('Zpracování všech typů produktů');

            $this->info('Krok 1: Sety');
            $this->processProducts('set', $limit, $offset, $chunkSize, $force);

            $this->info('Krok 2: Minifigurky');
            $this->processProducts('minifig', $limit, $offset, $chunkSize, $force);
        } else {
            $this->processProducts($type, $limit, $offset, $chunkSize, $force);
        }

        $this->info("Generování mapování dokončeno.");
        return Command::SUCCESS;
    }

    protected function processProducts($type, $limit, $offset, $chunkSize, $force)
    {
        $query = Product::where('product_type', $type);

        if ($offset > 0) {
            $query->skip($offset);
        }
        if ($limit > 0) {
            $query->take($limit);
        }

        $totalCount = $query->count();
        $this->info("Počet produktů (typ {$type}): {$totalCount}");

        if ($totalCount === 0) {
            $this->info("Žádné produkty pro typ '{$type}'");
            return;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $query->orderBy('id')->chunk($chunkSize, function ($products) use (&$created, &$skipped, &$errors, $bar, $force, $type) {
            foreach ($products as $product) {
                try {
                    $productId = $product->id;
                    $productNum = $product->product_num;

                    $brickeconomyId = null;

                    if ($type === 'set') {
                        $brickeconomyId = $productNum;
                    }


                    $existingMapping = Product::where('brickeconomy_id', $brickeconomyId)->first();

                    if ($existingMapping && !$force) {
                        $skipped++;
                    } else {
                      
                        $product->update([
                            'brickeconomy_id' => $brickeconomyId,
                        ]);

                        $created++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Chyba při zpracování produktu {$product->id}: " . $e->getMessage());
                }

                $bar->advance();
            }

            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();

        $this->info("Dokončeno zpracování typu {$type}:");
        $this->info("- Vytvořeno/aktualizováno: {$created}");
        $this->info("- Přeskočeno (již existuje): {$skipped}");
        $this->info("- Chyby: {$errors}");
    }
}
