<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Scrapers\BrickEconomyScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScrapeMappedMinifigsCommand extends Command
{
    protected $signature = 'scrape:mapped-minifigs
                            {--limit=10 : Maximum number of minifigs to process}
                            {--delay=3 : Delay between requests in seconds}
                            {--offset=0 : Start from this offset in the database query}
                            {--force : Force scrape even if price already exists}';

    protected $description = 'Scrape data from BrickEconomy for minifigs with mappings';

    public function handle()
    {
        // Nastavení
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $offset = (int) $this->option('offset');
        $force = $this->option('force');

        // Inicializace scraperu
        $scraper = new BrickEconomyScraper();

        $this->info("Začínám scrapování dat pro minifigurky s mapováním");

        // Získání záznamů s již existujícím BrickEconomy ID
        $query = LegoIdMapping::whereNotNull('brickeconomy_id')
            ->where('rebrickable_id', 'LIKE', 'fig-%')
            ->orderBy('id');

        // Aplikace offsetu
        if ($offset > 0) {
            $query->skip($offset);
        }

        // Aplikace limitu
        if ($limit > 0) {
            $query->take($limit);
        }

        // Počet záznamů ke zpracování
        $totalCount = $query->count();
        $this->info("Celkem ke zpracování: {$totalCount} minifigurek");

        // Progress bar
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        // Zpracování jeden po druhém
        $query->chunk(10, function ($mappings) use ($scraper, $delay, &$successCount, &$errorCount, $bar, $force) {
            foreach ($mappings as $mapping) {
                try {
                    $rebrickableId = $mapping->rebrickable_id;
                    $brickEconomyId = $mapping->brickeconomy_id;

                    // Kontrola, zda již existuje produkt
                    $product = DB::table('products')->where('product_num', $rebrickableId)->first();

                    if (!$product) {
                        $this->warn("Produkt {$rebrickableId} neexistuje v databázi, přeskakuji.");
                        $errorCount++;
                        $bar->advance();
                        continue;
                    }

                    // Kontrola, zda už existuje cena (pokud není force)
                    if (!$force) {
                        $recentPrice = DB::table('prices')
                            ->where('product_id', $product->id)
                            ->where('created_at', '>=', now()->subDays(7))
                            ->exists();

                        if ($recentPrice) {
                            $bar->advance();
                            continue;
                        }
                    }

                    // Stáhneme data z BrickEconomy
                    $productData = $scraper->getProductDetails($brickEconomyId);

                    if ($productData) {
                        // Přidáme/aktualizujeme Rebrickable ID
                        $productData['product_num'] = $rebrickableId;

                        // Uložíme data
                        $saved = $scraper->saveProductToDatabase($productData);

                        if ($saved) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Chyba při zpracování minifigurky {$mapping->rebrickable_id}: " . $e->getMessage());
                    $errorCount++;
                }

                $bar->advance();

                // Zpoždění mezi požadavky
                if ($delay > 0) {
                    sleep($delay);
                }
            }

            // Vyčištění paměti
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();

        $this->info("Úspěšně zpracováno: {$successCount}");
        $this->info("Chyby: {$errorCount}");

        return 0;
    }
}
