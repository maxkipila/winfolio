<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Imports\ThemeImport;
use App\Imports\MinifigImport;
use App\Imports\SetImport;
use App\Imports\SetMinifigImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ImportLegoData extends Command
{
    protected $signature = 'import:lego-data {--truncate : Vyprázdní tabulky před importem}';
    protected $description = 'Stáhne a importuje LEGO data (themes, sets, minifigs, relationships). Standardně aktualizuje existující záznamy.';

    protected $baseUrl = 'https://cdn.rebrickable.com/media/downloads/';

    protected $datasets = [
        'themes' => [
            'file'   => 'themes.csv.gz',
            'import' => ThemeImport::class,
            'model'  => \App\Models\Theme::class
        ],
        'sets' => [
            'file'   => 'sets.csv.gz',
            'import' => SetImport::class,
            'model'  => \App\Models\Product::class
        ],
        'minifigs' => [
            'file'   => 'minifigs.csv.gz',
            'import' => MinifigImport::class,
            'model'  => \App\Models\Product::class
        ],
        'inventories' => [
            'file'   => 'inventories.csv.gz',
            'import' => null,
            'model'  => null
        ],
        'inventory_minifigs' => [
            'file'   => 'inventory_minifigs.csv.gz',
            'import' => null,
            'model'  => null
        ]
    ];

    public function handle()
    {
        DB::disableQueryLog();
        ini_set('memory_limit', '1024M');

        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        try {
            $this->info("Začínám import LEGO dat...");

            // 1. Témata
            $this->importDataset('themes');

            // 2. Produkty - sety a minifigurky
            $this->importDataset('sets');
            $this->importDataset('minifigs');

            // 3. Vazby mezi sety a minifigurkami
            $this->importRelationships();

            $this->info("Import LEGO dat dokončen");
            return 0;
        } catch (\Exception $e) {
            $this->error("Chyba při importu: " . $e->getMessage());
            return 1;
        }
    }

    /* protected function importRelationships()
    {
        $this->info("Import vazeb mezi sety a minifigurkami...");

        $inventoriesPath = $this->downloadAndExtractFile('inventories');
        $inventoryMinifigsPath = $this->downloadAndExtractFile('inventory_minifigs');

        if (!$inventoriesPath || !$inventoryMinifigsPath) {
            $this->error("Nelze importovat vazby - chybí soubory");
            return;
        }

        try {
            $importer = new SetMinifigImport();
            $importer->import($inventoriesPath, $inventoryMinifigsPath);
            $this->info("Vazby byly úspěšně importovány");
        } catch (\Exception $e) {
            $this->error("Chyba při importu vazeb: " . $e->getMessage());
        }

        // Úklid souborů
        if (file_exists($inventoriesPath)) unlink($inventoriesPath);
        if (file_exists($inventoryMinifigsPath)) unlink($inventoryMinifigsPath);
    } */
    /* 
    public function handle()
    {
        DB::disableQueryLog();

        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $dataType = $this->argument('dataType');

        if ($dataType && isset($this->datasets[$dataType])) {
            if ($dataType === 'relationships') {
                $this->importRelationships();
            } else {
                $this->importDataset($dataType);
            }
        } else if (!$dataType) {
            // Nejprve importujeme základní datové sady
            foreach (['themes', 'sets', 'minifigs'] as $type) {
                $this->importDataset($type);
            }

            // Nakonec importujeme vazby
            $this->importRelationships();
        } else {
            $this->error("Neznámý typ dat: $dataType");
            return 1; 
        }

        return 0;
    } */

    protected function importRelationships()
    {
        $this->info("Import vazeb mezi sety a minifigurkami...");

        $inventoriesPath = $this->downloadAndExtractFile('inventories');
        $inventoryMinifigsPath = $this->downloadAndExtractFile('inventory_minifigs');

        if (!$inventoriesPath || !$inventoryMinifigsPath) {
            $this->error("Nelze importovat vazby - chybí soubory");
            return;
        }

        try {
            $importer = new SetMinifigImport();
            $importer->import($inventoriesPath, $inventoryMinifigsPath);
            $this->info("Vazby byly úspěšně importovány");
        } catch (\Exception $e) {
            $this->error("Chyba při importu vazeb: " . $e->getMessage());
        }

        // Úklid souborů
        if (file_exists($inventoriesPath)) unlink($inventoriesPath);
        if (file_exists($inventoryMinifigsPath)) unlink($inventoryMinifigsPath);
    }

    protected function downloadAndExtractFile($type)
    {
        if (!isset($this->datasets[$type]) || !$this->datasets[$type]['file']) {
            return null;
        }

        $url = $this->baseUrl . $this->datasets[$type]['file'];
        $resourcePath = resource_path('imports');
        $gzFilePath = "{$resourcePath}/{$type}.csv.gz";
        $csvFilePath = "{$resourcePath}/{$type}.csv";

        // Vytvoření adresáře
        if (!file_exists($resourcePath)) {
            mkdir($resourcePath, 0755, true);
        }

        // Stažení souboru
        $response = Http::get($url);
        if ($response->failed()) {
            $this->error("Nepodařilo se stáhnout {$type}: " . $response->status());
            return null;
        }

        file_put_contents($gzFilePath, $response->body());

        // Rozbalení souboru
        $gzipContent = file_get_contents($gzFilePath);
        $csvContent = gzdecode($gzipContent);

        if ($csvContent === false) {
            $this->error("Nepodařilo se rozbalit {$type}");
            unlink($gzFilePath);
            return null;
        }

        file_put_contents($csvFilePath, $csvContent);
        unlink($gzFilePath);

        return $csvFilePath;
    }

    protected function importDataset($type)
    {
        $this->info("Import {$type}...");

        if ($this->option('truncate')) {
            $modelClass = $this->datasets[$type]['model'];
            if ($modelClass) {
                $modelClass::truncate();
                $this->info("Tabulka {$type} vyprázdněna");
            }
        }

        $dataset = $this->datasets[$type];
        if (!$dataset['import']) {
            $this->info("Přeskakuji import {$type} - nemá importér");
            return;
        }

        $csvFilePath = $this->downloadAndExtractFile($type);
        if (!$csvFilePath) {
            $this->error("Soubor pro {$type} se nepodařilo stáhnout");
            return;
        }

        $this->processCsvInChunks($csvFilePath, $dataset['import']);

        // Úklid
        unlink($csvFilePath);
    }

    protected function processCsvInChunks($filePath, $importClass)
    {
        $chunkSize = 1000;
        $header = null;
        $rowCount = 0;
        $rows = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($header === null) {
                    $header = $data;
                    continue;
                }

                $rowCount++;
                $rows[] = array_combine($header, $data);

                if ($rowCount % $chunkSize === 0) {
                    // Create a temporary CSV file for the current chunk
                    $tempFilePath = tempnam(sys_get_temp_dir(), 'chunk') . '.csv';
                    $tempHandle = fopen($tempFilePath, 'w');
                    fputcsv($tempHandle, $header);
                    foreach ($rows as $row) {
                        fputcsv($tempHandle, $row);
                    }
                    fclose($tempHandle);

                    // Import the temporary CSV file
                    Excel::import(new $importClass, $tempFilePath);

                    // Clean up the temporary file
                    unlink($tempFilePath);

                    $rows = [];
                    /* $this->info("Processed {$rowCount} rows"); */
                }
            }

            if (!empty($rows)) {
                $tempFilePath = tempnam(sys_get_temp_dir(), 'chunk') . '.csv';
                $tempHandle = fopen($tempFilePath, 'w');
                fputcsv($tempHandle, $header);
                foreach ($rows as $row) {
                    fputcsv($tempHandle, $row);
                }
                fclose($tempHandle);
                Excel::import(new $importClass, $tempFilePath);
                unlink($tempFilePath);

                $this->info("Processed final batch of rows");
            }

            fclose($handle);
        } else {
            $this->error("Nepodařilo se otevřít soubor {$filePath}");
        }
    }
}
