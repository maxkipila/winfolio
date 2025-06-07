<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Product;
use App\Models\Trend;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrendService
{

    public function calculateTrendingProducts(int $limit = 8, int $days = 30): array
    {
        // datum před X dny
        $startDate = Carbon::now()->subDays($days);
        $today = Carbon::today();

        $trendingProductIds = DB::table('product_user')
            ->select('product_id', DB::raw('COUNT(*) as favorites_count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('product_id')
            ->orderBy('favorites_count', 'desc')
            ->limit($limit)
            ->get();

        $trends = [];

        foreach ($trendingProductIds as $item) {
            // Růst za poslední týden a rok - výpočet před vytvořením Trend
            $weeklyGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 7);
            $monthlyGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 30);
            $annualGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 365);

            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $item->product_id,
                    'type' => 'trending',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $weeklyGrowth,
                    'monthly_growth' => $monthlyGrowth,
                    'annual_growth' => $annualGrowth,
                    'favorites_count' => $item->favorites_count,
                ]
            );

            $trend->load('product.latest_price', 'product.theme');
            $trends[] = $trend;
        }

        return $trends;
    }

    public function getTrendingProducts(Request $request, int $days = 7)
    {
        $startDate = Carbon::now()->subDays($days);

        $query = DB::table('product_user')
            ->select('product_id', DB::raw('COUNT(*) as favorites_count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('product_id');

        $productIds = $query->pluck('product_id')->toArray();

        $productsQuery = Product::whereIn('id', $productIds)
            ->with(['latest_price', 'theme']);

        $products = $productsQuery
            ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
            ->paginate($request->paginate ?? 10);


        $products->getCollection()->transform(function ($product) {
            $product->weekly_growth = $this->calculateGrowthForProductOptimized($product->id, 7);
            $product->annual_growth = $this->calculateGrowthForProductOptimized($product->id, 365);


            $product->favorites_count = DB::table('product_user')
                ->where('product_id', $product->id)
                ->count();

            return $product;
        });

        return $products;
    }

    public function calculateGrowth($productIds, string $fromDate, ?string $toDate = null): array
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $toDate = $toDate ? Carbon::parse($toDate) : now();
        $fromDate = Carbon::parse($fromDate)->startOfDay();

        $results = [];
        $totalInitialValue = 0;
        $totalCurrentValue = 0;

        foreach ($productIds as $productId) {
            $initialPrice = $this->getMedianPriceForProduct($productId, $fromDate);
            $currentPrice = $this->getMedianPriceForProduct($productId, $toDate);

            if ($initialPrice === null || $currentPrice === null) {
                $results[$productId] = [
                    'initial_value' => $initialPrice,
                    'current_value' => $currentPrice,
                    'growth_percentage' => null,
                    'growth_value' => null
                ];
                continue;
            }

            // Výpočet růstu
            $growthValue = $currentPrice - $initialPrice;
            $growthPercentage = $initialPrice > 0
                ? ($growthValue / $initialPrice) * 100
                : null;

            $results[$productId] = [
                'initial_value' => $initialPrice,
                'current_value' => $currentPrice,
                'growth_percentage' => $growthPercentage,
                'growth_value' => $growthValue
            ];

            $totalInitialValue += $initialPrice;
            $totalCurrentValue += $currentPrice;
        }

        // Celkový růst portfolia
        $totalGrowthValue = $totalCurrentValue - $totalInitialValue;
        $totalGrowthPercentage = $totalInitialValue > 0
            ? ($totalGrowthValue / $totalInitialValue) * 100
            : 0;

        return [
            'products' => $results,
            'total' => [
                'initial_value' => $totalInitialValue,
                'current_value' => $totalCurrentValue,
                'growth_percentage' => $totalGrowthPercentage,
                'growth_value' => $totalGrowthValue
            ]
        ];
    }


    /**
     * Vypočítá produkty s největšími cenovými změnami
     *
     * @param int $limit Počet produktů, které chceme vrátit
     * @return array
     */


    public function calculateTopMovers(int $limit = 8): array
    {
        $today = Carbon::today();
        $monthAgo = Carbon::today()->subDays(30);

        // Jednoduchý přímý SQL dotaz pro nalezení produktů s největšími cenovými změnami
        $query = "
        SELECT 
            p1.product_id,
            p1.value as current_value,
            p2.value as past_value,
            ROUND(((p1.value - p2.value) / p2.value) * 100, 1) as growth_percentage
        FROM 
            (SELECT product_id, value FROM prices WHERE (product_id, date) IN 
                (SELECT product_id, MAX(date) FROM prices GROUP BY product_id)
            ) p1
        JOIN 
            (SELECT product_id, value FROM prices WHERE (product_id, date) IN 
                (SELECT product_id, MAX(date) FROM prices 
                 WHERE date <= ? GROUP BY product_id)
            ) p2 ON p1.product_id = p2.product_id
        WHERE 
            p2.value > 0
        ORDER BY 
            ABS(growth_percentage) DESC
        LIMIT ?
    ";


        $topMovers = DB::select($query, [$monthAgo, $limit]);

        $results = [];

        foreach ($topMovers as $mover) {
            // Vytvořit nebo aktualizovat záznam o trendu
            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $mover->product_id,
                    'type' => 'top_mover',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $this->getSimpleGrowth($mover->product_id, 7),
                    'monthly_growth' => $mover->growth_percentage,
                    'annual_growth' => $this->getSimpleGrowth($mover->product_id, 365),
                ]
            );

            // Načíst související data a přidat do výsledků
            $trend->load('product.latest_price', 'product.theme');
            $results[] = $trend;
        }

        return $results;
    }

    public function getSimpleGrowth(int $productId, int $days): ?float
    {
        $latest = Price::where('product_id', $productId)
            ->orderBy('date', 'desc')
            ->first();

        if (!$latest)
            return 0;

        $before = Carbon::parse($latest->date)->subDays($days);

        $old = Price::where('product_id', $productId)
            ->where('date', '<=', $before)
            ->orderBy('date', 'desc')
            ->first() ??
            Price::where('product_id', $productId)
            ->orderBy('date', 'asc')
            ->first();

        if (!$latest || !$old || $old->value < 0.5) {
            return null;
        }

        return round((($latest->value - $old->value) / $old->value) * 100, 2);
    }

    public function calculateGrowthForProductOptimized(int $productId, int $days): ?float
    {
        return $this->getSimpleGrowth($productId, $days);
    }

    public function getAnnualizedGrowth($productId)
    {
        $oldest = Price::where('product_id', $productId)
            ->orderBy('date', 'asc')
            ->first();

        $latest = Price::where('product_id', $productId)
            ->orderBy('date', 'desc')
            ->first();

        if (!$oldest || !$latest || $oldest->id === $latest->id || $oldest->value < 0.5) {
            return null;
        }

        $days = Carbon::parse($oldest->date)->diffInDays(Carbon::parse($latest->date));
        if ($days <= 0) {
            return null;
        }

        $growth = (($latest->value - $oldest->value) / $oldest->value) * 100;
        $annualized = (pow($latest->value / $oldest->value, 365 / $days) - 1) * 100;

        return round($annualized, 2);
    }


    public function getMonthlyAverageOfDailyMedians(int $productId, Carbon $month): ?float
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $currentDate = $startDate->copy();

        $dailyMedians = [];

        while ($currentDate->lte($endDate)) {
            $median = $this->getDailyMedianPrice($productId, $currentDate->toDateString());
            if (!is_null($median)) {
                $dailyMedians[] = $median;
            }
            $currentDate->addDay();
        }

        if (empty($dailyMedians)) {
            return null;
        }

        return round(array_sum($dailyMedians) / count($dailyMedians), 2);
    }

    public function getMedianPriceForProduct(int $productId, ?string $date = null): ?float
    {
        $queryDate = $date ? Carbon::parse($date) : now();

        // Získání ceny k danému datu nebo před ním
        $price = Price::where('product_id', $productId)
            ->where('date', '<=', $queryDate)
            ->orderByDesc('date')
            ->first();

        if ($price) {
            return $price->value;
        }

        $futurePrice = Price::where('product_id', $productId)
            ->where('date', '>', $queryDate)
            ->orderBy('date')
            ->first();

        return $futurePrice ? $futurePrice->value : null;
    }

    /**
     * Vrací čistý medián z cen pro konkrétní den (bez agregovaných hodnot)
     */
    public function getDailyMedianPrice(int $productId, string $date): ?float
    {
        $queryDate = Carbon::parse($date);

        $dayStart = $queryDate->copy()->startOfDay();
        $dayEnd = $queryDate->copy()->endOfDay();

        $dailyPrices = Price::where('product_id', $productId)
            ->whereBetween('date', [$dayStart, $dayEnd])
            ->pluck('value')
            ->toArray();

        if (empty($dailyPrices)) {
            return null;
        }

        sort($dailyPrices);
        $count = count($dailyPrices);
        $middle = floor($count / 2);

        return $count % 2 === 0
            ? ($dailyPrices[$middle - 1] + $dailyPrices[$middle]) / 2
            : $dailyPrices[$middle];
    }
    /*  public function getMedianPriceForProduct(int $productId, ?string $date = null, ?string $condition = null): ?float
    {
        $queryDate = $date ? Carbon::parse($date) : now();

        // Získání všech cen daného dne
        $dayStart = $queryDate->copy()->startOfDay();
        $dayEnd = $queryDate->copy()->endOfDay();

        $dailyPricesQuery = Price::where('product_id', $productId)
            ->whereBetween('created_at', [$dayStart, $dayEnd]);

        if ($condition) {
            $dailyPricesQuery->where('condition', $condition);
        }

        $dailyPrices = $dailyPricesQuery->pluck('value')->toArray();

        if (empty($dailyPrices)) {
            return null;
        }

        sort($dailyPrices);
        $count = count($dailyPrices);
        $middle = floor($count / 2);

        return $count % 2 === 0
            ? ($dailyPrices[$middle - 1] + $dailyPrices[$middle]) / 2
            : $dailyPrices[$middle];
    } */

    //Získá mediánovou cenu produktu pro konkrétní datum
    /*  public function getMedianPriceForProduct(int $productId, ?string $date = null, ?string $condition = null): ?float
    {
    
        $queryDate = $date ? Carbon::parse($date) : now();

    
        $query = Price::where('product_id', $productId)
            ->where('created_at', '<=', $queryDate);

       
        if ($condition) {
            $query->where('condition', $condition);
        }

      
        $aggregatedPrice = $query->where('type', 'aggregated')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($aggregatedPrice) {
            return $aggregatedPrice->value;
        }
        
        $prices = $query->orderBy('created_at', 'desc')
            ->limit(10)  
            ->pluck('value')
            ->toArray();

        if (empty($prices)) {
            return null;
        }

        // Výpočet mediánu
        sort($prices);
        $count = count($prices);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($prices[$middle - 1] + $prices[$middle]) / 2;
        }

        return $prices[$middle];
    } */

    /**
     * Získá historická data cen pro graf včetně mediánu
     *
     * @param int $productId ID produktu
     * @param string|null $condition Konkrétní podmínka pro filtrování
     * @param int $months Počet měsíců zpět pro generování dat
     * @return array Data pro graf
     */

    //validni kod

    /*   public function getPriceHistoryWithMedian(int $productId, int $months = 24): array 
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths($months);

        // Filtrujeme pouze záznamy typu "aggregated"
        $pricePoints = Price::where('product_id', $productId)
            ->where('type', 'aggregated')  // Zde je klíčový filtr
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get(['value', 'created_at']);

        $formattedPoints = $pricePoints->map(function ($point) {
            return [
                'date' => $point->created_at->format('Y-m-d'),
                'value' => (float)$point->value
            ];
        })->toArray();

        return [
            'history' => $formattedPoints,
            'current_price' => !empty($formattedPoints) ? end($formattedPoints)['value'] : null,
            'min_price' => !empty($formattedPoints) ? min(array_column($formattedPoints, 'value')) : null,
            'max_price' => !empty($formattedPoints) ? max(array_column($formattedPoints, 'value')) : null,
            'avg_price' => !empty($formattedPoints) ? array_sum(array_column($formattedPoints, 'value')) / count($formattedPoints) : null,
        ];
    } */
    public function getPriceHistoryWithMedian(int $productId, int $months = 24): array
    {
        // $endDate = now();
        // $startDate = $endDate->copy()->subMonths($months);

        // všechny cenové body 
        $pricePoints = Price::where('product_id', $productId)
            ->orderBy('date')
            ->get(['value', 'date']);

        $formattedPoints = $pricePoints->map(function ($point) {
            /*  dd($point); */
            return [
                'date' => $point->date,
                'value' => (float)$point->value
            ];
        })->toArray();

        // Kalkulace základních statistických údajů
        $values = array_column($formattedPoints, 'value');
        $currentPrice = !empty($values) ? end($values) : $this->getMedianPriceForProduct($productId);

        return [
            'history' => $formattedPoints,
            'current_price' => $currentPrice,
            'min_price' => !empty($values) ? min($values) : null,
            'max_price' => !empty($values) ? max($values) : null,
            'avg_price' => !empty($values) ? array_sum($values) / count($values) : null,
        ];
    }
    public function getProductGrowth(int $productId, int $days): ?float
    {
        return $this->calculateGrowthForProductOptimized($productId, $days);
    }


    public function createAggregatedData(int $productId): bool
    {
        // Najdeme nejstarší a nejnovější cenu
        $priceRange = Price::where('product_id', $productId)
            ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
            ->first();

        if (!$priceRange->min_date) {
            return false;
        }

        $startDate = Carbon::parse($priceRange->min_date)->startOfMonth();
        $endDate = Carbon::parse($priceRange->max_date)->endOfMonth();
        $current = $startDate->copy();

        $aggregatedData = [];

        while ($current->lte($endDate)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $prices = Price::where('product_id', $productId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->pluck('value')
                ->toArray();

            if (!empty($prices)) {
                // Výpočet mediánu
                sort($prices);
                $count = count($prices);
                $middle = floor($count / 2);
                $medianValue = $count % 2 === 0
                    ? ($prices[$middle - 1] + $prices[$middle]) / 2
                    : $prices[$middle];


                $aggregatedData[] = [
                    'product_id' => $productId,
                    'value' => $medianValue,
                    'condition' => 'New',
                    'date' => $monthStart,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            $current->addMonth();
        }

        if (!empty($aggregatedData)) {
            Price::insert($aggregatedData);
            return true;
        }

        return false;
    }



    /**
     * Vypočítá interpolovanou hodnotu pro dny, kde nemáme přímá data
     */
    /*    private function calculateInterpolatedValue($priceData, Carbon $date): ?float
    {

        $prevPrice = $priceData->filter(function ($price) use ($date) {
            return $price->created_at->lt($date);
        })->sortByDesc('created_at')->first();

        $nextPrice = $priceData->filter(function ($price) use ($date) {
            return $price->created_at->gt($date);
        })->sortBy('created_at')->first();

        if ($prevPrice && $nextPrice) {
            $totalDays = $prevPrice->created_at->diffInDays($nextPrice->created_at);
            if ($totalDays === 0) return $prevPrice->value;

            $daysFromPrev = $prevPrice->created_at->diffInDays($date);
            $ratio = $daysFromPrev / $totalDays;

            return $prevPrice->value + ($nextPrice->value - $prevPrice->value) * $ratio;
        } else if ($prevPrice) {
            return $prevPrice->value;
        } else if ($nextPrice) {
            return $nextPrice->value;
        }

        return null;
    } */

    /**
     * Vypočítá předpověď cen do budoucna
     */
    /* private function calculateForecast(array $history, int $days): array
    {
        if (count($history) < 7) {
            return [];
        }

        // Pro jednoduchou předpověď použijeme lineární regresi na poslední data
        $lastPoints = array_slice($history, -30); // Posledních 30 bodů

        // Výpočet průměrného růstu za den
        $growthRates = [];
        for ($i = 1; $i < count($lastPoints); $i++) {
            $prev = $lastPoints[$i - 1]['value'];
            $curr = $lastPoints[$i]['value'];

            if ($prev > 0) {
                $growthRates[] = ($curr - $prev) / $prev;
            }
        }

        if (empty($growthRates)) {
            return [];
        }

        // Průměrný denní růst
        $avgDailyGrowth = array_sum($growthRates) / count($growthRates);

        // Poslední známá hodnota
        $lastPoint = end($history);
        $lastDate = Carbon::parse($lastPoint['date']);
        $lastValue = $lastPoint['value'];

        // Generování předpovědi
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $forecastDate = $lastDate->copy()->addDays($i);
            $forecastValue = $lastValue * pow(1 + $avgDailyGrowth, $i);

            $forecast[] = [
                'date' => $forecastDate->format('Y-m-d'),
                'value' => round($forecastValue, 2),
                'forecast' => true
            ];
        }

        return $forecast;
    } */

    /**
     * Získá statistické údaje o cenách produktu
     */
    // Upraveno:
    public function getPriceStatistics(int $productId): array
    {
        $query = Price::where('product_id', $productId);

        $stats = $query->selectRaw('
        MIN(value) as min_value,
        MAX(value) as max_value,
        AVG(value) as avg_value,
        COUNT(*) as count
    ')->first();

        // Získání aktuální ceny
        $latestPrice = Price::where('product_id', $productId)
            ->latest('date')
            ->first();

        // Získání ceny před rokem
        $yearAgoPrice = Price::where('product_id', $productId)
            ->where('date', '<=', now()->subYear())
            ->latest('date')
            ->first();

        // Vypočet ročního růstu
        $annualGrowth = null;
        if ($latestPrice && $yearAgoPrice && $yearAgoPrice->value > 0) {
            $annualGrowth = (($latestPrice->value - $yearAgoPrice->value) / $yearAgoPrice->value) * 100;
        }

        return [
            'min' => $stats->min_value,
            'max' => $stats->max_value,
            'avg' => $stats->avg_value,
            'median' => $this->getMedianPriceForProduct($productId),
            'count' => $stats->count,
            'latest' => $latestPrice ? $latestPrice->value : null,
            'annual_growth' => $annualGrowth
        ];
    }
    /**
     * Získá historické hodnoty portfolia pro zadané produkty a interval.
     *
     * @param array $productIds Pole ID produktů v portfoliu
     * @param string $interval 'day', 'week', nebo 'month'
     * @param int $period Počet intervalů zpět (např. 12 měsíců)
     * @return array
     */
    public function getPortfolioHistory(array $productIds, string $interval = 'month', int $period = 12): array
    {
        if (empty($productIds)) {
            return [];
        }

        $points = [];
        $now = Carbon::now();

        // Vytvoření datových bodů podle intervalu
        $dates = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            switch ($interval) {
                case 'day':
                    $dates[] = $now->copy()->subDays($i)->startOfDay();
                    break;
                case 'week':
                    $dates[] = $now->copy()->subWeeks($i)->startOfWeek();
                    break;
                case 'month':
                default:
                    $dates[] = $now->copy()->subMonths($i)->startOfMonth();
                    break;
            }
        }

        // Pro každé datum spočítáme hodnotu portfolia
        foreach ($dates as $date) {
            $total = 0;
            foreach ($productIds as $productId) {
                $price = $this->getMedianPriceForProduct($productId, $date->toDateString());
                if ($price !== null) {
                    $total += $price;
                }
            }

            $points[] = [
                'date' => $date->toDateString(),
                'value' => round($total, 2)
            ];
        }

        // Pokud nemáme žádné body, vrátíme prázdné pole
        if (empty($points)) {
            return [];
        }

        return $points;
    }

    /*  private function calculatePortfolioValueForDate(array $productIds, Carbon $date): float
    {
        $total = 0;

        foreach ($productIds as $productId) {
            $price = $this->getMedianPriceForProduct($productId, $date->toDateString());
            if ($price !== null) {
                $total += $price;
            }
        }

        return $total;
    } */

    // Najde nejbližší datum, pro které máme data, v daném směru
    /*    private function findNearestDateWithData(array $productIds, Carbon $date, int $direction): ?Carbon
    {
        $currentDate = $date->copy();
        $maxDays = 15; // Maximální počet dní, které budeme hledat

        for ($i = 1; $i <= $maxDays; $i++) {
            if ($direction > 0) {
                $currentDate->addDay();
            } else {
                $currentDate->subDay();
            }

            $hasData = false;
            foreach ($productIds as $productId) {
                if ($this->getMedianPriceForProduct($productId, $currentDate->toDateString()) !== null) {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                return $currentDate;
            }
        }

        return null;
    } */

    /*  private function interpolateValue(array $productIds, Carbon $targetDate, Carbon $pastDate, Carbon $futureDate): float
    {
        $pastValue = $this->calculatePortfolioValueForDate($productIds, $pastDate);
        $futureValue = $this->calculatePortfolioValueForDate($productIds, $futureDate);

        $totalDays = $pastDate->diffInDays($futureDate);
        $daysFromPast = $pastDate->diffInDays($targetDate);

        if ($totalDays === 0) {
            return $pastValue;
        }

        $ratio = $daysFromPast / $totalDays;
        return $pastValue + ($futureValue - $pastValue) * $ratio;
    } */
}
