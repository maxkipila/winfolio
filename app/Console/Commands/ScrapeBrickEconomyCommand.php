<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Scrapers\BrickEconomyScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeBrickEconomyCommand extends Command
{
    protected $signature = 'scrape:brickeconomy
                        {product_ids?* : Seznam ID produktů (brickeconomy_id)}
                        {--file= : Cesta k souboru se seznamem ID (jeden na řádek)}
                        {--save : Uložit data do databáze (výchozí: true)}
                        {--market : Scrapovat také aktuální nabídky z tržišť}
                        {--history : Scrapovat historická data pro grafy}
                        {--delay=2 : Prodleva mezi požadavky v sekundách}
                        {--limit=10 : Maximální počet produktů ke scrapování (při prázdném vstupu)}
                        {--output= : Uložit výsledky do JSON souboru}';

    protected $description = 'Scrapuje data o LEGO produktech z BrickEconomy.com';

    public function handle()
    {
        // Vytvoříme scraper
        $scraper = new BrickEconomyScraper();

        // Získáme paramery
        $productIds = $this->argument('product_ids');
        $file = $this->option('file');
        $saveToDb = (bool) $this->option('save');  // Oprava: explicitní konverze na boolean
        $includeMarket = $this->option('market') || $saveToDb;
        $includeHistory = $this->option('history');
        $delay = (int) $this->option('delay');
        $outputFile = $this->option('output');
        $limit = (int) $this->option('limit');

        // Pro účely debugování
        $this->info("Režim ukládání do DB: " . ($saveToDb ? 'ZAPNUTO' : 'VYPNUTO'));

        // Načtení ID produktů
        $productIdsToScrape = $this->getProductIdsToScrape($productIds, $file, $limit);

        if (empty($productIdsToScrape)) {
            $this->error('Nebyly zadány žádné produkty ke scrapování.');
            return 1;
        }

        $this->info('Začínám scrapování ' . count($productIdsToScrape) . ' produktů z BrickEconomy.com');

        // Progress bar
        $bar = $this->output->createProgressBar(count($productIdsToScrape));
        $bar->start();

        $results = [];
        $savedCount = 0;

        // Procházení produktů
        foreach ($productIdsToScrape as $productId) {
            // Získání detailů produktu
            $productData = $scraper->getProductDetails($productId);

            if ($productData) {
                $product = null;

                if ($saveToDb) {
                    $product = $scraper->saveProductToDatabase($productData);

                    if ($product) {
                        $savedCount++;
                    }
                }

                // Scrapování tržních nabídek
                $marketListings = [];
                if ($includeMarket && $product) {
                    sleep($delay); // Zpoždění před dalším požadavkem
                    $marketListings = $scraper->getMarketListings($productId);

                    if ($saveToDb && !empty($marketListings)) {
                        $scraper->saveMarketListings($product->id, $marketListings);
                    }
                }

                // Scrapování historických dat
                $historyData = [];
                if ($includeHistory && $product) {
                    sleep($delay); // Zpoždění před dalším požadavkem
                    $historyData = $scraper->getHistoricalPrices($productId);

                    if ($saveToDb && !empty($historyData)) {
                        // Uložení historických dat
                        $this->saveHistoricalData($product->id, $historyData);
                    }
                }

                // Přidání výsledku
                $results[$productId] = [
                    'success' => true,
                    'product_id' => $product ? $product->id : null,
                    'data' => $productData,
                    'market_listings_count' => count($marketListings),
                    'history_points_count' => count($historyData)
                ];

                $this->newLine();
                $this->info("✓ {$productId}: Úspěšně staženo" . ($product ? " a uloženo (ID: {$product->id})" : ""));
            } else {
                $results[$productId] = [
                    'success' => false,
                    'message' => "Chyba při stahování dat"
                ];

                $this->newLine();
                $this->error("✗ {$productId}: Chyba při stahování dat");
            }

            $bar->advance();

            // Zpoždění mezi požadavky
            if ($delay > 0) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Souhrn
        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $failCount = count($results) - $successCount;

        $this->info("Scrapování dokončeno: {$successCount} úspěšných, {$failCount} neúspěšných");
        $this->info("Uloženo do databáze: {$savedCount} produktů");

        // Uložení výsledků do souboru
        if ($outputFile) {
            file_put_contents($outputFile, json_encode($results, JSON_PRETTY_PRINT));
            $this->info("Výsledky uloženy do souboru {$outputFile}");
        }

        return 0;
    }

    /**
     * Získá seznam ID produktů ke scrapování
     */
    private function getProductIdsToScrape(?array $commandLineIds, ?string $filePath, int $limit): array
    {
        $productIds = [];

        // 1. Z příkazové řádky
        if (!empty($commandLineIds)) {
            $productIds = array_merge($productIds, $commandLineIds);
        }

        // 2. Ze souboru
        if ($filePath && file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $fileIds = array_filter(
                explode(PHP_EOL, $fileContent),
                fn($line) => !empty(trim($line))
            );

            $productIds = array_merge($productIds, $fileIds);
        }

        // 3. Pokud nebyl zadán žádný produkt, můžeme vzít posledních N produktů z databáze,
        // které nemají aktuální cenu
        if (empty($productIds)) {
            $recentProducts = Product::whereDoesntHave('prices', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();

            foreach ($recentProducts as $product) {
                $productIds[] = $product->product_num;
            }
        }

        // Odstranění duplikátů a prázdných hodnot
        return array_unique(array_filter(array_map('trim', $productIds)));
    }

    /**
     * Uloží historická data o cenách
     */
    private function saveHistoricalData(int $productId, array $historyData): void
    {
        if (empty($historyData)) {
            return;
        }

        // Předpokládáme, že existuje model HistoricalPrice
        foreach ($historyData as $point) {
            \App\Models\HistoricalPrice::updateOrCreate(
                [
                    'product_id' => $productId,
                    'date' => $point['date']
                ],
                [
                    'value' => $point['value'],
                    'year' => $point['year'],
                    'month' => $point['month']
                ]
            );
        }
    }
}
