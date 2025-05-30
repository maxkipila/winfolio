<?php

namespace App\Console\Commands;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class InitialProductImport extends Command
{
    protected $signature = 'import:initial';

    protected $description = 'Import dat';

    public function handle()
    {
        ini_set('memory_limit', '256M');

        Artisan::call('import:lego-data', [], $this->getOutput());
        Artisan::call('app:assign-themes-to-minifigs', [], $this->getOutput());

        $jobs = ScrapeRebrickableForIDsCommand::scrape($this);

        $chunkSize = 500; // Number of jobs per batch
        $chunks = $jobs->chunk($chunkSize); // Split jobs into chunks
        $length = $chunks->count() - 1;

        foreach ($chunks as $key => $chunk) {
            Bus::batch($chunk)
                ->finally(function (Batch $batch) {
                    $hasPendingBatches = DB::table('job_batches')
                        ->where('name', 'like', '%RebrickableScrape%') // Replace with your batch name or pattern
                        ->whereNull('cancelled_at') // Ensure the batch is not cancelled
                        ->whereNull('finished_at') // Ensure the batch is not finished
                        ->exists();

                    Log::info("Any pending batches?", ['hasPendingBatches' => $hasPendingBatches]);

                    if (!$hasPendingBatches) {
                        ini_set('memory_limit', '512M');
                        Artisan::call('import:lego-images');
                        Artisan::call('import:historical');
                    }
                })
                ->name("RebrickableScrape")
                ->allowFailures()
                ->dispatch();

            $this->info("Chunk $key/{$length} dispatched");
        }
        $this->info("All chunks dispatched");

        return self::SUCCESS;
    }
}
