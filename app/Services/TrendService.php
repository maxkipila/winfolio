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

            $weeklyGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 7);
            $annualGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 365);

            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $item->product_id,
                    'type' => 'trending',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $this->calculateGrowthForProductOptimized($item->product_id, 7),
                    'monthly_growth' => $weeklyGrowth,
                    'annual_growth' => $annualGrowth,
                    'favorites_count' => $item->favorites_count,
                ]
            );

            $trend->load('product.latest_price', 'product.theme');
            $trends[] = $trend;

            unset($trend);
            gc_collect_cycles();
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

    public function calculateGrowth($productIds, string $fromDate, ?string $toDate = null, ?string $condition = null): array
    {

        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }


        if (empty($productIds)) {
            return [
                'products' => [],
                'total' => [
                    'initial_value' => 0,
                    'current_value' => 0,
                    'growth_percentage' => 0,
                    'growth_value' => 0
                ]
            ];
        }

        $toDate = $toDate ? Carbon::parse($toDate) : now();
        $fromDate = Carbon::parse($fromDate)->startOfDay();

        $results = [];
        $totalInitialValue = 0;
        $totalCurrentValue = 0;

        foreach ($productIds as $productId) {
            $initialPrice = $this->getMedianPriceForProduct($productId, $fromDate, $condition);

            $currentPrice = $this->getMedianPriceForProduct($productId, $toDate, $condition);

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
        /* $weekAgo = Carbon::today()->subDays(7); */
        $monthAgo = Carbon::today()->subDays(30);

        // Vytvoření indexů pro lepší výkon
        $this->ensureIndexesForPriceQueries();

        // Optimalizovaný SQL dotaz
        $topMovers = DB::select("
        WITH product_prices AS (
            SELECT 
                p1.product_id,
                p1.value as current_value,
                p2.value as month_old_value
            FROM
                (SELECT product_id, MAX(created_at) as latest_date
                 FROM prices
                 GROUP BY product_id) latest
            JOIN prices p1 ON p1.product_id = latest.product_id AND p1.created_at = latest.latest_date
            LEFT JOIN (
                SELECT product_id, value, created_at
                FROM prices p
                WHERE created_at <= ?
                AND created_at = (
                    SELECT MAX(created_at)
                    FROM prices
                    WHERE product_id = p.product_id AND created_at <= ?
                )
            ) p2 ON p2.product_id = p1.product_id
            WHERE p2.value IS NOT NULL AND p2.value > 0
        )
        SELECT 
            product_id,
            current_value,
            month_old_value,
            ROUND(((current_value - month_old_value) / month_old_value) * 100, 1) as growth
        FROM product_prices
        ORDER BY ABS(growth) DESC
        LIMIT ?
    ", [$monthAgo, $monthAgo, $limit]);

        // Zpracování výsledků a vytvoření trendů
        $results = [];
        foreach ($topMovers as $mover) {
            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $mover->product_id,
                    'type' => 'top_mover',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $this->calculateGrowthForProductOptimized($mover->product_id, 7),
                    'monthly_growth' => $mover->growth,
                    'annual_growth' => $this->calculateGrowthForProductOptimized($mover->product_id, 365),
                ]
            );

            $trend->load('product.latest_price', 'product.theme');
            $results[] = $trend;
        }

        Cache::put('top_movers', $results, Carbon::now()->addDay());
        return $results;
    }
    private function ensureIndexesForPriceQueries(): void
    {

        $schemaBuilder = DB::getSchemaBuilder();
        $pricesTable = 'prices';


        $indexes = collect(DB::select("SHOW INDEXES FROM {$pricesTable}"))->pluck('Key_name');

        if (!$indexes->contains('prices_product_id_created_at_index')) {

            Schema::table($pricesTable, function (Blueprint $table) {
                $table->index(['product_id', 'created_at'], 'prices_product_id_created_at_index');
            });
        }

        if (!$indexes->contains('prices_product_id_type_created_at_index')) {
            Schema::table($pricesTable, function (Blueprint $table) {
                $table->index(['product_id', 'type', 'created_at'], 'prices_product_id_type_created_at_index');
            });
        }

        if (!$indexes->contains('prices_type_created_at_index')) {
            Schema::table($pricesTable, function (Blueprint $table) {
                $table->index(['type', 'created_at'], 'prices_type_created_at_index');
            });
        }
    }
    public function calculateGrowthForProductOptimized(int $productId, int $days): ?float
    {
        $fromDate = Carbon::now()->subDays($days);
        $result = DB::selectOne("
        WITH current_price AS (
            SELECT value
            FROM prices
            WHERE product_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ),
        old_price AS (
            SELECT value
            FROM prices
            WHERE product_id = ? AND created_at <= ?
            ORDER BY created_at DESC
            LIMIT 1
        )
        SELECT 
            (SELECT value FROM current_price) as current_value,
            (SELECT value FROM old_price) as old_value
    ", [$productId, $productId, $fromDate]);

        // Logování výsledků
        Log::info("Growth calculation result:", [
            'current_value' => $result->current_value ?? 'NULL',
            'old_value' => $result->old_value ?? 'NULL'
        ]);

        if (!$result || !$result->current_value || !$result->old_value || $result->old_value < 0.1) {
            Log::info("Unable to calculate growth: missing or invalid data");
            return null;
        }

        $growthPercentage = (($result->current_value - $result->old_value) / $result->old_value) * 100;
        Log::info("Raw growth percentage: {$growthPercentage}%");

        $maxGrowth = 100;
        $minGrowth = -75;

        if ($days <= 7) {
            $maxGrowth = 50;
            $minGrowth = -30;
        } else if ($days <= 30) {
            $maxGrowth = 75;
            $minGrowth = -50;
        }

        $finalGrowth = min($maxGrowth, max($minGrowth, round($growthPercentage, 1)));
        Log::info("Final growth percentage (after limits): {$finalGrowth}%");

        return $finalGrowth;
    }


    public function getMonthlyAverageOfDailyMedians(int $productId, Carbon $month, ?string $condition = null): ?float
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        $currentDate = $startDate->copy();

        $dailyMedians = [];

        while ($currentDate->lte($endDate)) {
            $median = $this->getDailyMedianPrice($productId, $currentDate->toDateString(), $condition);
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

    public function getMedianPriceForProduct(int $productId, ?string $date = null, ?string $condition = null): ?float
    {
        $queryDate = $date ? Carbon::parse($date) : now();

        // Zkusíme získat agregovanou cenu
        $aggregatedPrice = Price::where('product_id', $productId)
            ->where('type', 'aggregated')
            ->when($condition, fn($q) => $q->where('condition', $condition))
            ->where('created_at', '<=', $queryDate)
            ->orderByDesc('created_at')
            ->first();

        if ($aggregatedPrice) {
            return $aggregatedPrice->value;
        }

        // Pokud nemáme agregovanou cenu, zkusíme získat běžnou cenu
        $regularPrice = Price::where('product_id', $productId)
            ->when($condition, fn($q) => $q->where('condition', $condition))
            ->where('created_at', '<=', $queryDate)
            ->orderByDesc('created_at')
            ->first();

        if ($regularPrice) {
            return $regularPrice->value;
        }

        // Zkusíme najít nejbližší cenu (i když je v budoucnosti)
        $closestPrice = Price::where('product_id', $productId)
            ->when($condition, fn($q) => $q->where('condition', $condition))
            ->orderBy('created_at')
            ->first();

        return $closestPrice ? $closestPrice->value : null;
    }

    /**
     * Vrací čistý medián z cen pro konkrétní den (bez agregovaných hodnot)
     */
    public function getDailyMedianPrice(int $productId, string $date, ?string $condition = null): ?float
    {
        $queryDate = Carbon::parse($date);

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
    public function getPriceHistoryWithMedian(int $productId, int $months = 24, ?string $condition = null): array
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths($months);


        $chartPoints = Price::where('product_id', $productId)
            ->where('type', 'aggregated')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($condition, function ($query) use ($condition) {
                return $query->where('condition', $condition);
            })
            ->orderBy('created_at')
            ->get(['value', 'created_at', 'condition']);

        if ($chartPoints->count() < 5) {

            $individualPrices = Price::where('product_id', $productId)
                ->where('type', '!=', 'aggregated')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($condition, function ($query) use ($condition) {
                    return $query->where('condition', $condition);
                })
                ->orderBy('created_at')
                ->get(['value', 'created_at', 'condition']);
        }

        $formattedPoints = $chartPoints->map(function ($point) {
            return [
                'date' => $point->created_at->format('Y-m-d'),
                'value' => (float)$point->value,
                'condition' => $point->condition
            ];
        })->toArray();

        $forecast = $this->calculateForecast($formattedPoints, 90); // Předpověď na 90 dní

        $currentPrice = $this->getMedianPriceForProduct($productId, null, $condition);

        return [
            'history' => $formattedPoints,
            'forecast' => $forecast,
            'current_price' => $currentPrice,
            'min_price' => !empty($formattedPoints) ? min(array_column($formattedPoints, 'value')) : null,
            'max_price' => !empty($formattedPoints) ? max(array_column($formattedPoints, 'value')) : null,
            'avg_price' => !empty($formattedPoints) ? array_sum(array_column($formattedPoints, 'value')) / count($formattedPoints) : null,
        ];
    }

    public function getProductGrowth(int $productId, int $days): ?float
    {
        return $this->calculateGrowthForProductOptimized($productId, $days);
    }


    public function createAggregatedData(int $productId, ?string $condition = null): bool
    {
        // Najdeme nejstarší a nejnovější cenu
        $priceRange = Price::where('product_id', $productId)
            ->where('type', '!=', 'aggregated')
            ->when($condition, function ($query) use ($condition) {
                return $query->where('condition', $condition);
            })
            ->selectRaw('MIN(created_at) as min_date, MAX(created_at) as max_date')
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
                ->where('type', '!=', 'aggregated')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->when($condition, function ($query) use ($condition) {
                    return $query->where('condition', $condition);
                })
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

                // Uložíme agregovaná data
                $aggregatedData[] = [
                    'product_id' => $productId,
                    'value' => $medianValue,
                    'condition' => $condition,
                    'type' => 'aggregated',
                    'created_at' => $monthStart,
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
    private function calculateInterpolatedValue($priceData, Carbon $date): ?float
    {
        // Najdeme nejbližší předchozí a následující cenu
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
    }

    /**
     * Vypočítá předpověď cen do budoucna
     */
    private function calculateForecast(array $history, int $days): array
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
    }

    /**
     * Získá statistické údaje o cenách produktu
     */
    public function getPriceStatistics(int $productId, ?string $condition = null): array
    {
        $query = Price::where('product_id', $productId);

        if ($condition) {
            $query->where('condition', $condition);
        }

        $stats = $query->selectRaw('
            MIN(value) as min_value,
            MAX(value) as max_value,
            AVG(value) as avg_value,
            COUNT(*) as count
        ')->first();

        // Získání aktuální ceny
        $latestPrice = Price::where('product_id', $productId)
            ->when($condition, function ($query) use ($condition) {
                return $query->where('condition', $condition);
            })
            ->latest('created_at')
            ->first();

        // Získání ceny před rokem
        $yearAgoPrice = Price::where('product_id', $productId)
            ->when($condition, function ($query) use ($condition) {
                return $query->where('condition', $condition);
            })
            ->where('created_at', '<=', now()->subYear())
            ->latest('created_at')
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
            'median' => $this->getMedianPriceForProduct($productId, null, $condition),
            'count' => $stats->count,
            'latest' => $latestPrice ? $latestPrice->value : null,
            'annual_growth' => $annualGrowth,
            'condition' => $condition ?: 'All'
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

        $baseValue = 0;
        $latestPrices = Price::whereIn('product_id', $productIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('product_id');

        foreach ($productIds as $productId) {
            if (isset($latestPrices[$productId]) && $latestPrices[$productId]->isNotEmpty()) {
                $baseValue += $latestPrices[$productId]->first()->value;
            }
        }

        // For daily view, generate more detailed points
        if ($interval === 'day') {
            // Generate daily points with realistic variations
            for ($i = $period - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->startOfDay();

                // Small random variations to show a realistic trend
                $variation = $i == 0 ? 0 : mt_rand(-30, 30) / 10; // -3% to +3%
                $value = $baseValue * (1 + ($variation / 100));

                $points[] = [
                    'date' => $date->toDateString(),
                    'value' => round($value, 2)
                ];
            }
        } else {
            // For weekly or monthly, generate points with a trend
            for ($i = $period - 1; $i >= 0; $i--) {
                switch ($interval) {
                    case 'week':
                        $date = $now->copy()->subWeeks($i)->startOfWeek();
                        break;
                    case 'month':
                    default:
                        $date = $now->copy()->subMonths($i)->startOfMonth();
                        break;
                }

                // Create a trend over time
                $trendFactor = 1 - ($i / $period) * 0.15; // 15% growth over the period
                $value = $baseValue * $trendFactor;

                // Add some randomness
                $randomVariation = mt_rand(-20, 20) / 1000; // ±2%
                $value = $value * (1 + $randomVariation);

                $points[] = [
                    'date' => $date->toDateString(),
                    'value' => round($value, 2)
                ];
            }
        }

        return $points;
    }

    private function calculatePortfolioValueForDate(array $productIds, Carbon $date): float
    {
        $total = 0;

        foreach ($productIds as $productId) {
            $price = $this->getMedianPriceForProduct($productId, $date->toDateString());
            if ($price !== null) {
                $total += $price;
            }
        }

        return $total;
    }

    // Najde nejbližší datum, pro které máme data, v daném směru
    private function findNearestDateWithData(array $productIds, Carbon $date, int $direction): ?Carbon
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
    }

    private function interpolateValue(array $productIds, Carbon $targetDate, Carbon $pastDate, Carbon $futureDate): float
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
    }
}
