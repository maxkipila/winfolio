<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use App\Scrapers\BrickEconomyScraper;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyPricesActualCommand extends Command
{
    protected $signature = 'daily:prices-actual
                            {--limit=100 : Maximum number of products to process}
                            {--chunk=10 : Number of products to process in one batch}
                            {--delay=3 : Delay between batches in seconds}
                            {--offset=0 : Start from this offset in the database query}
                            {--throttle=5 : Maximum parallel requests per minute}';

    protected $description = 'Daily check for price changes on BrickEconomy and update if different';

    protected BrickEconomyScraper $scraper;
    protected array $successProducts = [];
    protected array $failedProducts = [];
    protected array $updatedProducts = [];
    protected Carbon $startTime;

    public function __construct()
    {
        parent::__construct();
        $this->scraper = new BrickEconomyScraper();
    }

    public function handle()
    {
        $this->startTime = now();
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $chunk = (int) $this->option('chunk');
        $delay = (int) $this->option('delay');
        $throttle = (int) $this->option('throttle');

        $this->info("Začínám denní kontrolu změn cen na BrickEconomy");
        $this->info("Limit: {$limit}, Offset: {$offset}, Chunk: {$chunk}");

        // Získáme rebrickable_id produktů, které mají brickeconomy_id
        $brickEconomyIds = LegoIdMapping::whereNotNull('brickeconomy_id')
            ->pluck('rebrickable_id')
            ->toArray();

        // Definujeme základní query pro produkty s existujícími cenami a BrickEconomy ID
        $query = Product::query()
            ->whereHas('prices') // Pouze produkty, které mají ceny
            ->whereIn('product_num', $brickEconomyIds); // Pouze produkty s BrickEconomy ID

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

            foreach ($products as $product) {
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

                // Získáme BrickEconomy ID
                $brickEconomyId = $this->getBrickEconomyId($product);

                if ($brickEconomyId) {
                    try {
                        $requestCount++;
                        $productData = $this->scraper->getProductDetails($brickEconomyId);

                        if ($productData) {
                            $productData['product_num'] = $product->product_num;

                            // Získáme poslední cenu produktu
                            $lastPrice = Price::where('product_id', $product->id)
                                ->orderBy('created_at', 'desc')
                                ->first();

                            // Porovnáme nové ceny s poslední cenou
                            $newPriceData = $productData['price_data'] ?? [];
                            $hasPriceChanged = $this->hasPriceChanged($lastPrice, $newPriceData);

                            if ($hasPriceChanged) {
                                // Uložíme novou cenu
                                $savedProduct = $this->scraper->saveProductToDatabase($productData);

                                if ($savedProduct) {
                                    $this->updatedProducts[] = [
                                        'id' => $product->id,
                                        'product_num' => $product->product_num,
                                        'brick_economy_id' => $brickEconomyId
                                    ];
                                    $this->successProducts[] = [
                                        'id' => $product->id,
                                        'product_num' => $product->product_num,
                                        'brick_economy_id' => $brickEconomyId
                                    ];
                                } else {
                                    $this->failedProducts[] = [
                                        'id' => $product->id,
                                        'product_num' => $product->product_num,
                                        'reason' => 'Failed to save updated price'
                                    ];
                                }
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
        $this->info("Denní kontrola dokončena!");
        $this->info("Doba zpracování: " . $this->startTime->diffForHumans(now()));
        $this->info("Úspěšně zpracováno: " . count($this->successProducts));
        $this->info("Aktualizováno cen: " . count($this->updatedProducts));
        $this->info("Neúspěšně zpracováno: " . count($this->failedProducts));

        // Detaily chyb
        if (!empty($this->failedProducts)) {
            $this->info("Top 10 chyb:");
            $reasons = [];
            foreach ($this->failedProducts as $failed) {
                $reason = $failed['reason'] ?? 'Unknown';
                $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
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
        $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)->first();
        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        if ($product->product_type === 'set') {
            return $product->product_num;
        } elseif ($product->product_type === 'minifig') {
            $mapping = LegoIdMapping::where('name', 'LIKE', $product->name)
                ->whereNotNull('brickeconomy_id')
                ->first();

            if ($mapping) {
                return $mapping->brickeconomy_id;
            }
        }

        return null;
    }

    /**
     * Porovná poslední cenu s novými cenovými údaji
     */
    protected function hasPriceChanged(?Price $lastPrice, array $newPriceData): bool
    {
        if (!$lastPrice) {
            return true; // Pokud neexistuje poslední cena, považujeme to za změnu
        }

        $oldValue = $lastPrice->value ?? null;
        $oldRetail = $lastPrice->retail ?? null;
        $oldWholesale = $lastPrice->wholesale ?? null;

        $newValue = $newPriceData['value'] ?? null;
        $newRetail = $newPriceData['retail'] ?? null;
        $newWholesale = $newPriceData['wholesale'] ?? null;

        // Porovnáme hodnoty (přihlížíme k null hodnotám)
        return $oldValue != $newValue ||
            $oldRetail != $newRetail ||
            $oldWholesale != $newWholesale;
    }
}
