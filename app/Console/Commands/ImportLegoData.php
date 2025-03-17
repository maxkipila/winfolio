<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Imports\ThemeImport;
use App\Imports\MinifigImport;
use App\Imports\SetImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportLegoData extends Command
{
    protected $signature = 'import:lego-data {dataType?}';
    protected $description = 'Stáhne a importuje LEGO data (themes, sets, minifigs)';

    protected $baseUrl = 'https://rebrickable.com/media/downloads/';
    protected $datasets = [
        'themes' => [
            'file' => 'themes.csv.gz',
            'import' => ThemeImport::class
        ],
        'sets' => [
            'file' => 'sets.csv.gz',
            'import' => SetImport::class
        ],
        'minifigs' => [
            'file' => 'minifigs.csv.gz',
            'import' => MinifigImport::class
        ]
    ];

    public function handle()
    {

        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $dataType = $this->argument('dataType');

        if ($dataType && isset($this->datasets[$dataType])) {
            $this->importDataset($dataType);
        } else if (!$dataType) {
            foreach ($this->datasets as $type => $dataset) {
                $this->importDataset($type);
            }
        } else {
            $this->error("Neznámý typ dat: $dataType");
            return 1;
        }

        return 0;
    }

    protected function importDataset($type)
    {
        $dataset = $this->datasets[$type];
        $url = $this->baseUrl . $dataset['file'];
        $resourcePath = resource_path('imports');
        $gzFilePath = "{$resourcePath}/{$type}.csv.gz";
        $csvFilePath = "{$resourcePath}/{$type}.csv";

        $this->info("Stahuji {$type} data z: $url");

        if (!file_exists($resourcePath)) {
            mkdir($resourcePath, 0755, true);
        }

        $response = Http::get($url);
        if ($response->failed()) {
            $this->error("Nepodařilo se stáhnout soubor {$type}: " . $response->status());
            return;
        }

        file_put_contents($gzFilePath, $response->body());
        $this->info("Soubor {$type} stažen a uložen do: {$gzFilePath}");

        $gzipContent = file_get_contents($gzFilePath);
        $csvContent = gzdecode($gzipContent);

        if ($csvContent === false) {
            $this->error("Nepodařilo se rozbalit gzip soubor pro {$type}");
            return;
        }

        file_put_contents($csvFilePath, $csvContent);
        $this->info("Soubor {$type} rozbalen do: {$csvFilePath}");

        if (!file_exists($csvFilePath)) {
            $this->error("Soubor {$csvFilePath} neexistuje!");
            return;
        }
        $this->info("Soubor existuje na cestě: {$csvFilePath}");

        Excel::import(new $dataset['import'], $csvFilePath);
        $this->info("Data {$type} byla úspěšně importována do databáze");

        unlink($gzFilePath);
        unlink($csvFilePath);
        $this->info("Dočasné soubory pro {$type} byly smazány");
    }
}
