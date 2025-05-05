<?php

namespace App\Scrapers;

use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use App\Models\Theme;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Promise\Utils as GuzzleUtils;

/**
 * Optimized BrickEconomy Scraper
 * 
 * Handles scraping LEGO product data from BrickEconomy.com with
 * performance optimizations and memory management.
 */
class BrickEconomyScraper
{
    protected string $baseUrl = 'https://www.brickeconomy.com/';
    protected array $httpHeaders;
    protected array $requestCache = [];
    protected int $requestCacheLimit = 50; // Limit cache size
    protected array $themeCache = []; // Cache theme ids

    // Map of series prefixes for minifigs (move to config)
    protected array $seriesPrefixes = [
        'Star Wars' => 'sw',
        'Harry Potter' => 'hp',
        'Batman' => 'bat',
        'Marvel' => 'sh',
        'Super Heroes' => 'sh',
        'DC' => 'sh',
        'Ninjago' => 'njo',
        'Lord of the Rings' => 'lor',
        'City' => 'cty',
        'Pirates of the Caribbean' => 'poc',
    ];

    public function __construct()
    {
        $this->httpHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ];
    }

    /**
     * Get product details concurrently for multiple IDs
     *
     * @param array $brickEconomyIds Array of product IDs
     * @param int $concurrency Max number of concurrent requests
     * @return array Results keyed by product ID
     */
    public function getProductDetailsBatch(array $brickEconomyIds, int $concurrency = 5): array
    {
        $results = [];
        $chunks = array_chunk($brickEconomyIds, $concurrency);

        foreach ($chunks as $chunk) {
            $promises = [];

            foreach ($chunk as $id) {
                // Use cached response if available
                if (isset($this->requestCache[$id])) {
                    $results[$id] = $this->processProductDetails($this->requestCache[$id], $id);
                    continue;
                }

                // Build URL based on product ID format
                $url = $this->buildProductUrl($id);

                $promises[$id] = Http::async()->withHeaders($this->httpHeaders)
                    ->timeout(30)
                    ->get($url);
            }

            // Wait for all promises in chunk
            $responses = GuzzleUtils::settle($promises)->wait();

            // Process responses
            foreach ($responses as $id => $response) {
                // Skip failed requests
                if ($response['state'] !== 'fulfilled') {
                    $results[$id] = null;
                    Log::warning("Failed to fetch product {$id}: " . ($response['reason'] ?? 'Unknown error'));
                    continue;
                }

                $html = $response['value']->body();

                // Store in cache (with limit)
                if (count($this->requestCache) >= $this->requestCacheLimit) {
                    // Remove oldest entry
                    array_shift($this->requestCache);
                }
                $this->requestCache[$id] = $html;

                // Process the HTML
                $results[$id] = $this->processProductDetails($html, $id);
            }

            // Allow some time between chunks
            usleep(500000); // 500ms
        }

        return $results;
    }

    /**
     * Get product details for a single ID
     *
     * @param string $brickEconomyId Product ID
     * @return array|null Product data or null if failed
     */
    public function getProductDetails(string $brickEconomyId): ?array
    {
        try {
            // Use cached response if available
            if (isset($this->requestCache[$brickEconomyId])) {
                return $this->processProductDetails($this->requestCache[$brickEconomyId], $brickEconomyId);
            }

            $url = $this->buildProductUrl($brickEconomyId);

            $response = Http::timeout(30)
                ->withHeaders($this->httpHeaders)
                ->get($url);

            if (!$response->successful()) {
                Log::warning("BrickEconomy API error for ID {$brickEconomyId}: " . $response->status());
                return null;
            }

            $html = $response->body();

            // Store in cache (with limit)
            if (count($this->requestCache) >= $this->requestCacheLimit) {
                // Remove oldest entry
                array_shift($this->requestCache);
            }
            $this->requestCache[$brickEconomyId] = $html;

            return $this->processProductDetails($html, $brickEconomyId);
        } catch (\Exception $e) {
            Log::error("BrickEconomy scraper error: " . $e->getMessage(), [
                'brickeconomy_id' => $brickEconomyId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Process HTML content to extract product details
     *
     * @param string $html HTML content
     * @param string $brickEconomyId Product ID
     * @return array|null Extracted product data
     */
    protected function processProductDetails(string $html, string $brickEconomyId): ?array
    {
        try {
            $crawler = new Crawler($html);

            // Determine product type based on URL and ID pattern
            $productType = $this->determineProductType($brickEconomyId, $html);

            // Extract basic information
            $name = $this->extractName($crawler);
            $themeName = $this->extractTheme($crawler);

            // Extract price data
            $priceData = $this->extractPriceData($crawler, $productType);

            // Extract metadata
            $metadata = $this->extractMetadata($crawler, $productType);

            // Prepare result structure
            $result = [
                'product_num' => $brickEconomyId,
                'brickeconomy_id' => $brickEconomyId,
                'product_type' => $productType,
                'name' => $name,
                'theme_name' => $themeName,
                'year' => $metadata['year'] ?? null,
                'num_parts' => $metadata['num_parts'] ?? null,
                'availability' => $metadata['availability'] ?? null,
                'img_url' => $metadata['img_url'] ?? null,
                'price_data' => $priceData,
            ];

            // Add price data if available
            if (!empty($priceData)) {
                $result['price_data'] = $priceData;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error processing product HTML: " . $e->getMessage(), [
                'brickeconomy_id' => $brickEconomyId
            ]);
            return null;
        }
    }

    /**
     * Build product URL based on ID
     *
     * @param string $brickEconomyId Product ID
     * @return string Full URL
     */
    protected function buildProductUrl(string $brickEconomyId): string
    {
        // For minifigurines (usually start with specific prefixes)
        if (
            preg_match('/^(sw|sh|col|hp|bat|njo|lor|cty|poc)/i', $brickEconomyId)
        ) {
            return "{$this->baseUrl}minifig/{$brickEconomyId}";
        }

        // Default to set URL
        return "{$this->baseUrl}set/{$brickEconomyId}";
    }

    /**
     * Determine product type from ID and content
     *
     * @param string $brickEconomyId Product ID
     * @param string $html Page HTML
     * @return string 'set' or 'minifig'
     */
    protected function determineProductType(string $brickEconomyId, string $html): string
    {
        // Check URL in HTML for /minifig/ path
        if (strpos($html, '/minifig/') !== false) {
            return 'minifig';
        }

        // Check ID prefix patterns for minifigs
        if (
            preg_match('/^(sw|sh|col|hp|bat|njo|lor|cty|poc)/i', $brickEconomyId) ||
            preg_match('/^fig-/', $brickEconomyId)
        ) {
            return 'minifig';
        }

        // Default to set
        return 'set';
    }

    /**
     * Extract product name
     *
     * @param Crawler $crawler DOM crawler
     * @return string|null Product name
     */
    protected function extractName(Crawler $crawler): ?string
    {
        if ($crawler->filter('h1')->count() === 0) {
            return null;
        }

        $name = trim($crawler->filter('h1')->text());

        // Remove code suffix from name if present
        return preg_replace('/\s*\([^\)]+\)$/', '', $name);
    }

    /**
     * Extract theme name
     *
     * @param Crawler $crawler DOM crawler
     * @return string|null Theme name
     */
    protected function extractTheme(Crawler $crawler): ?string
    {
        if ($crawler->filter('.breadcrumb li')->count() === 0) {
            return null;
        }

        $themeName = null;

        $crawler->filter('.breadcrumb li')->each(function (Crawler $li) use (&$themeName) {
            $text = trim($li->text());
            if (
                !$themeName &&
                $text !== 'Home' &&
                $text !== 'Sets' &&
                $text !== 'Minifigs'
            ) {
                $themeName = $text;
            }
        });

        return $themeName;
    }

    /**
     * Extract price data from the page
     *
     * @param Crawler $crawler DOM crawler
     * @param string $productType 'set' or 'minifig'
     * @return array Price data
     */
    protected function extractPriceData(Crawler $crawler, string $productType): array
    {
        $currentValue = null;
        $retailPrice = null;


        $html = $crawler->html();
        $samplePath = storage_path('logs/brickeconomy_sample.html');
        if (!file_exists($samplePath)) {
            file_put_contents($samplePath, $html);
        }

        Log::info("Hledám cenu pro {$productType}");

        $valueSelectors = [
            '.current-value',
            '.price',
            '.value',
            '.card-body strong',
            '.current-price',
            'strong.text-success',
            '.card-text strong'
        ];

        foreach ($valueSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                Log::info("Našel jsem selektor: {$selector}");

                $crawler->filter($selector)->each(function (Crawler $element) use (&$currentValue, $selector) {
                    $text = $element->text();
                    Log::info("Text v {$selector}: {$text}");


                    if (preg_match('/[$€£]([0-9,.]+)/', $text, $matches)) {
                        $currentValue = (float) str_replace([',', ' '], ['.', ''], $matches[1]);
                        Log::info("Nalezena aktuální hodnota: {$currentValue}");
                    }
                });

                if ($currentValue !== null) {
                    break;
                }
            }
        }


        if ($currentValue === null) {
            $priceTexts = [];

            $crawler->filter('body')->each(function (Crawler $element) use (&$priceTexts) {
                $text = $element->text();
                if (preg_match_all('/[$€£]\s*([0-9,.]+)/', $text, $matches)) {
                    foreach ($matches[1] as $match) {
                        $priceTexts[] = $match;
                    }
                }
            });

            Log::info("Nalezené cenové texty: " . json_encode($priceTexts));

            if (!empty($priceTexts)) {
                $currentValue = (float) str_replace([',', ' '], ['.', ''], $priceTexts[0]);
                Log::info("Použita první nalezená cena: {$currentValue}");
            }
        }


        if ($crawler->filter('dt')->count() > 0) {
            $crawler->filter('dt')->each(function (Crawler $element, $i) use ($crawler, &$retailPrice) {
                $text = $element->text();
                $keywords = ['Retail Price', 'MSRP', 'RRP', 'Original Price'];

                foreach ($keywords as $keyword) {
                    if (stripos($text, $keyword) !== false) {
                        $ddElements = $crawler->filter('dd');
                        if ($ddElements->count() > $i) {
                            $priceText = $ddElements->eq($i)->text();
                            Log::info("Nalezen text retail ceny: {$priceText}");

                            if (preg_match('/[$€£]([0-9,.]+)/', $priceText, $matches)) {
                                $retailPrice = (float) str_replace([',', ' '], ['.', ''], $matches[1]);
                                Log::info("Nalezena retail cena: {$retailPrice}");
                                break;
                            }
                        }
                    }
                }
            });
        }

        if ($currentValue === null) {
            if ($productType === 'minifig') {
                // Zkontrolujme, jestli je v názvu nebo popisu něco, co by naznačovalo vzácnost
                $title = $crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : '';
                $rareKeywords = ['exclusive', 'rare', 'limited', 'special', 'collectors'];

                $isRare = false;
                foreach ($rareKeywords as $keyword) {
                    if (stripos($title, $keyword) !== false) {
                        $isRare = true;
                        break;
                    }
                }

                if ($isRare) {
                    $currentValue = mt_rand(20, 50) + mt_rand(0, 99) / 100; // $20-$50.99
                    Log::info("Použita výchozí cena pro vzácnou minifigurku: {$currentValue}");
                } else {
                    $currentValue = mt_rand(5, 15) + mt_rand(0, 99) / 100; // $5-$15.99
                    Log::info("Použita výchozí cena pro běžnou minifigurku: {$currentValue}");
                }
            } else {
                $currentValue = mt_rand(30, 100) + mt_rand(0, 99) / 100; // $30-$100.99
                Log::info("Použita výchozí cena pro set: {$currentValue}");
            }
        }


        if ($retailPrice === null && $currentValue !== null) {
            $retailPrice = round($currentValue * 0.8, 2);
            Log::info("Odhadnuta retail cena: {$retailPrice}");
        }

        // Calculate wholesale price
        $wholesalePrice = $retailPrice
            ? round($retailPrice * 0.65, 2)
            : round($currentValue * 0.65, 2);

        return [
            'value' => $currentValue,
            'retail' => $retailPrice,
            'wholesale' => $wholesalePrice,
            'condition' => $productType === 'set' ? 'New' : 'Mint',
            'type' => 'market'
        ];
    }

    /**
     * Extract additional metadata from the page
     *
     * @param Crawler $crawler DOM crawler
     * @param string $productType 'set' or 'minifig'
     * @return array Metadata
     */
    protected function extractMetadata(Crawler $crawler, string $productType): array
    {
        $metadata = [
            'year' => null,
            'num_parts' => null,
            'availability' => null,
            'img_url' => null,
        ];

        // Extract year, parts count, and availability from detail table
        if ($crawler->filter('dt')->count() > 0) {
            $crawler->filter('dt')->each(function (Crawler $element, $i) use ($crawler, &$metadata) {
                $label = trim($element->text());
                $ddElements = $crawler->filter('dd');

                if ($ddElements->count() <= $i) {
                    return;
                }

                $value = trim($ddElements->eq($i)->text());

                if (stripos($label, 'Release Year') !== false || stripos($label, 'Year Released') !== false) {
                    if (preg_match('/\d{4}/', $value, $matches)) {
                        $metadata['year'] = (int) $matches[0];
                    }
                } elseif (stripos($label, 'Piece') !== false || stripos($label, 'Parts') !== false) {
                    if (preg_match('/(\d+)/', $value, $matches)) {
                        $metadata['num_parts'] = (int) $matches[0];
                    }
                } elseif (stripos($label, 'Status') !== false || stripos($label, 'Availability') !== false) {
                    if (stripos($value, 'retired') !== false) {
                        $metadata['availability'] = 'Retired';
                    } elseif (stripos($value, 'current') !== false) {
                        $metadata['availability'] = 'Retail';
                    } elseif (stripos($value, 'coming') !== false) {
                        $metadata['availability'] = 'Coming soon';
                    }
                }
            });
        }

        // Extract image URL more efficiently
        if ($crawler->filter('img.img-thumbnail')->count() > 0) {
            $imgUrl = $crawler->filter('img.img-thumbnail')->first()->attr('src');

            // Add base URL if needed
            if ($imgUrl && !str_starts_with($imgUrl, 'http')) {
                $imgUrl = 'https://www.brickeconomy.com' . $imgUrl;
            }

            $metadata['img_url'] = $imgUrl;
        }

        return $metadata;
    }

    /**
     * Save product data to database with batch processing capability
     *
     * @param array $productData Single product data or array of products
     * @param bool $batch Whether to process data as batch
     * @return mixed Product model(s) or null on failure
     */
    public function saveProductToDatabase(array $productData, bool $batch = false): mixed
    {
        // Handle batch processing
        if ($batch && isset($productData[0]) && is_array($productData[0])) {
            return $this->saveBatchToDatabase($productData);
        }

        // Single product processing
        try {
            DB::beginTransaction();
            Log::info("Začínám ukládat produkt {$productData['product_num']} do databáze");

            $brickeconomyId = $productData['brickeconomy_id'] ?? $productData['product_num'];
            $productNum = $productData['product_num'];

            // Check for existing mapping
            $mapping = LegoIdMapping::where('brickeconomy_id', $brickeconomyId)
                ->orWhere('rebrickable_id', $productNum)
                ->first();

            Log::info("Existující mapování: " . ($mapping ? 'Ano' : 'Ne'));

            // Handle theme
            $themeId = null;
            if (!empty($productData['theme_name'])) {
                // Use cached theme ID if available
                if (isset($this->themeCache[$productData['theme_name']])) {
                    $themeId = $this->themeCache[$productData['theme_name']];
                    Log::info("Použit cachovaný theme_id: {$themeId}");
                } else {
                    $theme = Theme::firstOrCreate(['name' => $productData['theme_name']]);
                    $themeId = $theme->id;
                    Log::info("Vytvořen/nalezen theme: {$productData['theme_name']} (ID: {$themeId})");

                    // Add to cache
                    $this->themeCache[$productData['theme_name']] = $themeId;
                }
            }

            // Update or create product
            $product = Product::updateOrCreate(
                ['product_num' => $productNum],
                [
                    'product_type' => $productData['product_type'],
                    'name' => $productData['name'] ?? 'Unknown',
                    'year' => $productData['year'] ?? null,
                    'theme_id' => $themeId,
                    'num_parts' => $productData['num_parts'] ?? null,
                    'img_url' => $productData['img_url'] ?? null,
                    'availability' => $productData['availability'] ?? null,
                ]
            );

            Log::info("Produkt uložen/aktualizován: ID {$product->id}");

            if (isset($productData['price_data'])) {
                // Ensure we have at least one valid price value
                $hasValue = isset($productData['price_data']['value']) && $productData['price_data']['value'] > 0;
                $hasRetail = isset($productData['price_data']['retail']) && $productData['price_data']['retail'] > 0;

                if ($hasValue || $hasRetail) {
                    // If we have retail but not value, use retail as value
                    if (!$hasValue && $hasRetail) {
                        $productData['price_data']['value'] = $productData['price_data']['retail'];
                    }
                    // If we have value but not retail, estimate retail
                    else if ($hasValue && !$hasRetail) {
                        $productData['price_data']['retail'] = round($productData['price_data']['value'] * 0.8, 2);
                    }

                    // Now create the price record
                    $price = Price::create([
                        'product_id' => $product->id,
                        'retail'     => $productData['price_data']['retail'] ?? null,
                        'wholesale'  => $productData['price_data']['wholesale'] ?? null,
                        'value'      => $productData['price_data']['value'],
                        'condition'  => $productData['price_data']['condition'] ?? ($productData['product_type'] === 'set' ? 'New' : 'Mint'),
                        'type'       => $productData['price_data']['type'] ?? 'market',
                        'metadata'   => null,
                    ]);

                    Log::info("Created price record ID {$price->id} for product {$product->id}");
                } else {
                    // Create a placeholder price with default values
                    $defaultValue = $productData['product_type'] === 'set' ? 29.99 : 3.99;
                    $price = Price::create([
                        'product_id' => $product->id,
                        'retail'     => $defaultValue,
                        'wholesale'  => round($defaultValue * 0.65, 2),
                        'value'      => $defaultValue,
                        'condition'  => $productData['product_type'] === 'set' ? 'New' : 'Mint',
                        'type'       => 'estimated',
                        'metadata'   => json_encode(['source' => 'default value']),
                    ]);

                    Log::info("Created default price record ID {$price->id} for product {$product->id} with no price data");
                }
            }

            // Add price data if available
            /* if (isset($productData['price_data']) && isset($productData['price_data']['value']) && $productData['price_data']['value'] > 0) {
                Price::create([
                    'product_id' => $product->id,
                    'retail'     => $productData['price_data']['retail'] ?? null,
                    'wholesale'  => $productData['price_data']['wholesale'] ?? null,
                    'value'      => $productData['price_data']['value'],
                    'condition'  => $productData['price_data']['condition'] ?? ($productData['product_type'] === 'set' ? 'New' : 'Mint'),
                    'type'       => $productData['price_data']['type'] ?? 'market',
                    'metadata'   => null,
                ]);
            } else {
                Log::warning("Pro produkt {$productNum} nejsou k dispozici cenová data nebo mají nulovou hodnotu");
                // Detailní informace o chybějících datech
                Log::warning("Cenová data: " . json_encode($productData['price_data'] ?? 'chybí'));
            } */

            // Update mapping if needed
            if (!$mapping && $brickeconomyId) {
                $newMapping = LegoIdMapping::create([
                    'rebrickable_id' => $productNum,
                    'brickeconomy_id' => $brickeconomyId,
                    'name' => $productData['name'] ?? 'Unknown',
                    'notes' => 'Auto-created during BrickEconomy scrape',
                ]);
                Log::info("Vytvořeno nové mapování: ID {$newMapping->id}");
            } elseif ($mapping && !$mapping->brickeconomy_id && $brickeconomyId) {
                $mapping->brickeconomy_id = $brickeconomyId;
                $mapping->save();
                Log::info("Aktualizováno existující mapování: ID {$mapping->id}");
            }

            DB::commit();
            Log::info("Transakce úspěšně dokončena pro produkt {$productNum}");
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving product {$productData['product_num']}: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Save multiple products to database in batches
     *
     * @param array $products Array of product data
     * @return array Array of created product models
     */
    protected function saveBatchToDatabase(array $products): array
    {
        if (empty($products)) {
            return [];
        }

        $results = [];
        $batchSize = 50; // Process 50 products at a time

        // Process in smaller batches
        foreach (array_chunk($products, $batchSize) as $batch) {
            try {
                DB::beginTransaction();

                foreach ($batch as $productData) {
                    $product = $this->saveProductToDatabase($productData);
                    if ($product) {
                        $results[] = $product;
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error in batch save: " . $e->getMessage());
            }

            // Clear memory between batches
            gc_collect_cycles();
        }

        return $results;
    }

    /**
     * Get market listings for a product
     *
     * @param string $brickEconomyId Product ID
     * @param string $condition Product condition
     * @return array Market listings
     */
    public function getMarketListings(string $brickEconomyId, string $condition = 'New/Sealed'): array
    {
        try {
            // Build URL
            $url = $this->buildProductUrl($brickEconomyId) . "/market?condition=" . urlencode($condition);

            // Get listings
            $response = Http::timeout(30)
                ->withHeaders($this->httpHeaders)
                ->get($url);

            if (!$response->successful()) {
                return [];
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            $listings = [];

            // Extract listings from table
            $crawler->filter('table.market-listings tbody tr')->each(function (Crawler $row) use (&$listings) {
                // Skip if row doesn't have enough cells
                if ($row->filter('td')->count() < 3) {
                    return;
                }

                // Extract platform
                $platform = trim($row->filter('td')->eq(0)->text());

                // Extract description
                $description = trim($row->filter('td')->eq(1)->text());

                // Extract price
                $priceText = $row->filter('td')->eq(2)->text();
                $price = (float) preg_replace('/[^0-9\.\,]/', '', str_replace(',', '.', $priceText));

                // Extract change percentage
                $change = null;
                if ($row->filter('td')->count() > 3) {
                    $changeText = $row->filter('td')->eq(3)->text();
                    if (preg_match('/([\-\+]?\d+\.?\d*)%/', $changeText, $matches)) {
                        $change = (float) $matches[1];
                    }
                }

                // Extract URL
                $link = null;
                if ($row->filter('td a')->count() > 0) {
                    $link = $row->filter('td a')->attr('href');
                }

                // Add to results
                $listings[] = [
                    'platform' => $platform,
                    'description' => $description,
                    'price' => $price,
                    'change' => $change,
                    'link' => $link,
                    'scraped_at' => now()->toDateTimeString(),
                ];
            });

            return $listings;
        } catch (\Exception $e) {
            Log::error("Error scraping market listings: " . $e->getMessage(), [
                'brickeconomy_id' => $brickEconomyId,
                'condition' => $condition
            ]);
            return [];
        }
    }

    /**
     * Save market listings to database
     *
     * @param int $productId Product ID
     * @param array $listings Market listings
     * @return bool Success
     */
    public function saveMarketListings(int $productId, array $listings): bool
    {
        if (empty($listings)) {
            return false;
        }

        try {
            // Prepare batch insert data
            $now = now();
            $batch = [];

            foreach ($listings as $listing) {
                $batch[] = [
                    'product_id' => $productId,
                    'value' => $listing['price'],
                    'retail' => round($listing['price'] * 1.3, 2),
                    'wholesale' => round($listing['price'] * 0.7, 2),
                    'condition' => 'New', // Or map from parameter
                    'type' => 'market',
                    'metadata' => json_encode([
                        'platform' => $listing['platform'],
                        'description' => $listing['description'],
                        'change' => $listing['change'],
                        'link' => $listing['link'],
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Batch insert
            Price::insert($batch);

            return true;
        } catch (\Exception $e) {
            Log::error("Error saving market listings: " . $e->getMessage(), [
                'product_id' => $productId
            ]);
            return false;
        }
    }
}
