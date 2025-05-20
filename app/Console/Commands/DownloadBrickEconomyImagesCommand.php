<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class DownloadBrickEconomyImagesCommand extends Command
{
    protected $signature = 'app:download-images {--force : Přepsat existující obrázky}';
    protected $description = 'Stáhne obrázky z galerie BrickEconomy';

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
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
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

        // Přeskočíme produkty, které již mají obrázky (pokud není force)
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
                // Získat ID produktu na BrickEconomy
                $brickEconomyId = $this->getBrickEconomyId($product);
                if (!$brickEconomyId) {
                    $this->logError('No BrickEconomy ID');
                    $bar->advance();
                    continue;
                }

                // Smazat existující obrázky pokud je force
                if ($force) {
                    $product->clearMediaCollection('images');
                }

                // Sestavit URL stránky produktu
                $productUrl = $this->getProductUrl($product, $brickEconomyId);

                // Získat všechny URL obrázků z galerie
                $imageUrls = $this->scrapeGalleryImages($productUrl);

                if (empty($imageUrls)) {
                    $this->logError('No images found in gallery');
                    $bar->advance();
                    continue;
                }

                // Stáhnout a uložit každý obrázek z galerie
                $downloadedCount = 0;
                foreach ($imageUrls as $index => $imageUrl) {
                    $success = $this->downloadImage($product, $imageUrl, $index);
                    if ($success) {
                        $downloadedCount++;
                    }
                }

                if ($downloadedCount > 0) {
                    $this->totalSuccess++;
                    $this->info("  Staženo {$downloadedCount} obrázků pro {$product->product_num}");
                } else {
                    $this->logError('Failed to save any images');
                }
            } catch (Exception $e) {
                $this->logError('Exception: ' . $e->getMessage());
                Log::error("Image download error for product {$product->id}: " . $e->getMessage());
            }

            $bar->advance();
            sleep(2);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Stahování dokončeno za " . $this->startTime->diffForHumans(now()));
        $this->info("Úspěšně staženo obrázků pro {$this->totalSuccess} produktů");
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

    /**
     * Zaznamenává chyby při stahování 
     */
    protected function logError(string $reason): void
    {
        $this->totalFailed++;
        $this->errorReasons[$reason] = ($this->errorReasons[$reason] ?? 0) + 1;
    }

    /**
     * Získá ID produktu používané na BrickEconomy
     */
    protected function getBrickEconomyId(Product $product): ?string
    {
        // Nejprve zkusíme přímé mapování podle product_id
        $mapping = LegoIdMapping::where('product_id', $product->id)
            ->whereNotNull('brickeconomy_id')
            ->first();

        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        // Pak zkusíme mapování podle product_num
        $mapping = LegoIdMapping::where('rebrickable_id', $product->product_num)
            ->whereNotNull('brickeconomy_id')
            ->first();

        if ($mapping && $mapping->brickeconomy_id) {
            return $mapping->brickeconomy_id;
        }

        // Pro set je product_num často stejné jako BrickEconomy ID
        return $product->product_type === 'set' ? $product->product_num : null;
    }

    /**
     * Sestaví URL stránky produktu
     */
    protected function getProductUrl(Product $product, string $brickEconomyId): string
    {
        return $product->product_type === 'minifig'
            ? "https://www.brickeconomy.com/minifig/{$brickEconomyId}/"
            : "https://www.brickeconomy.com/set/{$brickEconomyId}/";
    }

    /**
     * Scrapuje všechny URL obrázků z galerie na stránce produktu
     */
    protected function scrapeGalleryImages(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            $imageUrls = [];

            $gallery = $crawler->filter('#setmediagallery');
            if ($gallery->count() > 0) {

                $gallery->filter('li img')->each(function (Crawler $img) use (&$imageUrls) {

                    if ($img->attr('onclick')) {
                        preg_match("/\$\('#setimagesimage'\).attr\('src', '([^']+)'\)/", $img->attr('onclick'), $matches);
                        if (isset($matches[1])) {
                            $imageUrls[] = 'https://www.brickeconomy.com' . $matches[1];
                        }
                    } elseif ($img->attr('src')) {

                        $src = $img->attr('src');
                        if (strpos($src, '_thumb') !== false) {
                            $largeUrl = str_replace('_thumb', '', $src);
                            $imageUrls[] = 'https://www.brickeconomy.com' . $largeUrl;
                        } else {
                            $imageUrls[] = 'https://www.brickeconomy.com' . $src;
                        }
                    }
                });
            }

            if (empty($imageUrls)) {
                $mainImage = $crawler->filter('#setimagesimage');
                if ($mainImage->count() > 0 && $mainImage->attr('src')) {
                    $imageUrls[] = 'https://www.brickeconomy.com' . $mainImage->attr('src');
                }
            }


            if (empty($imageUrls)) {
                $crawler->filter('img')->each(function (Crawler $img) use (&$imageUrls) {
                    if ($img->attr('src') && !str_contains($img->attr('src'), 'logo') && !str_contains($img->attr('src'), 'icon')) {
                        $src = $img->attr('src');
                        if (strpos($src, 'http') === 0) {
                            $imageUrls[] = $src;
                        } else {
                            $imageUrls[] = 'https://www.brickeconomy.com' . $src;
                        }
                    }
                });
            }

            return array_unique($imageUrls);
        } catch (Exception $e) {
            Log::error("Error scraping gallery from {$url}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Stáhne obrázek a uloží ho do media kolekce produktu
     */
    protected function downloadImage(Product $product, string $imageUrl, int $index = 0): bool
    {
        try {

            $tempFile = tempnam(sys_get_temp_dir(), 'brickeconomy_img_');


            $response = $this->client->get($imageUrl, [
                'sink' => $tempFile,
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() !== 200) {
                unlink($tempFile);
                return false;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tempFile);
            finfo_close($finfo);

            if (!str_starts_with($mimeType, 'image/')) {
                unlink($tempFile);
                $this->warn("Stažený soubor není obrázek: {$mimeType}");
                return false;
            }
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            $fileName = $index === 0
                ? "{$product->product_num}.{$extension}"
                : "{$product->product_num}_{$index}.{$extension}";


            $product->addMedia($tempFile)
                ->usingName($product->product_num . ($index > 0 ? "_$index" : ""))
                ->usingFileName($fileName)
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
