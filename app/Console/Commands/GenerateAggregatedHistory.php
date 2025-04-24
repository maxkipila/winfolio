<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateAggregatedHistory extends Command
{
    protected $signature = 'prices:generate-history {--months=12}';
    protected $description = 'Spustí agregaci cen zpětně za více měsíců';

    public function handle()
    {
        $months = (int) $this->option('months');
        $today = Carbon::now();

        $this->info("Spouštím agregaci zpětně za {$months} měsíců...");

        for ($i = 0; $i < $months; $i++) {
            $targetDate = $today->copy()->subMonths($i)->startOfMonth()->toDateString();

            $this->info("→ {$targetDate}");
            $this->call('prices:aggregate', [
                '--date' => $targetDate,
                '--force' => true
            ]);
        }

        $this->info("✅ Hotovo");
    }
}
