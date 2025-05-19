<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class DownloadBrickEconomyImagesCommand extends Command
{
    protected $signature = 'app:download-images {--force : Přepsat existující obrázky}';
    protected $description = 'Stáhne obrázkyz BrickEconomy';

    protected $client;
    protected $startTime;
    protected $totalSuccess = 0;
    protected $totalFailed = 0;
    protected $errorReasons = [];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    public function handle()
    {
        ini_set('memory_limit', '512M');
        DB::disableQueryLog();

        $this->startTime = now();
        $this->info("Začínám stahování obrázků z BrickEconomy");

        $force = $this->option('force');

        $query = Product::query()->orderBy('id', 'asc');

        // Pokud není force, přeskočíme produkty, které již mají obrázky
        if (!$force) {
            $query->whereDoesntHave('media', function ($q) {
                $q->where('collection_name', 'images');
            });
        }

        $totalProducts = $query->count();
        $this->info("Nalezeno {$totalProducts} produktů ke zpracování");

        $limit = 100;
        $bar = $this->output->createProgressBar(min($limit, $totalProducts));
        $bar->start();

        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            $this->info("Žádné produkty k zpracování.");
            return 0;
        }

        foreach ($products as $product) {
            try {
                $brickEconomyId = $this->getBrickEconomyId($product);

                if (!$brickEconomyId) {
                    $this->logError('No BrickEconomy ID');
                    $bar->advance();
                    continue;
                }

                $url = $this->getProductUrl($product, $brickEconomyId);
                $imageUrl = $this->scrapeImageUrl($url);

                if (empty($imageUrl)) {
                    $this->logError('No image URL found');
                    $bar->advance();
                    continue;
                }

                if ($force) {
                    $product->clearMediaCollection('images');
                }

                // Stáhneme a uložíme obrázek
                $success = $this->downloadAndSaveImage($product, $imageUrl);

                if ($success) {
                    $this->totalSuccess++;
                } else {
                    $this->logError('Failed to save image');
                }
            } catch (Exception $e) {
                $this->logError('Exception: ' . $e->getMessage());
                Log::error("Image download error for product {$product->id}: " . $e->getMessage());
            }

            $bar->advance();
            sleep(3);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Stahování dokončeno za " . $this->startTime->diffForHumans(now()));
        $this->info("Úspěšně staženo obrázků: {$this->totalSuccess}");
        $this->info("Selhalo: {$this->totalFailed}");

        if (!empty($this->errorReasons)) {
            $this->info("Nejčastější chyby:");
            arsort($this->errorReasons);
            foreach ($this->errorReasons as $reason => $count) {
                $this->info(" - {$reason}: {$count}x");
            }
        }

        return 0;
    }

    protected function logError(string $reason): void
    {
        $this->totalFailed++;
        $this->errorReasons[$reason] = ($this->errorReasons[$reason] ?? 0) + 1;
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

    protected function scrapeImageUrl(string $url): ?string
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();

            // Metoda 1: Extrakce z JSON-LD dat
            if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
                $jsonData = json_decode($matches[1], true);
                if (isset($jsonData['image']) && is_array($jsonData['image']) && !empty($jsonData['image'][0])) {
                    return $jsonData['image'][0];
                }
            }

            // Metoda 2: Použití Crawler pro hledání obrázků
            $crawler = new Crawler($html);

            // Hledání v hlavním img elementu
            $imgElement = $crawler->filter('#setimagesimage')->first();
            if ($imgElement->count() > 0 && $imgElement->attr('src')) {
                return $imgElement->attr('src');
            }

            // Zkoušíme další možné selektory pro obrázky
            $imgSelectors = [
                '.setmediagallery-images img',
                '.set-image img',
                '.minifig-image img',
                '.product-image img',
                '.side-box img',
                'img.img-thumbnail'
            ];

            foreach ($imgSelectors as $selector) {
                $imgElements = $crawler->filter($selector);
                if ($imgElements->count() > 0) {
                    return $imgElements->first()->attr('src');
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error("Error scraping image URL from {$url}: " . $e->getMessage());
            return null;
        }
    }

    protected function downloadAndSaveImage(Product $product, string $imageUrl): bool
    {
        try {
            // Vytvoříme dočasný soubor 
            $tempFile = tempnam(sys_get_temp_dir(), 'brickeconomy_img_');

            // Stáhneme obrázek 
            $imageResponse = $this->client->get($imageUrl, ['sink' => $tempFile]);

            // Zkontrolujeme, že jsme dostali obrázek
            $contentType = $imageResponse->getHeaderLine('Content-Type');
            if (!str_starts_with($contentType, 'image/')) {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tempFile);
                finfo_close($finfo);

                if (!str_starts_with($mimeType, 'image/')) {
                    unlink($tempFile);
                    throw new Exception("Stažený soubor není obrázek: {$mimeType}");
                }
            }

            // Získáme příponu souboru
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$extension) {
                $extension = match ($contentType) {
                    'image/jpeg', 'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
            }

            // media library
            $media = $product->addMedia($tempFile)
                ->usingName($product->product_num)
                ->usingFileName("{$product->product_num}.{$extension}")
                ->withResponsiveImages()
                ->toMediaCollection('images');

            return true;
        } catch (Exception $e) {
            Log::error("Error downloading image for product {$product->id}: " . $e->getMessage());
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return false;
        }
    }
}
