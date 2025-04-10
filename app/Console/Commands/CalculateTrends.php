<?php

namespace App\Console\Commands;

use App\Services\TrendService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CalculateTrends extends Command
{
    protected $signature = 'app:calculate-trends';
    protected $description = 'Vypočítá trendující produkty a produkty s největšími cenovými změnami';

    public function handle(TrendService $trendService)
    {
        $this->info('Začínám výpočet trendů...');

        $startTime = now();

        // Vypočítáme trendující položky
        $trendingCount = count($trendService->calculateTrendingProducts());
        $this->info("Vypočítáno {$trendingCount} trendujících produktů.");

        // Vypočítáme top movers
        $topMoversCount = count($trendService->calculateTopMovers());
        $this->info("Vypočítáno {$topMoversCount} top movers produktů.");

        $duration = now()->diffInSeconds($startTime);
        $this->info("Výpočet dokončen za {$duration} sekund.");

        return Command::SUCCESS;
    }
}
