<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Price;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PricesImporter extends Command
{
    protected $signature = 'prices:import
                           {--months=12 : Počet měsíců historie}
                           {--type=all : Typ produktů (all, set, minifig)}
                           {--chunk=100 : Velikost zpracovávaného bloku}
                           {--limit=1000 : Limit počtu produktů}
                           {--offset=0 : Začít od konkrétního offsetu}
                           {--scrape : Stáhnout ceny z online zdrojů}
                           {--force : Vynutit aktualizaci i pro produkty s existujícími cenami}';

    protected $description = 'Import cenových údajů pro produkty';

    public function handle()
    {
        $this->info('Import cenových údajů pro produkty...');

        $limit = (int)$this->option('limit');
        $offset = (int)$this->option('offset');
        $chunk = (int)$this->option('chunk');
        $type = $this->option('type');
        $months = (int)$this->option('months');
        $scrape = $this->option('scrape');
        $force = $this->option('force');

        // Zjistíme počet produktů bez cen
        $productsQuery = Product::query();

        if ($type !== 'all') {
            $productsQuery->where('product_type', $type);
        }

        if (!$force) {
            $productsQuery->whereDoesntHave('prices');
        }

        if ($offset > 0) {
            $productsQuery->skip($offset);
        }

        if ($limit > 0) {
            $productsQuery->take($limit);
        }

        $totalProducts = $productsQuery->count();
        $this->info("Celkem ke zpracování: {$totalProducts} produktů");

        if ($totalProducts === 0) {
            $this->info('Žádné produkty nevyžadují aktualizaci cen.');
            return Command::SUCCESS;
        }

        if ($scrape) {
            // Scraping aktuálních cen z online zdrojů
            $this->info('Stahuji aktuální ceny z online zdrojů...');

            $scrapingChunks = ceil($totalProducts / $chunk);
            $this->info("Zpracování proběhne ve {$scrapingChunks} blocích");

            for ($i = 0; $i < $scrapingChunks; $i++) {
                $currentOffset = $offset + ($i * $chunk);
                $currentLimit = min($chunk, $totalProducts - ($i * $chunk));

                $this->info("Zpracovávám blok " . ($i + 1) . " z {$scrapingChunks} (offset: {$currentOffset}, limit: {$currentLimit})");

                $this->call('scrape:brickeconomy-bulk', [
                    '--limit' => $currentLimit,
                    '--offset' => $currentOffset,
                    '--chunk' => min($chunk, 50), // Menší chunk pro spolehlivější scraping
                    '--type' => $type,
                    '--throttle' => 5, // Omezení počtu požadavků za minutu
                    '--delay' => 2, // Zpoždění mezi požadavky v sekundách
                ]);
            }
        } else {
            // Generování historických cen
            $this->info('Generuji historické cenové údaje...');
            $this->generateHistoricalPrices($productsQuery, $months, $chunk);
        }

        // Agregace cen pro vykreslení grafů
        $this->info('Agreguji cenové údaje pro grafy...');

        // Pouze pokud jsme negenerovali ceny scrapingem, potřebujeme je agregovat
        if (!$scrape) {
            $this->generateAggregatedPrices($months);
        }

        $this->info('Import cenových údajů byl úspěšně dokončen.');
        return Command::SUCCESS;
    }

    /**
     * Generuje historické ceny pro produkty
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Query pro výběr produktů
     * @param int $months Počet měsíců historie
     * @param int $chunkSize Velikost zpracovávaného bloku
     */
    private function generateHistoricalPrices($query, int $months, int $chunkSize): void
    {
        $faker = Faker::create();
        $today = Carbon::today();
        $priceData = [];
        $processedCount = 0;

        $totalProducts = $query->count();
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        // Zpracování po blocích pro efektivnější správu paměti
        $query->chunkById($chunkSize, function ($products) use ($faker, $today, $months, &$priceData, &$processedCount, $bar) {
            foreach ($products as $product) {
                // Generujeme základní cenu podle typu produktu
                if ($product->product_type === 'set') {
                    $basePrice = $product->num_parts
                        ? min(max($product->num_parts * 0.5, 10), 1000)
                        : $faker->randomFloat(2, 20, 500);
                    $condition = $faker->randomElement(['New', 'Used', 'Sealed']);
                } else { // minifig nebo jiný typ
                    $basePrice = $faker->randomFloat(2, 1, 50);
                    $condition = $faker->randomElement(['Mint', 'Good', 'Played']);
                }

                // Aktuální cena
                $priceData[] = [
                    'product_id' => $product->id,
                    'retail' => round($basePrice * 1.3, 2),
                    'wholesale' => round($basePrice * 0.7, 2),
                    'value' => round($basePrice, 2),
                    'condition' => $condition,
                    'type' => 'market',
                    'created_at' => $today,
                    'updated_at' => $today,
                ];

                // Generujeme historické ceny
                for ($i = 1; $i <= $months; $i++) {
                    // Generujeme variaci pro cenu (±15%)
                    $variationFactor = $faker->randomFloat(2, 0.85, 1.15);
                    $historicalPrice = round($basePrice * $variationFactor, 2);

                    $priceData[] = [
                        'product_id' => $product->id,
                        'retail' => round($historicalPrice * 1.3, 2),
                        'wholesale' => round($historicalPrice * 0.7, 2),
                        'value' => $historicalPrice,
                        'condition' => $condition,
                        'type' => 'market',
                        'created_at' => $today->copy()->subMonths($i)->startOfMonth(),
                        'updated_at' => $today->copy()->subMonths($i)->startOfMonth(),
                    ];
                }

                $processedCount++;
                $bar->advance();

                // Hromadné vložení po dosažení určitého počtu záznamů
                if (count($priceData) >= 1000) {
                    Price::insert($priceData);
                    $priceData = [];
                }
            }

            // Vložení zbývajících cen
            if (!empty($priceData)) {
                Price::insert($priceData);
                $priceData = [];
            }

            // Vyčištění paměti
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine();
        $this->info("Vygenerováno {$processedCount} cenových historií.");
    }

    /**
     * Generuje agregované ceny pro vykreslení grafů
     * 
     * @param int $months Počet měsíců historie
     */
    private function generateAggregatedPrices(int $months): void
    {
        $today = Carbon::today();

        for ($i = 0; $i <= $months; $i++) {
            $targetDate = $today->copy()->subMonths($i)->startOfMonth()->toDateString();

            $this->info("Generuji agregované ceny pro {$targetDate}...");
            $this->call('prices:aggregate', [
                '--date' => $targetDate,
                '--force' => true
            ]);
        }
    }
}
