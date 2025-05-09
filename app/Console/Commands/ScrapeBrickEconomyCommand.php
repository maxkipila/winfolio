<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Price;
use App\Models\Product;
use App\Scrapers\BrickEconomyScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ScrapeBrickEconomyCommand extends Command
{
    protected $signature = 'scrape:brickeconomy     
    {productIds?* : ID produktu nebo seznam ID (např. 078-1) nebo plná URL} 
    {--url= : Plná URL setu (např. https://www.brickeconomy.com/set/8599-1/lego-bionicle-krana-kal)} 
    {--debug : Zapne ladění} 
    {--save : Uloží data do databáze} 
    {--delay=2 : Prodleva mezi požadavky v sekundách (výchozí: 2)} 
    {--bulk : Spustí režim hromadného scrapování z databáze}
    {--limit=50 : Počet produktů ke zpracování v hromadném režimu (výchozí: 50)}
    {--offset=0 : Začátek od určitého offsetu (výchozí: 0)}
    {--type=all : Typ produktů (all, set, minifig)}
    {--only-without-price : Jen produkty bez aktuálních cen}
    {--proxy= : Použít proxy server ve formátu host:port}
    {--user-agent= : Použít vlastní User-Agent}
    {--throttle=5 : Maximální počet požadavků za minutu (ochrana IP)}
    {--download-images : Aktivuje stahování obrázků produktů}
    {--force-images : Přepíše existující obrázky}';

    protected $description = 'Scrapuje data o LEGO setech z BrickEconomy';

    private $puppeteerScript;

    public function __construct()
    {
        parent::__construct();
        $this->puppeteerScript = base_path('scripts/fetchBrickEconomy.cjs');
    }

    public function handle()
    {
        $this->createPuppeteerScript();
        $this->checkNodeDependencies();

        if ($this->option('bulk')) {
            return $this->handleBulkMode();
        }

        $urls = [];
        $productIds = $this->argument('productIds');

        if ($this->option('url')) {
            $urls[] = $this->option('url');
        } elseif (!empty($productIds)) {
            foreach ($productIds as $id) {
                $initialUrl = "https://www.brickeconomy.com/set/{$id}/";
                $result = $this->scrapeProduct($initialUrl);

                if ($result['success'] && !empty($result['data']['name'])) {
                    $name = $result['data']['name'];
                    $slug = $this->createSlug($name);
                    $finalUrl = "https://www.brickeconomy.com/set/{$id}/{$slug}";
                    $urls[] = $finalUrl;
                } else {
                    $this->warn("Nepodařilo se získat název pro ID {$id}, použije se původní URL.");
                    $urls[] = $initialUrl;
                }
            }
        } else {
            $this->error('Nebyly zadány žádné produkty ke zpracování.');
            return 1;
        }

        $this->info('Začínám scrapování ' . count($urls) . ' produktů z BrickEconomy');

        $success = 0;
        $failed = 0;
        $withPrice = 0;
        $saved = 0;

        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        foreach ($urls as $url) {
            $result = $this->scrapeProduct($url);

            if ($result['success']) {
                $success++;
                if (isset($result['data']['value']) || isset($result['data']['retail'])) {
                    $withPrice++;
                }

                if ($this->option('save') && isset($result['data'])) {
                    $productId = basename(parse_url($url, PHP_URL_PATH));
                    $savedProduct = $this->saveProductData($productId, $result['data']);
                    if ($savedProduct) {
                        $saved++;
                    }
                }
            } else {
                $failed++;
            }

            $bar->advance();

            if ($this->option('delay') > 0) {
                sleep($this->option('delay'));
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Scrapování dokončeno:");
        $this->info("- Úspěšně zpracováno: $success / " . count($urls));
        $this->info("- Produktů s cenou: $withPrice");
        $this->info("- Uloženo do DB: $saved");
        $this->info("- Selhalo: $failed");

        return 0;
    }

    protected function createSlug($name)
    {
        $name = preg_replace('/^\d+\s*/', '', $name);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        return $slug;
    }

    protected function scrapeProduct($url)
    {
        $this->info("Spouštím scrapování pro URL: {$url}");

        $this->createPuppeteerScript();

        $command = "node {$this->puppeteerScript} '{$url}'";

        exec($command . ' 2>&1', $output, $returnVar);
        $outputString = implode("\n", $output);

        $this->info("Přímý výstup: {$outputString}");

        // Filtrujeme pouze JSON část (pokud existuje)
        $jsonString = null;
        foreach ($output as $line) {
            if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                $jsonString = $line;
                break;
            }
        }

        if (!$jsonString) {
            $this->warn("Nenalezen validní JSON výstup");
            return ['success' => false];
        }

        $data = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->warn("Chyba při dekódování JSON: " . json_last_error_msg());
            return ['success' => false, 'error' => 'Neplatný JSON výstup'];
        }

        // Přidáme flag success
        $data['success'] = !isset($data['error']);

        return ['success' => $data['success'], 'data' => $data];
    }


    protected function createPuppeteerScript($url = null, $useProxy = false): void
    {
        $this->info('Vytvářím Puppeteer skript...');

        // Validace URL
        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Neplatné URL: ' . $url);
        }

        // Vytvoření adresáře pro skript
        $scriptDir = dirname($this->puppeteerScript);
        if (!is_dir($scriptDir)) {
            mkdir($scriptDir, 0755, true);
        }

        // Seznam user-agentů pro rotaci
        $userAgents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5.1 Safari/605.1.15"
        ];

        // Výběr náhodného user-agenta
        $userAgent = addslashes($this->option('user-agent') ?: $userAgents[array_rand($userAgents)]);

        // Proxy konfigurace
        $proxyConfig = '';
        if ($useProxy && $this->option('proxy')) {
            $proxy = addslashes($this->option('proxy'));
            $proxyConfig = "
            args.push('--proxy-server=$proxy');";
        }

        // Escapování URL pro bezpečnost
        $url = addslashes($url ?? '');

        // Sjednocený Puppeteer skript - OPTIMALIZOVANÝ BEZ DUPLICIT
        $scriptContent = <<<JS
const puppeteer = require("puppeteer-extra");
const StealthPlugin = require("puppeteer-extra-plugin-stealth");
const fs = require("fs");

puppeteer.use(StealthPlugin());

// Definice debug proměnné z prostředí
const isDebug = process.env.DEBUG === 'true';

(async () => {
    const url = process.argv[2] || '$url';
    
    if (!url) {
        console.log(JSON.stringify({ error: "URL není zadána" }));
        process.exit(1);
    }

    const args = ["--no-sandbox", "--disable-setuid-sandbox"];
    $proxyConfig

    const browser = await puppeteer.launch({
        headless: true,
        args: args,
    });

    try {
        const page = await browser.newPage();

        await page.setUserAgent("$userAgent");

        await page.setExtraHTTPHeaders({
            "Accept-Language": "en-US,en;q=0.9,cs;q=0.8",
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Encoding": "gzip, deflate, br",
            "Cache-Control": "no-cache",
            "Pragma": "no-cache"
        });

        if (isDebug) console.error("DEBUG: Navštěvuji URL:", url);
        await page.goto(url, { waitUntil: "networkidle2", timeout: 30000 });

        // Čekání na načtení stránky
        await new Promise((resolve) => setTimeout(resolve, 5000));

        // Uložení HTML pro debug
        if (isDebug) {
            const htmlContent = await page.content();
            console.error("DEBUG: HTML Content Length:", htmlContent.length);
            fs.writeFileSync('debug_page.html', htmlContent);
            console.error("DEBUG: HTML uložen do debug_page.html");
        }

        const data = await page.evaluate(() => {
            const result = {
                name: null,
                value: null,
                retail: null,
                condition: "New",
                currency: "EUR",
                availability: null,
                img_url: null,
                theme_name: null,
            };

            // Lokální debug uvnitř evaluate
            const debug = false; 

            // Jméno produktu
            const nameElement = document.querySelector("h1");
            if (nameElement) {
                result.name = nameElement.innerText.trim();
                if (debug) console.log("DEBUG: Název setu:", result.name);
            }

            // Vylepšení detekce obrázků - zkusíme několik selektorů
            const imgSelectors = [
                "img.img-thumbnail",                 // Běžný obrázek produktu
                ".product-image img",                // Alternativní selektor
                ".set-image img",                    // Pro sety
                ".minifig-image img",                // Pro minifigurky
                "#ContentPlaceHolder1_imgItem",      // Specifický ID selektor
                ".item-img img",                     // Obecný selektor obrázku položky
            ];

            // Projdeme všechny selektory a použijeme první nalezený
            for (const selector of imgSelectors) {
                const imgEl = document.querySelector(selector);
                if (imgEl && imgEl.src) {
                    // Zkontrolujeme, že URL není prázdná a není to placeholder
                    if (imgEl.src && 
                        !imgEl.src.includes('placeholder') && 
                        !imgEl.src.includes('no-image')) {
                        
                        // Pokud URL neobsahuje protokol, přidáme ho
                        result.img_url = imgEl.src.startsWith('http') 
                            ? imgEl.src 
                            : 'https:' + imgEl.src;
                        
                        if (debug) console.log("DEBUG: Obrázek nalezen:", result.img_url);
                        break;
                    }
                }
            }
            
            // Pokud stále nemáme obrázek, zkusíme najít jakýkoliv obrázek s rozumnou velikostí
            if (!result.img_url) {
                const allImages = document.querySelectorAll('img');
                
                for (const img of allImages) {
                    // Přeskočíme malé obrázky a ikony
                    if (img.width >= 100 && img.height >= 100 && img.src) {
                        result.img_url = img.src.startsWith('http') 
                            ? img.src 
                            : 'https:' + img.src;
                        if (debug) console.log("DEBUG: Alternativní obrázek nalezen:", result.img_url);
                        break;
                    }
                }
            }

            // Hledání availability - OPRAVENO
            const bodyText = document.body.innerText;
            const availabilityRegex = /Availability\\s*:?\\s*([^\\n]+)/i;
            const availabilityMatch = bodyText.match(availabilityRegex);
            if (availabilityMatch && availabilityMatch[1]) {
                result.availability = availabilityMatch[1].trim();
                if (debug) console.log("DEBUG: Availability from regex:", result.availability);
            }

            if (!result.availability) {
                document.querySelectorAll('.set-details .rowlist').forEach(row => {
                    const rowText = row.innerText;
                    if (rowText.toLowerCase().includes('availability')) {
                        const parts = rowText.split(':');
                        if (parts.length > 1) {
                            result.availability = parts[1].trim();
                            if (debug) console.log("DEBUG: Availability from rows:", result.availability);
                        }
                    }
                });
            }

            // Retail price - OPRAVENO
            const retailRegex = /Retail price\\s*:?\\s*[€\$]?(\\d+\\.?\\d*)/i;
            const retailMatch = bodyText.match(retailRegex);
            if (retailMatch && retailMatch[1]) {
                result.retail = parseFloat(retailMatch[1]);
                if (debug) console.log("DEBUG: Retail price from regex:", result.retail);
            }

            // Value
            const pricingPanel = document.querySelector("#ContentPlaceHolder1_PanelSetPricing .side-box-body");
            if (pricingPanel) {
                const rows = pricingPanel.querySelectorAll(".row.rowlist");
                if (debug) console.log("DEBUG: Pricing panel rows:", rows.length);

                rows.forEach(row => {
                    const labelElement = row.querySelector(".col-xs-5") || row.querySelector(".label");
                    const valueElement = row.querySelector(".col-xs-7 b") || row.querySelector("strong");

                    if (labelElement && valueElement) {
                        const label = labelElement.innerText.trim().toLowerCase();
                        const valueText = valueElement.innerText.trim();
                        if (debug) console.log("DEBUG: Price label:", label, "Value:", valueText);

                        const numericValue = parseFloat(valueText.replace(/[^0-9.]/g, ""));
                        if (!isNaN(numericValue)) {
                            if (label.includes("value")) {
                                result.value = numericValue;
                                if (debug) console.log("DEBUG: Found value:", result.value);
                            } else if (label.includes("retail price") && !result.retail) {
                                result.retail = numericValue;
                                if (debug) console.log("DEBUG: Found retail price:", result.retail);
                            }
                        }
                    }
                });
            }

            // Theme
            const themeElement = Array.from(document.querySelectorAll('.col-xs-5')).find(el =>
                el.innerText.trim().toLowerCase() === 'theme'
            );
            if (themeElement) {
                const valueElement = themeElement.nextElementSibling;
                if (valueElement && valueElement.classList.contains('col-xs-7')) {
                    result.theme_name = valueElement.innerText.trim();
                    if (debug) console.log("DEBUG: Theme found:", result.theme_name);
                }
            }

            return result;
        });

        console.log(JSON.stringify(data));
    } catch (error) {
        console.log(JSON.stringify({ error: error.message }));
    } finally {
        await browser.close();
    }
})();
JS;

        // Odstranění starého skriptu
        if (file_exists($this->puppeteerScript)) {
            unlink($this->puppeteerScript);
        }

        // Zápis skriptu
        $bytesWritten = file_put_contents($this->puppeteerScript, $scriptContent, LOCK_EX);
        if ($bytesWritten === false) {
            throw new \RuntimeException('Chyba při zápisu skriptu do souboru: ' . $this->puppeteerScript);
        }

        $this->info('Puppeteer skript byl úspěšně vytvořen, zapsáno ' . $bytesWritten . ' bajtů: ' . $this->puppeteerScript);
    }



    protected function checkNodeDependencies()
    {
        $process = new Process(['node', '-v']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Node.js není nainstalován. Nainstalujte Node.js a zkuste to znovu.');
            exit(1);
        }

        $process = new Process(['npm', '-v']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('npm není nainstalován. Nainstalujte npm a zkuste to znovu.');
            exit(1);
        }
    }
    protected function selectProductsForBulkMode(): \Illuminate\Database\Eloquent\Collection
    {
        $limit = (int)$this->option('limit') ?: 50;
        $offset = (int)$this->option('offset') ?: 0;
        $type = $this->option('type') ?: 'all';
        $onlyWithoutPrice = $this->option('only-without-price') ?: false;

        // If no explicit offset given, continue from last scraped product
        if ($offset === 0) {
            $lastProductId = \App\Models\Price::orderByDesc('id')->value('product_id');
            if ($lastProductId) {
                $offset = $lastProductId;
                $this->info("Automatically continuing from last scraped product ID: {$offset}");
            }
        }

        $lastProcessedId = max(0, $offset);
        if ($offset > 0) {
            $this->info("Skipping first {$offset} products...");
        }

        // Připrav query pro výběr produktů
        $query = Product::query();

        // Filtrování podle ID - začínáme VĚTŠÍ než poslední zpracované ID
        if ($lastProcessedId > 0) {
            $query->where('id', '>', $lastProcessedId);
        }

        // Aplikuj filtry
        if ($type !== 'all') {
            $query->where('product_type', $type);
        }

        if ($onlyWithoutPrice) {
            $query->whereDoesntHave('prices', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            });
        }

        // Seřazení podle ID a limit
        $query->orderBy('id', 'asc')
            ->take($limit);

        $products = $query->get();

        if ($products->count() > 0) {
            $firstId = $products->first()->id;
            $lastId = $products->last()->id;
            $this->info("Vybrány produkty s ID od {$firstId} do {$lastId} (celkem: {$products->count()})");
        } else {
            $this->info("Nenalezeny žádné produkty ke zpracování");
        }

        return $products;
    }

    // V ScrapeBrickEconomyCommand.php - metoda handleBulkMode()
    protected function handleBulkMode()
    {
        $this->info('Spouštím hromadný režim scrapování...');

        // Získání konfigurací
        $limit = (int)$this->option('limit') ?: 50;
        $offset = (int)$this->option('offset') ?: 0;
        $downloadImages = $this->option('download-images') ?: false;
        $forceImages = $this->option('force-images') ?: false;

        // Zobrazit aktuální stav DB před začátkem
        $this->showDbStats();

        // Použijeme novou metodu pro výběr produktů
        $products = $this->selectProductsForBulkMode();

        $this->info("Nalezeno {$products->count()} produktů ke zpracování");

        // Statistiky
        $success = 0;
        $failed = 0;
        $savedPrices = 0;
        $updatedAvailability = 0;
        $downloadedImages = 0;
        $failedImageDownloads = 0;

        // Progress bar
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $index => $product) {
            $this->info("Zpracovávám produkt #{($offset + $index + 1)}: {$product->product_num} - {$product->name}");

            // Scrapování
            $url = "https://www.brickeconomy.com/set/{$product->product_num}/";
            $this->info("Spouštím scrapování pro URL: {$url}");

            $result = $this->scrapeProduct($url);

            if (isset($result['success']) && $result['success'] && isset($result['data'])) {
                $success++;
                $data = $result['data'];

                $this->info("== ZÍSKANÁ DATA ==");
                $this->info("Název: " . ($data['name'] ?? 'Nenalezeno'));
                $this->info("Hodnota: " . ($data['value'] ?? 'Nenalezeno'));
                $this->info("Retail: " . ($data['retail'] ?? 'Nenalezeno'));
                $this->info("Dostupnost: " . ($data['availability'] ?? 'Nenalezeno'));

                if (isset($data['img_url'])) {
                    $this->info("Obrázek: {$data['img_url']}");
                } else {
                    $this->info("Obrázek: Nenalezen");
                }

                $this->info("Téma: " . ($data['theme_name'] ?? 'Nenalezeno'));

                // 1. Aktualizace availability
                if (!empty($data['availability'])) {
                    $this->info("Aktualizuji availability na: {$data['availability']}");
                    $product->availability = $data['availability'];
                    $product->save();
                    $updatedAvailability++;
                }

                // 2. Přidání ceny do databáze - DŮLEŽITÉ!
                if (
                    (isset($data['value']) && is_numeric($data['value']) && $data['value'] > 0) ||
                    (isset($data['retail']) && is_numeric($data['retail']) && $data['retail'] > 0)
                ) {
                    $savedPrice = $this->savePriceToDatabase($product, $data);
                    if ($savedPrice) {
                        $savedPrices++;
                        $this->info("Cena byla úspěšně uložena!");
                    }
                } else {
                    $this->warn("Pro produkt {$product->product_num} nebyly nalezeny platné cenové údaje.");
                }

                // 3. Stahování a ukládání obrázků, pokud je aktivní
                if ($downloadImages && isset($data['img_url']) && !empty($data['img_url'])) {
                    $hasExistingImages = $product->getMedia('images')->count() > 0;

                    // Kontrola, zda již produkt má obrázky a zda je force režim
                    if (!$hasExistingImages || $forceImages) {
                        try {
                            $success = $this->downloadAndSaveImage($product, $data['img_url']);

                            if ($success) {
                                $this->info("Obrázek úspěšně stažen a uložen");
                                $downloadedImages++;
                            } else {
                                $this->warn("Nepodařilo se stáhnout obrázek");
                                $failedImageDownloads++;
                            }
                        } catch (\Exception $e) {
                            $this->error("Chyba při stahování obrázku: " . $e->getMessage());
                            $failedImageDownloads++;
                        }
                    } else {
                        $this->info("Produkt již má obrázky, přeskakuji (použijte --force-images pro přepsání)");
                    }

                    // Aktualizace URL obrázku v databázi
                    if (!$product->img_url && isset($data['img_url'])) {
                        $product->img_url = $data['img_url'];
                        $product->save();
                    }
                }
            } else {
                $this->warn("Nepodařilo se získat data pro {$product->product_num}");
                if (isset($result['error'])) {
                    $this->error("Chyba: " . $result['error']);
                }
                $failed++;
            }

            $bar->advance();

            // Zpoždění mezi požadavky
            sleep($this->option('delay') ?: 2);

            // Explicitní úklid paměti
            if ($index % 10 === 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Zobrazíme poslední zpracovaný produkt jako referenci pro další běh
        if ($products->count() > 0) {
            $lastProduct = $products->last();
            $nextOffset = $offset + $products->count();
            $this->info("Poslední zpracovaný produkt: ID={$lastProduct->id}, {$lastProduct->product_num}");
            $this->info("Pro pokračování použijte: --offset={$nextOffset}");
        }

        // Zobrazit aktuální stav DB po dokončení
        $this->showDbStats();

        // Statistika
        $this->info("Hromadné scrapování dokončeno:");
        $this->info("- Zpracováno celkem: " . $products->count());
        $this->info("- Úspěšně zpracováno: {$success}");
        $this->info("- Aktualizováno availability: {$updatedAvailability}");
        $this->info("- Uloženo cenových záznamů: {$savedPrices}");
        if ($downloadImages) {
            $this->info("- Staženo obrázků: {$downloadedImages}");
            $this->info("- Neúspěšných stahování obrázků: {$failedImageDownloads}");
        }
        $this->info("- Selhalo: {$failed}");

        return 0;
    }

    /**
     * Ukládá cenové údaje do databáze
     */
    protected function savePriceToDatabase(Product $product, array $data): bool
    {
        // Připrav data pro cenu
        $priceData = [
            'product_id' => $product->id,
            'condition' => 'New',
            'type' => 'market',
            'currency' => $data['currency'] ?? 'EUR',
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Přidej pouze validní cenové údaje
        if (isset($data['value']) && is_numeric($data['value']) && $data['value'] > 0) {
            $priceData['value'] = (float)$data['value'];
        }

        if (isset($data['retail']) && is_numeric($data['retail']) && $data['retail'] > 0) {
            $priceData['retail'] = (float)$data['retail'];
            // Wholesale počítáme pouze pokud máme validní retail hodnotu
            $priceData['wholesale'] = round($priceData['retail'] * 0.7, 2);
        }

        // Pokud nemáme žádné cenové hodnoty, nemá smysl ukládat
        if (!isset($priceData['value']) && !isset($priceData['retail'])) {
            return false;
        }

        // Zobrazíme data pro debug
        $this->info("Data pro DB: " . json_encode($priceData));

        // Je důležité zkusit více metod ukládání, pokud jedna selže
        try {
            // Metoda 1: Přímé použití modelu
            $price = new \App\Models\Price();
            $price->fill($priceData);
            if ($price->save()) {
                $this->info("Cena byla úspěšně uložena pomocí Eloquent modelu s ID: {$price->id}");
                return true;
            }
        } catch (\Exception $e) {
            $this->warn("Metoda 1 selhala: " . $e->getMessage());

            try {
                // Metoda 2: Přímé vložení do DB
                $priceId = DB::table('prices')->insertGetId($priceData);
                if ($priceId) {
                    $this->info("Cena byla úspěšně uložena pomocí Query Builder s ID: {$priceId}");
                    return true;
                }
            } catch (\Exception $e2) {
                $this->warn("Metoda 2 selhala: " . $e2->getMessage());

                try {
                    // Metoda 3: Jednoduchý insert bez vracení ID
                    $inserted = DB::table('prices')->insert($priceData);
                    if ($inserted) {
                        $this->info("Cena byla úspěšně uložena pomocí prostého insert.");
                        return true;
                    }
                } catch (\Exception $e3) {
                    $this->error("Všechny metody selhaly při ukládání ceny. Poslední chyba: " . $e3->getMessage());
                    return false;
                }
            }
        }
        $this->error("Nepodařilo se uložit cenu pro produkt {$product->product_num}");
        return false;
    }



    // Pomocná metoda pro zobrazení statistik DB
    protected function showDbStats()
    {
        $this->info("==== STATISTIKY DATABÁZE ====");
        $this->info("Počet produktů: " . DB::table('products')->count());
        $this->info("Počet cen: " . DB::table('prices')->count());
        $this->info("Poslední cena: " . DB::table('prices')->orderBy('id', 'desc')->value('id'));

        // Počet produktů s obrázky
        $mediaCount = DB::table('media')->where('collection_name', 'images')->count();
        $productsWithImages = DB::table('media')
            ->where('collection_name', 'images')
            ->distinct('model_id')
            ->count('model_id');

        $this->info("Počet médií: " . $mediaCount);
        $this->info("Počet produktů s obrázky: " . $productsWithImages);
        $this->info("============================");
    }

    protected function saveProductData($productId, $data)
    {
        try {
            // Získáme scrapovaná data
            $scrapedData = [
                'product_num' => $productId,
                'name' => $data['name'] ?? null,
                'product_type' => $this->determineProductType($productId),
                'availability' => $data['availability'] ?? null,
                'img_url' => $data['img_url'] ?? null,
                'theme_name' => $data['theme_name'] ?? null,
                'price_data' => [
                    'value' => $data['value'] ?? null,
                    'retail' => $data['retail'] ?? null,
                    'wholesale' => isset($data['retail']) ? round($data['retail'] * 0.7, 2) : null,
                    'condition' => 'New',
                    'type' => 'market',
                    'currency' => $data['currency'] ?? 'EUR',
                ]
            ];

            // Debug výpis - zobrazí, co ukládáme
            if ($this->option('debug')) {
                $this->info('Ukládám data:');
                $this->info('Product #: ' . $scrapedData['product_num']);
                $this->info('Name: ' . $scrapedData['name']);
                $this->info('Availability: ' . ($scrapedData['availability'] ?: 'Nezjištěno'));
                $this->info('Theme: ' . ($scrapedData['theme_name'] ?: 'Nezjištěno'));
                $this->info('Value: ' . ($scrapedData['price_data']['value'] ?? 'N/A'));
                $this->info('Retail: ' . ($scrapedData['price_data']['retail'] ?? 'N/A'));
                $this->info('Wholesale: ' . ($scrapedData['price_data']['wholesale'] ?? 'N/A'));
            }

            // Použijeme instanci scraperu pro uložení dat
            $scraper = app(\App\Scrapers\BrickEconomyScraper::class);
            $product = $scraper->saveProductToDatabase($scrapedData);

            if ($product) {
                $this->info("Produkt '{$product->name}' byl úspěšně uložen s ID {$product->id}");

                // Ověříme, zda byly ceny správně uloženy
                $price = \App\Models\Price::where('product_id', $product->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($price) {
                    $this->info("Uložená cena: value={$price->value}, retail={$price->retail}, wholesale={$price->wholesale}");
                } else {
                    $this->warn("Produkt byl uložen, ale nebyla uložena cena!");
                }

                return true;
            } else {
                $this->error("Nepodařilo se uložit produkt {$productId}");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("Chyba při ukládání produktu {$productId}: " . $e->getMessage());
            return false;
        }
    }

    protected function getProductIds(): array
    {
        $ids = [];

        if ($arguments = $this->argument('productIds')) {
            $ids = array_merge($ids, $arguments);
        }

        if ($filePath = $this->option('file')) {
            if (file_exists($filePath)) {
                $fileIds = array_filter(explode(PHP_EOL, file_get_contents($filePath)));
                $ids = array_merge($ids, $fileIds);
            } else {
                $this->warn("Soubor $filePath neexistuje");
            }
        }

        if (empty($ids) && $this->option('bulk')) {
            $ids = $this->getProductIdsFromDatabase();
        }

        return array_unique(array_filter(array_map('trim', $ids)));
    }

    protected function getProductIdsFromDatabase(): array
    {
        $limit = (int)$this->option('limit');
        $offset = (int)$this->option('offset');
        $type = $this->option('type');
        $onlyWithoutPrice = $this->option('only-without-price');

        $query = Product::query();

        if ($type !== 'all') {
            $query->where('product_type', $type);
        }

        if ($onlyWithoutPrice) {
            $query->whereDoesntHave('prices', function ($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            });
        }

        if ($offset > 0) {
            $query->skip($offset);
        }
        $query->take($limit);

        $products = $query->get();
        $ids = [];

        foreach ($products as $product) {
            $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)
                ->whereNotNull('brickeconomy_id')
                ->first();

            if ($mapping && $mapping->brickeconomy_id) {
                $ids[] = $mapping->brickeconomy_id;
                if ($this->option('debug')) {
                    $this->info("Produkt {$product->product_num} mapován na {$mapping->brickeconomy_id}");
                }
            } else {
                $ids[] = $product->product_num;
            }
        }

        return $ids;
    }

    protected function determineProductType(string $productId): string
    {
        if (
            preg_match('/^(sw|sh|col|hp|bat|njo|lor|cty|poc)/i', $productId) ||
            preg_match('/^fig-/', $productId)
        ) {
            return 'minifig';
        }

        return 'set';
    }

    protected function buildProductUrl(string $brickEconomyId, string $type): string
    {
        if ($type === 'minifig') {
            return "https://www.brickeconomy.com/minifig/{$brickEconomyId}/";
        }

        return "https://www.brickeconomy.com/set/{$brickEconomyId}/";
    }


    protected function downloadAndSaveImage(Product $product, string $imageUrl): bool
    {
        try {
            // Vytvoření temp souboru pro stažení
            $tempFile = tempnam(sys_get_temp_dir(), 'lego_img_');

            // Stažení obrázku
            $client = new \GuzzleHttp\Client();
            $response = $client->get($imageUrl, [
                'sink' => $tempFile,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => 'https://www.brickeconomy.com/'
                ],
                'connect_timeout' => 10,
                'timeout' => 30,
            ]);

            // Kontrola odpovědi
            if ($response->getStatusCode() !== 200) {
                unlink($tempFile);
                return false;
            }

            // Ověření, že soubor je obrázek
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tempFile);
            if (!str_starts_with($mime, 'image/')) {
                $this->warn("Stažený soubor není obrázek: $mime");
                unlink($tempFile);
                return false;
            }

            // Určení přípony souboru podle MIME typu
            $extension = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            // Vytvoření finálního názvu souboru
            $filename = $product->product_num . '.' . $extension;

            // Přidání do media library
            $media = $product->addMedia($tempFile)
                ->usingName($product->product_num)
                ->usingFileName($filename)
                ->toMediaCollection('images');

            return true;
        } catch (\Exception $e) {
            $this->error("Chyba při stahování obrázku: " . $e->getMessage());
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return false;
        }
    }
}
