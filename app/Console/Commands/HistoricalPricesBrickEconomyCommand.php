<?php

namespace App\Console\Commands;

use App\Enums\PriceType;
use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class HistoricalPricesBrickEconomyCommand extends Command
{
    protected $signature = 'historical:prices-brickeconomy {--force : Přepsat existující záznamy}';
    protected $description = 'Scrape historical price data from BrickEconomy';

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
        $this->info("Scraping historických cen z BrickEconomy");

        $force = $this->option('force');
        $lastProcessedId = 0;

        if (!$force) {
            $lastProcessedId = DB::table('prices')
                ->where('type', PriceType::SCRAPED->value)
                ->join('products', 'prices.product_id', '=', 'products.id')
                ->max('products.id') ?? 0;

            $this->info("Pokračuji od ID > {$lastProcessedId}");
        }

        $query = Product::where('id', '>', $lastProcessedId)->orderBy('id', 'asc');
        $totalProducts = $query->count();
        $this->info("Nalezeno {$totalProducts} produktů ke zpracování");

        $limit = 100;
        $bar = $this->output->createProgressBar(min($limit, $totalProducts));
        $bar->start();

        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            $this->info("Žádné nové produkty k zpracování.");
            return 0;
        }

        foreach ($products as $product) {
            try {
                $brickEconomyId = $this->getBrickEconomyId($product);

                if (!$brickEconomyId) {
                    $this->logError('No BrickEconomy ID');
                    $this->saveEmptyRecord($product);
                    $bar->advance();
                    continue;
                }

                $url = $this->getProductUrl($product, $brickEconomyId);
                $historicalData = $this->scrapeHistoricalData($url);

                if (empty($historicalData)) {
                    $this->logError('No historical data found');
                    $this->saveEmptyRecord($product);
                    $bar->advance();
                    continue;
                }

                $saved = $this->saveHistoricalPrices($product, $historicalData);

                if ($saved) {
                    $this->totalSuccess++;
                    $this->totalPoints += count($historicalData);
                } else {
                    $this->logError('Failed to save data');
                    $this->saveEmptyRecord($product);
                }
            } catch (Exception $e) {
                $this->logError('Exception: ' . $e->getMessage());
                Log::error("Scraping error for product {$product->id}: " . $e->getMessage());
                $this->saveEmptyRecord($product);
            }

            $bar->advance();
            sleep(3);
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
            foreach ($this->errorReasons as $reason => $count) {
                $this->info(" - {$reason}: {$count}x");
            }
        }

        if ($products->count() > 0) {
            $lastId = $products->last()->id;
            $this->info("Poslední zpracovaný produkt ID: {$lastId}");
        }

        return 0;
    }

    protected function logError(string $reason): void
    {
        $this->totalFailed++;
        $this->errorReasons[$reason] = ($this->errorReasons[$reason] ?? 0) + 1;
    }

    protected function saveEmptyRecord(Product $product): void
    {
        try {
            Price::create([
                'product_id' => $product->id,
                'value' => 0,
                'type' => PriceType::SCRAPED->value,
                'date' => now()->format('Y-m-d'),
                'currency' => 'EUR',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error("Chyba při vytváření prázdného záznamu pro produkt {$product->id}: " . $e->getMessage());
        }
    }

    protected function getProductUrl(Product $product, string $brickEconomyId): string
    {
        return $product->product_type === 'minifig'
            ? "https://www.brickeconomy.com/minifig/{$brickEconomyId}/"
            : "https://www.brickeconomy.com/set/{$brickEconomyId}/";
    }

    protected function getBrickEconomyId(Product $product): ?string
    {
        $mapping = LegoIdMapping::where('product_id', $product->id)
            ->whereNotNull('brickeconomy_id')
            ->first();

        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)
            ->whereNotNull('brickeconomy_id')
            ->first();

        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        return $product->product_type === 'set' ? $product->product_num : null;
    }

    protected function scrapeHistoricalData(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $historicalData = $this->extractDataFromJavaScript($crawler, $html);

            if (empty($historicalData)) {
                $historicalData = $this->extractDataFromTable($crawler);
            }

            if (empty($historicalData)) {
                $currentPrice = $this->extractCurrentPrice($crawler);
                if ($currentPrice > 0) {
                    $historicalData[] = [
                        'date' => now()->format('Y-m-d'),
                        'price' => $currentPrice,
                    ];
                }
            }

            return $historicalData;
        } catch (Exception $e) {
            Log::error("Error scraping URL {$url}: " . $e->getMessage());
            return [];
        }
    }

    protected function extractDataFromJavaScript(Crawler $crawler, string $html): array
    {
        $historicalData = [];

        preg_match_all('/data\.addRows\(\[\s*(.*?)\s*\]\);/s', $html, $rowsMatches, PREG_SET_ORDER);
        $dataRows = '';
        foreach ($rowsMatches as $match) {
            $rowsContent = $match[1];
            if (strpos($rowsContent, "'Released'") !== false) {
                $dataRows = $rowsContent;
                break;
            }
        }

        if (!$dataRows && !empty($rowsMatches)) {
            $dataRows = $rowsMatches[0][1] ?? '';
        }

        if ($dataRows && preg_match_all('/\[new Date\((\d+),\s*(\d+),\s*(\d+)\),\s*([\d.]+)/s', $dataRows, $matches, PREG_SET_ORDER)) {
            $minYear = 1970;
            $maxYear = now()->year;

            foreach ($matches as $match) {
                $year = (int)$match[1];
                $month = (int)$match[2];
                $day = (int)$match[3];
                $price = (float)$match[4];

                if ($year >= $minYear && $year <= $maxYear) {
                    $date = Carbon::create($year, $month + 1, $day)->format('Y-m-d');
                    $historicalData[] = [
                        'date' => $date,
                        'price' => $price,
                    ];
                }
            }
        }

        return $historicalData;
    }

    protected function extractDataFromTable(Crawler $crawler): array
    {
        $historicalData = [];

        try {
            $crawler->filter('table.table tr')->each(function (Crawler $row) use (&$historicalData) {
                $cells = $row->filter('td')->each(fn(Crawler $cell) => trim($cell->text()));
                if (count($cells) >= 2) {
                    $dateText = $cells[0];
                    $priceText = $cells[1];

                    if (
                        preg_match('/(\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}|\w+ \d{1,2},? \d{4})/', $dateText, $dateMatches) &&
                        preg_match('/\$?(\d+(?:\.\d+)?)/', $priceText, $priceMatches)
                    ) {
                        try {
                            $date = Carbon::parse($dateMatches[1]);
                            $year = (int)$date->format('Y');
                            $minYear = 1970;
                            $maxYear = now()->year;

                            if ($year >= $minYear && $year <= $maxYear) {
                                $formattedDate = $date->format('Y-m-d');
                                $price = (float)$priceMatches[1];
                                $historicalData[] = ['date' => $formattedDate, 'price' => $price];
                            }
                        } catch (Exception $e) {
                            // Skip parsing errors
                        }
                    }
                }
            });
        } catch (Exception $e) {
            Log::error("Table parsing error: " . $e->getMessage());
        }

        return $historicalData;
    }

    protected function extractCurrentPrice(Crawler $crawler): ?float
    {
        try {
            if (preg_match('/Today\s+€([\d.]+)/', $crawler->html(), $matches)) {
                return (float)$matches[1];
            }

            $selectors = [
                '.side-box-body b',
                '.side-box-body strong',
                '.price-box .value',
                '.set-value',
                '#ContentPlaceHolder1_PanelSetPricing .side-box-body strong'
            ];

            foreach ($selectors as $selector) {
                $elements = $crawler->filter($selector);
                if ($elements->count() > 0) {
                    $valueText = $elements->first()->text();
                    if (strpos($valueText, ' - ') === false && preg_match('/€?([\d.,]+)/', $valueText, $priceMatches)) {
                        return (float)str_replace(',', '.', $priceMatches[1]);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Current price extraction error: " . $e->getMessage());
        }

        return null;
    }

    protected function saveHistoricalPrices(Product $product, array $historicalData): bool
    {
        if (empty($historicalData)) {
            return false;
        }

        try {
            $existingDates = Price::where('product_id', $product->id)
                ->where('type', PriceType::SCRAPED->value)
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $recordsToInsert = [];
            $now = now();

            foreach ($historicalData as $data) {
                $formattedDate = $data['date'];
                $price = (float)$data['price'];

                if (!in_array($formattedDate, $existingDates) && $price > 0) {
                    $recordsToInsert[] = [
                        'product_id' => $product->id,
                        'value' => $price,
                        'type' => PriceType::SCRAPED->value,
                        'date' => $formattedDate,
                        'currency' => 'EUR',
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }

            if (!empty($recordsToInsert)) {
                DB::table('prices')->insert($recordsToInsert);
            }

            return !empty($recordsToInsert) || count($existingDates) > 0;
        } catch (Exception $e) {
            Log::error("Error saving prices for product {$product->id}: " . $e->getMessage());
            return false;
        }
    }
}
