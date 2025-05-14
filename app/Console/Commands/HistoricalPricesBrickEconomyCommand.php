<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

class HistoricalPricesBrickEconomyCommand extends Command
{
    protected $signature = 'historical:prices-brickeconomy
                        {--type=all : Type of products to scrape (all, set, minifig)}
                        {--limit=20 : Maximum number of products to process}
                        {--days=365 : How many days back to import}
                        {--concurrency=3 : Number of processes to use simultaneously}
                        {--product_id= : Specific product ID to process}';

    protected $description = 'Scrape historical price data from BrickEconomy using DOM Crawler';

    protected $client;
    protected $startTime;
    protected $totalSuccess = 0;
    protected $totalPoints = 0;
    protected $totalFailed = 0;
    protected $errorReasons = [];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    public function handle()
    {

        ini_set('memory_limit', '512M');
        DB::disableQueryLog();

        $this->startTime = now();
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');
        $productId = $this->option('product_id'); // Získání ID produktu z parametrů

        $this->info("Začínám scraping historických cen z BrickEconomy pomocí DOM Crawler");
        $this->info("Typ produktů: {$type}, Limit: {$limit}, Dny zpět: {$days}");

        if ($productId) {
            $this->info("Zpracovávám pouze produkt s ID: {$productId}");
        }

        $query = Product::query();

        // Pokud je zadáno ID produktu, použijeme pouze tento produkt
        if ($productId) {
            $query->where('id', $productId);
        } else {
            // Jinak filtrujeme podle původních kritérií
            $query->when($type !== 'all', fn($q) => $q->where('product_type', $type))
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('lego_id_mappings')
                        ->whereNotNull('brickeconomy_id')
                        ->whereRaw('products.id = lego_id_mappings.product_id');
                });
        }
        ini_set('memory_limit', '512M');
        DB::disableQueryLog();

        $this->startTime = now();
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');

        $this->info("Začínám scraping historických cen z BrickEconomy pomocí DOM Crawler");
        $this->info("Typ produktů: {$type}, Limit: {$limit}, Dny zpět: {$days}");

        // Příprava produktů ke zpracování
        $query = Product::query()
            ->when($type !== 'all', fn($q) => $q->where('product_type', $type))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('lego_id_mappings')
                    ->whereNotNull('brickeconomy_id')
                    ->whereRaw('products.id = lego_id_mappings.product_id');
            });

        $totalProducts = $query->count();
        $this->info("Nalezeno {$totalProducts} produktů ke zpracování");

        $totalProducts = $limit > 0 && $limit < $totalProducts ? $limit : $totalProducts;

        // Progress bar
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        $products = $query->take($totalProducts)->get();

        foreach ($products as $product) {
            try {
                $brickEconomyId = $this->getBrickEconomyId($product);

                if (!$brickEconomyId) {
                    $this->logError('No BrickEconomy ID');
                    $bar->advance();
                    continue;
                }

                $historicalData = $this->scrapeHistoricalData($brickEconomyId, $days);

                if (empty($historicalData)) {
                    $this->logError('No historical data found');
                    $bar->advance();
                    continue;
                }

                $saved = $this->saveHistoricalPrices($product, $historicalData);

                if ($saved) {
                    $this->totalSuccess++;
                    $this->totalPoints += count($historicalData);
                } else {
                    $this->logError('Failed to save data');
                }
            } catch (Exception $e) {
                $this->logError('Exception: ' . $e->getMessage());
                Log::error("Scraping error for product {$product->id}: " . $e->getMessage());
            }

            $bar->advance();

            usleep(500000); // 0.5 sekundy
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Scraping dokončen za " . $this->startTime->diffForHumans(now()));
        $this->info("Úspěšně zpracováno produktů: {$this->totalSuccess}");
        $this->info("Celkem staženo datových bodů: {$this->totalPoints}");
        $this->info("Selhalo: {$this->totalFailed}");

        if (!empty($this->errorReasons)) {
            $this->info("Nejčastější chyby:");
            arsort($this->errorReasons);
            $i = 0;
            foreach ($this->errorReasons as $reason => $count) {
                $this->info(" - {$reason}: {$count}x");
                $i++;
                if ($i >= 5) break;
            }
        }

        return 0;
    }

    /**
     * Získá BrickEconomy ID pro produkt
     */
    protected function getBrickEconomyId(Product $product): ?string
    {
        static $mappingCache = [];

        if (isset($mappingCache[$product->id])) {
            return $mappingCache[$product->id];
        }

        $mapping = LegoIdMapping::where('product_id', $product->id)
            ->whereNotNull('brickeconomy_id')
            ->first();

        if ($mapping) {
            $mappingCache[$product->id] = $mapping->brickeconomy_id;
            return $mapping->brickeconomy_id;
        }

        if ($product->product_type === 'set') {
            $mappingCache[$product->id] = $product->product_num;
            return $product->product_num;
        }

        $mappingCache[$product->id] = null;
        return null;
    }

    /**
     * Stáhne historická data z BrickEconomy pomocí DOM Crawler
     */
    protected function scrapeHistoricalData(string $brickEconomyId, int $days): array
    {
        $url = "https://www.brickeconomy.com/set/{$brickEconomyId}/";

        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            $scripts = $crawler->filter('script')->extract(['_text']);

            $historicalData = [];

            foreach ($scripts as $script) {
                if (strpos($script, 'chartData') !== false || strpos($script, 'chart.data') !== false) {
                    preg_match_all('/data\s*:\s*(\[.*?\])/s', $script, $matches);

                    if (!empty($matches[1][0])) {
                        $dataPoints = $matches[1][0];

                        preg_match_all(
                            '/{(?:\s*x\s*:\s*(?:new Date\(["\']?([^"\')]+)["\']?\)|"([^"]+)")(?:\s*,\s*|\s*)\s*y\s*:\s*([0-9.]+))/s',
                            $dataPoints,
                            $pointMatches,
                            PREG_SET_ORDER
                        );

                        if (!empty($pointMatches)) {
                            foreach ($pointMatches as $match) {
                                // Datum může být v indexu 1 nebo 2 podle použitého formátu
                                $date = !empty($match[1]) ? $match[1] : $match[2];
                                $price = (float) $match[3];

                                try {
                                    $pointDate = Carbon::parse($date);
                                    if ($pointDate->isAfter(now()->subDays($days))) {
                                        $historicalData[] = [
                                            'date' => $pointDate->format('Y-m-d'),
                                            'price' => $price,
                                            'condition' => 'New'
                                        ];
                                    }
                                } catch (Exception $e) {
                                    Log::warning("Nepodařilo se zpracovat datum: {$date}");
                                    continue;
                                }
                            }
                        }
                    }

                    if (empty($historicalData)) {
                        preg_match_all('/data:\s*\[\s*\{\s*x:\s*"([^"]+)"\s*,\s*y:\s*(\d+(?:\.\d+)?)\s*\}/s', $script, $alternateMatches, PREG_SET_ORDER);

                        if (!empty($alternateMatches)) {
                            foreach ($alternateMatches as $match) {
                                $date = $match[1];
                                $price = (float) $match[2];

                                try {
                                    $pointDate = Carbon::parse($date);
                                    if ($pointDate->isAfter(now()->subDays($days))) {
                                        $historicalData[] = [
                                            'date' => $pointDate->format('Y-m-d'),
                                            'price' => $price,
                                            'condition' => 'New'
                                        ];
                                    }
                                } catch (Exception $e) {
                                    Log::warning("Nepodařilo se zpracovat datum: {$date}");
                                    continue;
                                }
                            }
                        }
                    }

                    // Pokud jsme našli data, již nepokračujeme
                    if (!empty($historicalData)) {
                        break;
                    }
                }
            }

            if (empty($historicalData)) {
                $crawler->filter('table.table tr')->each(function (Crawler $row) use (&$historicalData, $days) {
                    try {
                        $cells = $row->filter('td')->extract(['_text']);

                        if (count($cells) >= 2) {
                            $dateText = trim($cells[0]);
                            $priceText = trim($cells[1]);

                            $dateMatches = [];
                            if (preg_match('/(\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}|\w+ \d{1,2}, \d{4})/', $dateText, $dateMatches)) {
                                $date = Carbon::parse($dateMatches[1]);

                                $priceMatches = [];
                                if (preg_match('/\$?(\d+(?:\.\d+)?)/', $priceText, $priceMatches)) {
                                    $price = (float) $priceMatches[1];

                                    // Filtrujeme podle požadovaného rozsahu dnů
                                    if ($date->isAfter(now()->subDays($days))) {
                                        $historicalData[] = [
                                            'date' => $date->format('Y-m-d'),
                                            'price' => $price,
                                            'condition' => 'New'
                                        ];
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Ignorujeme chyby při procházení řádků
                    }
                });
            }

            // Extrahujeme aktuální cenu pro dnešní datum, pokud jsme ji ještě nemáme
            if (empty($historicalData)) {
                $valueElement = $crawler->filter('.side-box-body b, .side-box-body strong')->first();
                if ($valueElement->count() > 0) {
                    $valueText = $valueElement->text();
                    $priceMatches = [];
                    if (preg_match('/\$?(\d+(?:\.\d+)?)/', $valueText, $priceMatches)) {
                        $price = (float) $priceMatches[1];
                        $historicalData[] = [
                            'date' => now()->format('Y-m-d'),
                            'price' => $price,
                            'condition' => 'New'
                        ];
                    }
                }
            }

            // Seřadíme data podle data
            usort($historicalData, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            return $historicalData;
        } catch (GuzzleException $e) {
            Log::error("HTTP error for {$brickEconomyId}: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            Log::error("Error scraping {$brickEconomyId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Uloží historická data do databáze
     */
    protected function saveHistoricalPrices(Product $product, array $historicalData): bool
    {
        if (empty($historicalData)) {
            return false;
        }

        try {
            // Zjistíme, která data už máme v DB
            $existingDates = Price::where('product_id', $product->id)
                ->where('condition', 'New')
                ->where('type', 'aggregated') // Změněno z 'historical' na 'aggregated'
                ->whereIn('created_at', array_column($historicalData, 'date'))
                ->pluck('created_at')
                ->map(function ($date) {
                    return $date->format('Y-m-d');
                })
                ->toArray();

            // Připravíme data pro bulk insert
            $dataToInsert = [];

            foreach ($historicalData as $data) {
                if (!in_array($data['date'], $existingDates)) {
                    $dataToInsert[] = [
                        'product_id' => $product->id,
                        'value' => $data['price'],
                        'retail' => round($data['price'] * 0.8, 2),
                        'wholesale' => round($data['price'] * 0.6, 2),
                        'condition' => $data['condition'],
                        'type' => 'aggregated',
                        'created_at' => $data['date'],
                        'updated_at' => now()
                    ];
                }
            }

            if (!empty($dataToInsert)) {
                Price::insert($dataToInsert);
            }

            return true;
        } catch (Exception $e) {
            Log::error("Error saving data for product {$product->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function logError($reason)
    {
        $this->totalFailed++;

        if (!isset($this->errorReasons[$reason])) {
            $this->errorReasons[$reason] = 0;
        }

        $this->errorReasons[$reason]++;
    }
}
