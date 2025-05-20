<?php

namespace App\Console\Commands;

use App\Models\LegoIdMapping;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeBrickLinkIdsCommand extends Command
{
    protected $signature = 'lego:scrape-bricklinkold
                           {--batch=10 : Počet minifigurek zpracovávaných v jedné dávce}
                           {--delay=2 : Zpoždění mezi požadavky v sekundách}
                           {--limit=1000 : Maximální počet minifigurek ke zpracování}
                           {--offset=0 : Začít od určitého offsetu}
                           {--timeout=30 : Timeout pro HTTP požadavky v sekundách}
                           {--retries=3 : Počet pokusů při selhání HTTP požadavku}';

    protected $description = 'Scrapuje BrickLink ID z Rebrickable pro minifigurky';

    // Přidat cache pro omezení opakovaných požadavků
    protected $requestCache = [];

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        // Načíst parametry
        $batch = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $timeout = (int) $this->option('timeout');
        $retries = (int) $this->option('retries');

        // Najít minifigurky bez BrickEconomy ID
        $query = LegoIdMapping::whereNull('brickeconomy_id')
            ->where('rebrickable_id', 'LIKE', 'fig-%')
            ->orderBy('id');

        // Pokud není žádné mapování, najdeme minifigurky, které nemají mapování vůbec
        if ($query->count() == 0) {
            $this->info("Žádná existující mapování nenalezena, hledám minifigurky bez mapování...");

            $minifigIds = Product::where('product_type', 'minifig')
                ->where('product_num', 'LIKE', 'fig-%')
                ->pluck('product_num')
                ->toArray();

            $existingMappingIds = LegoIdMapping::pluck('rebrickable_id')->toArray();
            $unmappedIds = array_diff($minifigIds, $existingMappingIds);

            if (empty($unmappedIds)) {
                $this->error("Žádné minifigurky bez mapování nenalezeny.");
                return 1;
            }

            // Dávkové vytvoření základních mapování místo po jednom
            $this->info("Vytvářím základní mapování pro " . count($unmappedIds) . " minifigurek...");

            $mappingsToCreate = [];
            $productsById = Product::whereIn('product_num', $unmappedIds)
                ->pluck('name', 'product_num')
                ->toArray();

            foreach ($unmappedIds as $id) {
                $mappingsToCreate[] = [
                    'rebrickable_id' => $id,
                    'name' => $productsById[$id] ?? null,
                    'notes' => 'Auto-generated: minifig without mapping',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Vložit data po dávkách pro optimalizaci paměti
                if (count($mappingsToCreate) >= 100) {
                    LegoIdMapping::insert($mappingsToCreate);
                    $mappingsToCreate = [];
                }
            }

            // Vložit zbývající data
            if (!empty($mappingsToCreate)) {
                LegoIdMapping::insert($mappingsToCreate);
            }

            $query = LegoIdMapping::whereNull('brickeconomy_id')
                ->where('rebrickable_id', 'LIKE', 'fig-%')
                ->orderBy('id');
        }

        // Aplikace offsetu a limitu
        if ($offset > 0) {
            $query->skip($offset);
        }
        if ($limit > 0) {
            $query->take($limit);
        }

        $totalToProcess = $query->count();
        $this->info("Celkem ke zpracování: {$totalToProcess} minifigurek");

        // Progress bar
        $bar = $this->output->createProgressBar($totalToProcess);
        $bar->start();

        $updated = 0;
        $failed = 0;
        $skipped = 0;

        // Optimalizace: zpracování po dávkách s hromadnou aktualizací
        $query->chunk($batch, function ($mappings) use (&$updated, &$failed, &$skipped, $bar, $delay, $timeout, $retries) {
            $updatedMappings = [];

            foreach ($mappings as $mapping) {
                $rebrickableId = $mapping->rebrickable_id;

                try {
                    // Sestavit URL na Rebrickable
                    $url = "https://rebrickable.com/minifigs/{$rebrickableId}/";

                    // Kontrola cache pro omezení duplicitních požadavků
                    if (isset($this->requestCache[$url])) {
                        $html = $this->requestCache[$url];
                    } else {
                        // Přidání retries pro stabilnější scraping
                        $attempt = 0;
                        $success = false;
                        $response = null;

                        while (!$success && $attempt < $retries) {
                            try {
                                $response = Http::timeout($timeout)
                                    ->withHeaders([
                                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36',
                                        'Accept' => 'text/html,application/xhtml+xml,application/xml',
                                        'Accept-Language' => 'en-US,en;q=0.9',
                                    ])
                                    ->get($url);

                                $success = $response->successful();
                            } catch (\Exception $e) {
                                $this->warn(" - {$rebrickableId}: Pokus " . ($attempt + 1) . " selhal: " . $e->getMessage());
                            }

                            $attempt++;
                            if (!$success && $attempt < $retries) {
                                sleep(1); // Krátké zpoždění mezi pokusy
                            }
                        }

                        if (!$success || !$response) {
                            throw new \Exception("Vyčerpány všechny pokusy");
                        }

                        $html = $response->body();
                        // Uložit do cache
                        $this->requestCache[$url] = $html;

                        // Omezení velikosti cache
                        if (count($this->requestCache) > 100) {
                            // Odstranit nejstarší položky
                            array_shift($this->requestCache);
                        }
                    }

                    $crawler = new Crawler($html);

                    // Optimalizované hledání BrickLink ID - jednodušší selektory
                    $brickLinkId = $this->extractBrickLinkId($crawler);

                    if ($brickLinkId) {
                        // Převést BrickLink ID na BrickEconomy ID
                        // Většinou jsou stejné nebo se liší jen ve velikosti písmen
                        $brickEconomyId = strtolower($brickLinkId);

                        // Připravit data pro hromadnou aktualizaci
                        $updatedMappings[] = [
                            'id' => $mapping->id,
                            'bricklink_id' => $brickLinkId,
                            'brickeconomy_id' => $brickEconomyId,
                            'notes' => ($mapping->notes ? $mapping->notes . ' | ' : '') . 'BrickLink ID scraped from Rebrickable',
                            'updated_at' => now(),
                        ];

                        $updated++;
                        $this->line(" - {$rebrickableId} -> BrickLink: {$brickLinkId}, BrickEconomy: {$brickEconomyId}");
                    } else {
                        $failed++;
                        $this->warn(" - {$rebrickableId}: BrickLink ID nenalezeno");
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $this->error(" - {$rebrickableId}: Výjimka: " . $e->getMessage());
                }

                $bar->advance();
            }

            // Hromadná aktualizace všech záznamů najednou
            if (!empty($updatedMappings)) {
                $this->batchUpdate($updatedMappings);
            }

            // Zpoždění mezi dávkami
            if ($delay > 0 && count($mappings) > 0) {
                sleep($delay);
            }

            // Vyčištění paměti
            gc_collect_cycles();
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Hotovo!");
        $this->info("Aktualizováno: {$updated}");
        $this->info("Selhalo: {$failed}");
        $this->info("Přeskočeno: {$skipped}");

        return 0;
    }

    /**
     * Optimalizovaná metoda pro extrakci BrickLink ID
     */
    private function extractBrickLinkId(Crawler $crawler): ?string
    {
        // Nejprve zkusíme najít odkaz přímo na BrickLink s ID v URL
        $brickLinkId = null;

        // Procházíme všechny odkazy
        $crawler->filter('a')->each(function (Crawler $link) use (&$brickLinkId) {
            if ($brickLinkId) return; // Už jsme našli

            $href = $link->attr('href');

            // Hledáme specificky v URL části bricklink.com/v2/catalog/catalogitem.page?
            if (preg_match('/bricklink\.com\/v2\/catalog\/catalogitem\.page\?([^&]*)/', $href, $matches)) {
                // Extrahujeme ID z query parametrů
                parse_str($matches[1], $params);
                if (isset($params['M'])) {
                    $brickLinkId = $params['M'];
                    return;
                }
            }

            // Také hledáme v textu odkazu, pokud obsahuje "BrickLink" a vypadá jako ID
            $text = trim($link->text());
            if (stripos($text, 'bricklink') !== false && preg_match('/([a-z0-9]{3,10})/i', $text, $matches)) {
                $brickLinkId = $matches[1];
            }
        });

        if ($brickLinkId) return $brickLinkId;

        // Druhý pokus: hledáme v tabulce External IDs
        $crawler->filter('h4, h3')->each(function (Crawler $heading) use (&$brickLinkId, $crawler) {
            if ($brickLinkId) return; // Už jsme našli

            $headingText = $heading->text();
            if (stripos($headingText, 'External') !== false) {
                // Našli jsme sekci External IDs, nyní hledáme řádek s BrickLink
                $heading->nextAll()->filter('table tr')->each(function (Crawler $row) use (&$brickLinkId) {
                    if ($brickLinkId) return; // Už jsme našli

                    if ($row->filter('td')->count() >= 2) {
                        $label = trim($row->filter('td')->eq(0)->text());
                        if (stripos($label, 'BrickLink') !== false) {
                            $valueText = trim($row->filter('td')->eq(1)->text());
                            // Očistíme text od případného "ID:" nebo podobných prefixů
                            $cleanId = preg_replace('/^(ID|No|Number):\s*/i', '', $valueText);
                            $brickLinkId = trim($cleanId);
                        }
                    }
                });
            }
        });

        // Pokud jsme našli něco jako "v2", je to pravděpodobně nesprávná extrakce
        if ($brickLinkId === 'v2') {
            return null;
        }

        return $brickLinkId;
    }

    /**
     * Dávková aktualizace záznamů
     */
    private function batchUpdate(array $records): void
    {
        // Pro každý záznam provedeme samostatný update
        // Využíváme query builder místo Eloquent
        foreach ($records as $record) {
            $id = $record['id'];
            unset($record['id']);

            DB::table('lego_id_mappings')
                ->where('id', $id)
                ->update($record);
        }
    }
}
