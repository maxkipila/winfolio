<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use App\Scrapers\BrickEconomyScraper;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualPricesBrickEconomyCommand extends Command
{
    protected $signature = 'actual:prices-brickeconomy
                            {--type=all : Type of products to scrape (all, set, minifig)}
                            {--limit=100 : Maximum number of products to process}
                            {--chunk=10 : Number of products to process in one batch}
                            {--delay=3 : Delay between batches in seconds}
                            {--offset=0 : Start from this offset in the database query}
                            {--use-existing-mappings : Only scrape products that already have BrickEconomy ID mappings}
                            {--force : Force scrape even if price already exists}
                            {--throttle=5 : Maximum parallel requests per minute}';

    protected $description = 'Bulk scrape data from BrickEconomy for all products';

    protected BrickEconomyScraper $scraper;
    protected array $successProducts = [];
    protected array $failedProducts = [];
    protected Carbon $startTime;

    public function __construct()
    {
        parent::__construct();
        $this->scraper = new BrickEconomyScraper();
    }

    public function handle()
    {
        $this->startTime = now();
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $chunk = (int) $this->option('chunk');
        $delay = (int) $this->option('delay');
        $useExistingMappings = $this->option('use-existing-mappings');
        $force = $this->option('force');
        $throttle = (int) $this->option('throttle');

        $this->info("Začínám hromadné scrapování dat z BrickEconomy");
        $this->info("Typ produktů: {$type}, Limit: {$limit}, Offset: {$offset}, Chunk: {$chunk}");

        // Definujeme základní query
        $query = Product::query();

        // Filtrujeme podle typu
        if ($type !== 'all') {
            $query->where('product_type', $type);
        }

        // Přeskočíme produkty s cenami z posledních 7 dní, pokud není zadán --force
        if (!$force) {
            $query->whereDoesntHave('prices', function ($priceQuery) {
                $priceQuery->where('created_at', '>=', now()->subDays(7));
            });
        }

        if ($useExistingMappings) {
            $brickEconomyIds = LegoIdMapping::whereNotNull('brickeconomy_id')
                ->pluck('rebrickable_id')
                ->toArray();

            $query->whereIn('product_num', $brickEconomyIds);
        }

        // Počet produktů ke zpracování
        $totalProducts = $query->count();
        $this->info("Celkem nalezeno produktů ke zpracování: {$totalProducts}");

        if ($limit > 0 && $limit < $totalProducts) {
            $this->info("Omezeno na maximálně {$limit} produktů");
            $totalProducts = $limit;
        }

        // Progress bar
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        // Vypneme query log pro úsporu paměti
        DB::disableQueryLog();

        // Časovač pro throttling
        $requestCount = 0;
        $lastRequestTime = now();

        // Zpracování po částech
        $processed = 0;
        $query->orderBy('id')->skip($offset)->chunk($chunk, function ($products) use (&$processed, $limit, $delay, $bar, &$requestCount, &$lastRequestTime, $throttle) {
            // Přeskočíme, pokud jsme už dosáhli limitu
            if ($limit > 0 && $processed >= $limit) {
                return false;
            }

            // Zpracujeme aktuální dávku
            foreach ($products as $product) {
                // Přeskočíme, pokud jsme už dosáhli limitu
                if ($limit > 0 && $processed >= $limit) {
                    break;
                }

                // Kontrola throttlingu
                if ($requestCount >= $throttle) {
                    $elapsed = now()->diffInSeconds($lastRequestTime);
                    if ($elapsed < 60) {
                        $sleepTime = 60 - $elapsed;
                        $this->line("Throttling - čekám {$sleepTime} sekund...");
                        sleep($sleepTime);
                    }
                    $requestCount = 0;
                    $lastRequestTime = now();
                }

                // Získáme BrickEconomy ID pro tento produkt
                $brickEconomyId = $this->getBrickEconomyId($product);

                if ($brickEconomyId) {
                    try {
                        // Navýšíme počet požadavků
                        $requestCount++;

                        // Stáhneme data z BrickEconomy
                        $productData = $this->scraper->getProductDetails($brickEconomyId);

                        if ($productData) {
                            // Připravíme data a uložíme
                            $productData['product_num'] = $product->product_num;
                            $savedProduct = $this->scraper->saveProductToDatabase($productData);

                            if ($savedProduct) {
                                $this->successProducts[] = [
                                    'id' => $product->id,
                                    'product_num' => $product->product_num,
                                    'brick_economy_id' => $brickEconomyId
                                ];
                            } else {
                                $this->failedProducts[] = [
                                    'id' => $product->id,
                                    'product_num' => $product->product_num,
                                    'reason' => 'Failed to save data'
                                ];
                            }
                        } else {
                            $this->failedProducts[] = [
                                'id' => $product->id,
                                'product_num' => $product->product_num,
                                'reason' => 'Failed to fetch data'
                            ];
                        }
                    } catch (Exception $e) {
                        $this->failedProducts[] = [
                            'id' => $product->id,
                            'product_num' => $product->product_num,
                            'reason' => 'Exception: ' . $e->getMessage()
                        ];

                        Log::error("Chyba při zpracování produktu {$product->product_num}: " . $e->getMessage());
                    }
                } else {
                    $this->failedProducts[] = [
                        'id' => $product->id,
                        'product_num' => $product->product_num,
                        'reason' => 'No BrickEconomy ID'
                    ];
                }

                $processed++;
                $bar->advance();

                // Vyčištění paměti
                if ($processed % 10 === 0) {
                    gc_collect_cycles();
                }
            }

            // Zpoždění mezi dávkami
            if ($delay > 0) {
                sleep($delay);
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Výpis statistik
        $this->info("Zpracování dokončeno!");
        $this->info("Doba zpracování: " . $this->startTime->diffForHumans(now()));
        $this->info("Úspěšně zpracováno: " . count($this->successProducts));
        $this->info("Neúspěšně zpracováno: " . count($this->failedProducts));

        // Detaily chyb
        if (!empty($this->failedProducts)) {
            $this->info("Top 10 chyb:");
            $reasons = [];
            foreach ($this->failedProducts as $failed) {
                $reason = $failed['reason'] ?? 'Unknown';
                if (!isset($reasons[$reason])) {
                    $reasons[$reason] = 0;
                }
                $reasons[$reason]++;
            }

            arsort($reasons);
            $i = 0;
            foreach ($reasons as $reason => $count) {
                $this->info(" - {$reason}: {$count}x");
                $i++;
                if ($i >= 10) break;
            }
        }

        return 0;
    }

    /**
     * Získá BrickEconomy ID pro produkt
     */
    protected function getBrickEconomyId(Product $product): ?string
    {
        // Zkusíme nejdřív najít v mapování
        $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->first();
        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        // Pokud nemáme mapování, zkusíme odvodit podle typu
        if ($product->product_type === 'set') {
            // Pro sety je formát často stejný
            return $product->product_num;
        } elseif ($product->product_type === 'minifig') {
            // Pro minifigurky zkusíme najít v mapování podle jména
            $mapping = LegoIdMapping::where('name', 'LIKE', $product->name)
                ->whereNotNull('brickeconomy_id')
                ->first();

            if ($mapping) {
                return $mapping->brickeconomy_id;
            }

            // Nemáme mapování, musíme ho vytvořit ručně nebo přeskočit
            return null;
        }

        return null;
    }
}
