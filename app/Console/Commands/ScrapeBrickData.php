<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ScrapeBrickData extends Command
{
    protected $signature = 'lego:scrape
                           {--source=brickeconomy : Zdroj dat (rebrickable, brickeconomy)}
                           {--mode=single : Režim (single, bulk)}
                           {--product-nums=* : Seznam produktových čísel pro single režim}
                           {--file= : Cesta k souboru s produktovými čísly}
                           {--type=all : Typ produktů (all, set, minifig)}
                           {--limit=50 : Počet produktů ke zpracování}
                           {--offset=0 : Začátek od určitého offsetu}
                           {--force : Přepsat existující data}
                           {--delay=2 : Zpoždění mezi požadavky v sekundách}';

    protected $description = 'Jednotný nástroj pro scrapování LEGO dat z různých zdrojů';

    public function handle()
    {
        ini_set('memory_limit', '1G');
        DB::disableQueryLog();

        $source = $this->option('source');
        $mode = $this->option('mode');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $delay = (int) $this->option('delay');
        $file = $this->option('file');
        $force = $this->option('force') ? '--force' : '';
        $productNums = $this->option('product-nums');

        // Příprava příkazu podle zdroje a režimu
        if ($source === 'brickeconomy') {
            if ($mode === 'single') {
                if (empty($productNums) && !$file) {
                    $this->error('Musíte zadat alespoň jedno produktové číslo nebo cestu k souboru.');
                    return 1;
                }

                $this->info('Scrapuji data z BrickEconomy (single režim)...');

                $command = 'scrape:brickeconomy';
                $params = [
                    '--delay' => $delay
                ];

                if ($file) {
                    $params['--file'] = $file;
                }

                if (!empty($productNums)) {
                    $params['product_num'] = $productNums;
                }

                if ($force) {
                    $params['--save'] = true;
                }

                Artisan::call($command, $params);
                $this->info(Artisan::output());
            } else {
                $this->info('Scrapuji data z BrickEconomy (bulk režim)...');

                $command = 'scrape:brickeconomy-bulk';
                $params = [
                    '--type' => $this->option('type'),
                    '--limit' => $limit,
                    '--offset' => $offset,
                    '--delay' => $delay,
                    '--chunk' => min(50, $limit),
                    '--throttle' => 5
                ];

                if ($force) {
                    $params['--force'] = true;
                }

                Artisan::call($command, $params);
                $this->info(Artisan::output());
            }
        } elseif ($source === 'mapped-minifigs') {
            // Scrapování předmapovaných minifigurek
            $this->info('Scrapuji data pro předmapované minifigurky...');

            Artisan::call('scrape:mapped-minifigs', [
                '--limit' => $limit,
                '--delay' => $delay,
                '--offset' => $offset,
                '--force' => $force
            ]);

            $this->info(Artisan::output());
        } else {
            // Přidejte zde další podporované zdroje
            $this->error("Nepodporovaný zdroj: {$source}");
            return 1;
        }

        return 0;
    }
}
