<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeBrickLinkIdsFromRebricklable extends Command
{
    protected $signature = 'lego:scrape-bricklink
                           {--batch=50 : Počet minifigurek zpracovávaných v jedné dávce}
                           {--delay=2 : Zpoždění mezi požadavky v sekundách}
                           {--limit=0 : Maximální počet minifigurek ke zpracování (0 = všechny)}
                           {--offset=0 : Začít od určitého offsetu}
                           {--timeout=30 : Timeout pro HTTP požadavky v sekundách}
                           {--retries=3 : Počet pokusů při selhání HTTP požadavku}';

    protected $description = 'Automaticke scrapovani id produktu z rebrickable (kde je bricklink id) na brickeconomy_id';

    protected $requestCache = [];

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $batch = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $timeout = (int) $this->option('timeout');
        $retries = (int) $this->option('retries');

        // Najde minifigurky bez BrickEconomy ID a s product_id
        $query = LegoIdMapping::whereNull('brickeconomy_id')
            ->whereNotNull('product_id')
            ->whereHas('product', function ($q) {
                $q->where('product_type', 'minifig');
            })
            ->orderBy('id');

        // Aplikace offsetu a limitu
        if ($offset > 0) {
            $query->skip($offset);
        }

        if ($limit > 0) {
            $query->take($limit);
        }

        $totalToProcess = $query->count();
        $this->info("Celkem ke zpracování: {$totalToProcess} minifigurek");

        if ($totalToProcess === 0) {
            $this->info("Nebyly nalezeny žádné minifigurky ke zpracování.");
            return 0;
        }

        // Progress bar
        $bar = $this->output->createProgressBar($totalToProcess);
        $bar->start();

        $updated = 0;
        $failed = 0;

        // Získáme všechny záznamy najednou pro malé dávky
        if ($totalToProcess <= $batch) {
            $mappings = $query->get();
            $this->processItems($mappings, $updated, $failed, $bar, $delay, $timeout, $retries);
        } else {
            // Klasický přístup pro větší počet záznamů - zpracování v dávkách
            for ($i = 0; $i < $totalToProcess; $i += $batch) {
                $mappings = $query->skip($i)->take($batch)->get();
                $this->processItems($mappings, $updated, $failed, $bar, $delay, $timeout, $retries);

                gc_collect_cycles();

                if ($delay > 0) {
                    sleep($delay);
                }
            }
        }

        $bar->finish();
        $this->newLine(2);


        // Zobrazit kolik zbývá nezpracovaných
        $remaining = LegoIdMapping::whereNull('brickeconomy_id')
            ->whereNotNull('product_id')
            ->whereHas('product', function ($q) {
                $q->where('product_type', 'minifig');
            })
            ->count();

        $this->info("Zbývá zpracovat: {$remaining} minifigurek");

        // Aktualizace product_id v existujících mapováních
        $this->info("Aktualizuji product_id v existujících mapováních...");
        $this->updateProductIds();
        $this->info("Aktualizace product_id dokončena");

        return 0;
    }

    // Metoda pro zpracování položek
    private function processItems($mappings, &$updated, &$failed, $bar, $delay, $timeout, $retries)
    {
        foreach ($mappings as $mapping) {
            // Přístup k product_num pouze přes vztah product
            $product = $mapping->product;
            if (!$product) {
                $failed++;
                $bar->advance();
                continue;
            }
            $rebrickableId = $product->product_num;

            try {
                // Sestavení URL pro scraping
                $url = "https://rebrickable.com/minifigs/{$rebrickableId}/";

                if (isset($this->requestCache[$url])) {
                    $html = $this->requestCache[$url];
                } else {
                    $attempt = 0;
                    $success = false;
                    $response = null;

                    while (!$success && $attempt < $retries) {
                        try {
                            $response = Http::timeout($timeout)
                                ->withHeaders([
                                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                                    'Accept' => 'text/html,application/xhtml+xml,application/xml',
                                    'Accept-Language' => 'en-US,en;q=0.9',
                                ])
                                ->get($url);

                            $success = $response->successful();
                        } catch (\Exception $e) {
                            $attempt++;
                            if (!$success && $attempt < $retries) {
                                sleep(1);
                            }
                        }
                    }

                    if (!$success || !$response) {
                        throw new \Exception("Vyčerpány všechny pokusy");
                    }

                    $html = $response->body();
                    $this->requestCache[$url] = $html;

                    if (count($this->requestCache) > 100) {
                        array_shift($this->requestCache);
                    }
                }

                $crawler = new Crawler($html);

                // Získání BrickLink ID
                $brickLinkId = $this->extractBrickLinkId($crawler);

                if ($brickLinkId) {
                    // prevod na malá písmena
                    $brickEconomyId = strtolower($brickLinkId);

                    $mapping->brickeconomy_id = $brickEconomyId;
                    $mapping->updated_at = now();
                    $savedResult = $mapping->save();

                    if ($savedResult) {
                        $updated++;
                    } else {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }

            $bar->advance();

            // Zpoždění mezi požadavky
            if ($delay > 0) {
                usleep($delay * 500000);
            }
        }
    }

    private function extractBrickLinkId(Crawler $crawler): ?string
    {
        //Hledání BL id v URL
        $brickLinkId = null;
        $crawler->filter('a')->each(function (Crawler $link) use (&$brickLinkId) {
            if ($brickLinkId) return;

            $href = $link->attr('href');

            // Hledáme specificky v URL části bricklink.com/v2/catalog/catalogitem.page?
            if (preg_match('/bricklink\.com\/v2\/catalog\/catalogitem\.page\?([^&]*)/', $href, $matches)) {

                parse_str($matches[1], $params);
                if (isset($params['M'])) {
                    $brickLinkId = $params['M'];
                    return;
                }
            }
            $text = trim($link->text());
            if (stripos($text, 'bricklink') !== false && preg_match('/([a-z0-9]{3,10})/i', $text, $matches)) {
                $brickLinkId = $matches[1];
            }
        });

        if ($brickLinkId) return $brickLinkId;

        $crawler->filter('h4, h3, .external-ids, .external-links')->each(function (Crawler $section) use (&$brickLinkId) {
            if ($brickLinkId) return;

            $section->filter('table tr')->each(function (Crawler $row) use (&$brickLinkId) {
                if ($brickLinkId) return;

                if ($row->filter('td')->count() >= 2) {
                    $label = trim($row->filter('td')->eq(0)->text());
                    if (stripos($label, 'BrickLink') !== false) {
                        $valueText = trim($row->filter('td')->eq(1)->text());
                        $cleanId = preg_replace('/^(ID|No|Number):\s*/i', '', $valueText);
                        $brickLinkId = trim($cleanId);
                    }
                }
            });

            if (!$brickLinkId) {
                $section->filter('a, span, div')->each(function (Crawler $element) use (&$brickLinkId) {
                    if ($brickLinkId) return;

                    $text = $element->text();
                    if (preg_match('/(frnd|sw|hp|col|sh|bat|njo|lor|cty|poc)(\d+)/i', $text, $matches)) {
                        $brickLinkId = $matches[0];
                    }
                });
            }
        });

        if ($brickLinkId === 'v2') {
            return null;
        }

        return $brickLinkId;
    }
}
