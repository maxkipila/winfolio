<?php

namespace App\Scrapers;

use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use App\Models\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BrickEconomyScraper
{
    protected string $baseUrl = 'https://www.brickeconomy.com/';
    protected string $puppeteerScript;
    protected array $httpHeaders;

    public function __construct()
    {
        $this->puppeteerScript = base_path('scripts/fetchBrickEconomy.cjs');
        $this->httpHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ];
    }

    /**
     * Získá data produktu z BrickEconomy
     */
    public function getProductDetails(string $productId): ?array
    {
        try {
            // Sestavíme URL
            $url = $this->buildProductUrl($productId);

            // Získáme HTML pomocí Puppeteeru
            $data = $this->fetchDataWithPuppeteer($url);

            if (!$data) {
                Log::error("Nepodařilo se získat data z Puppeteeru pro produkt $productId");
                return null;
            }

            // Fallback: if Puppeteer didn't find retail or value, parse HTML with DomCrawler
            if ((empty($data['retail']) || empty($data['value']))) {
                try {
                    $response = Http::withHeaders($this->httpHeaders)->get($url);
                    $crawler = new Crawler($response->body());
                    $panel = $crawler->filter('#ContentPlaceHolder1_PanelSetPricing .side-box-body');
                    // Retail price
                    if ($panel->filter('h4')->count()) {
                        $panel->filter('h4')->each(function (Crawler $h4) use (&$data) {
                            $text = trim($h4->text());
                            if ($text === 'Retail price') {
                                $next = $h4->nextAll()->filter('p')->first();
                                $data['retail'] = isset($next) ? floatval(str_replace(['$', ','], '', $next->text())) : $data['retail'];
                            }
                        });
                    }
                    // New/Sealed Value
                    if ($panel->filter('dt')->count()) {
                        $panel->filter('dt')->each(function (Crawler $dt) use (&$data) {
                            $key = trim($dt->text());
                            if ($key === 'Value') {
                                $dd = $dt->nextAll()->filter('dd')->first();
                                $data['value'] = isset($dd) ? floatval(str_replace(['$', ','], '', $dd->text())) : $data['value'];
                            }
                        });
                    }
                } catch (\Exception $e) {
                    Log::warning("Fallback HTML parse failed: " . $e->getMessage());
                }
            }

            // Vezmeme informace o produktu a cenách
            $productType = $this->determineProductType($productId);

            // Doplníme dodatečné informace
            $result = [
                'product_num' => $productId,
                'brickeconomy_id' => $productId,
                'product_type' => $productType,
                'name' => $data['name'] ?? null,
                'img_url' => $data['img_url'] ?? null,
                'availability' => $data['availability'] ?? null,
                'price_data' => [
                    'value' => $data['value'] ?? null,
                    'retail' => $data['retail'] ?? null,
                    'wholesale' => isset($data['retail']) ? round($data['retail'] * 0.7, 2) : null,
                    'condition' => $data['condition'] ?? 'New',
                    'type' => 'market',
                    'currency' => $data['currency'] ?? 'USD',
                ],
            ];

            return $result;
        } catch (\Exception $e) {
            Log::error("Chyba při získávání dat produktu $productId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Získá data pomocí Puppeteeru
     */
    protected function fetchDataWithPuppeteer(string $url): ?array
    {
        try {
            // Kontrola existence Puppeteer skriptu
            if (!file_exists($this->puppeteerScript)) {
                throw new \Exception("Puppeteer skript neexistuje na cestě {$this->puppeteerScript}");
            }

            // Spuštění Puppeteer skriptu
            $process = new Process(['node', $this->puppeteerScript, $url]);
            $process->setTimeout(60); // Zvýšený timeout
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error("Chyba Puppeteeru: " . $process->getErrorOutput());
                throw new ProcessFailedException($process);
            }

            // Parsování JSON výstupu
            $output = $process->getOutput();
            $data = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Nepodařilo se parsovat JSON data: " . json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Chyba při spouštění Puppeteeru: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Určí typ produktu podle ID a/nebo obsahu stránky
     */
    protected function determineProductType(string $productId): string
    {
        // Kontrola podle prefixu ID
        if (
            preg_match('/^(sw|sh|col|hp|bat|njo|lor|cty|poc)/i', $productId) ||
            preg_match('/^fig-/', $productId)
        ) {
            return 'minifig';
        }

        // Mapování podle ID
        $mapping = LegoIdMapping::where('rebrickable_id', $productId)
            ->orWhere('brickeconomy_id', $productId)
            ->first();

        if ($mapping) {
            // Zkusíme najít produkt v DB a zjistit jeho typ
            $product = Product::where('product_num', $mapping->rebrickable_id)->first();
            if ($product && $product->product_type) {
                return $product->product_type;
            }
        }

        // Výchozí hodnota
        return 'set';
    }

    /**
     * Sestaví URL produktu na BrickEconomy
     */
    protected function buildProductUrl(string $brickEconomyId): string
    {
        $type = $this->determineProductType($brickEconomyId);

        if ($type === 'minifig') {
            return "https://www.brickeconomy.com/minifig/{$brickEconomyId}/";
        }

        return "https://www.brickeconomy.com/set/{$brickEconomyId}/";
    }

    /**
     * Uloží data produktu do databáze
     */
    /**
     * Uloží data produktu do databáze
     */
    public function saveProductToDatabase(array $productData): ?Product
    {
        try {
            DB::beginTransaction();

            // Získání produktového čísla a ID
            $productNum = $productData['product_num'];

            // Zpracování tématu
            $themeId = null;
            if (!empty($productData['theme_name'])) {
                $theme = Theme::firstOrCreate(['name' => $productData['theme_name']]);
                $themeId = $theme->id;
            }

            // Vytvoření nebo aktualizace produktu
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

            // Zpracování mapování
            if (isset($productData['brickeconomy_id']) && $productData['brickeconomy_id'] !== $productNum) {
                LegoIdMapping::updateOrCreate(
                    ['rebrickable_id' => $productNum],
                    [
                        'brickeconomy_id' => $productData['brickeconomy_id'],
                        'name' => $productData['name'] ?? null,
                    ]
                );
            }

            // Vytvoříme pole pro ceny
            $priceData = [
                'product_id' => $product->id,
                'condition' => $productData['price_data']['condition'] ?? 'New',
                'type' => $productData['price_data']['type'] ?? 'market',
                'currency' => $productData['price_data']['currency'] ?? 'EUR',
            ];

            // Explicitně přidáme každou cenovou položku jen pokud existuje a je nenulová
            if (isset($productData['price_data']['value']) && $productData['price_data']['value'] > 0) {
                $priceData['value'] = $productData['price_data']['value'];
            }

            if (isset($productData['price_data']['retail']) && $productData['price_data']['retail'] > 0) {
                $priceData['retail'] = $productData['price_data']['retail'];
            }

            if (isset($productData['price_data']['wholesale']) && $productData['price_data']['wholesale'] > 0) {
                $priceData['wholesale'] = $productData['price_data']['wholesale'];
            } elseif (isset($priceData['retail'])) {
                $priceData['wholesale'] = round($priceData['retail'] * 0.7, 2);
            }

            // Uložíme cenu, pokud máme alespoň jednu položku
            if (isset($priceData['value']) || isset($priceData['retail']) || isset($priceData['wholesale'])) {
                Price::create($priceData);
                Log::debug('Uložena cena pro produkt', $priceData);
            } else {
                Log::warning('Nebyly nalezeny žádné cenové údaje k uložení');
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Chyba při ukládání produktu: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return null;
        }
    }
}
