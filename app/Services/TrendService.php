<?php

namespace App\Services;

use App\Models\Price;
use App\Models\Product;
use App\Models\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TrendService
{

    public function calculateTrendingProducts(int $limit = 8, int $days = 7): array
    {
        // Získáme datum před X dny
        $startDate = Carbon::now()->subDays($days);
        $today = Carbon::today();

        // Optimalizovaný dotaz - získáme pouze ID produktů a počet oblíbených
        $trendingProductIds = DB::table('product_user')
            ->select('product_id', DB::raw('COUNT(*) as favorites_count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('product_id')
            ->orderBy('favorites_count', 'desc')
            ->limit($limit)
            ->get();

        // Uložíme výsledky do databáze a cache
        $trends = [];

        foreach ($trendingProductIds as $item) {
            // Vypočítáme růst za poslední týden a rok pomocí SQL dotazu
            $weeklyGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 7);
            $annualGrowth = $this->calculateGrowthForProductOptimized($item->product_id, 365);

            // Uložíme trend
            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $item->product_id,
                    'type' => 'trending',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $weeklyGrowth,
                    'annual_growth' => $annualGrowth,
                    'favorites_count' => $item->favorites_count,
                ]
            );

            // Načteme produkt až po uložení trendu
            $trend->load('product.latest_price', 'product.theme');
            $trends[] = $trend;

            // Uvolníme paměť
            unset($trend);
            gc_collect_cycles();
        }

        // Uložíme do cache pro rychlé načítání
        Cache::put('trending_products', $trends, Carbon::now()->addDay());

        return $trends;
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
        $weekAgo = Carbon::today()->subDays(7);
        $yearAgo = Carbon::today()->subDays(365);

        // Optimalizovaný SQL dotaz, který používá JOIN s dílčími dotazy pro získání správných hodnot
        $topMovers = DB::select("
            WITH latest_price_dates AS (
                SELECT 
                    product_id,
                    MAX(created_at) AS latest_date
                FROM prices
                GROUP BY product_id
            ),
            week_old_price_dates AS (
                SELECT 
                    product_id,
                    MAX(created_at) AS week_old_date
                FROM prices
                WHERE created_at <= ?
                GROUP BY product_id
            ),
            year_old_price_dates AS (
                SELECT 
                    product_id,
                    MAX(created_at) AS year_old_date
                FROM prices
                WHERE created_at <= ?
                GROUP BY product_id
            )
            SELECT 
                lp.product_id,
                lp_prices.value AS current_value,
                wop_prices.value AS week_old_value,
                yop_prices.value AS year_old_value,
                CASE 
                    WHEN wop_prices.value > 0 THEN ((lp_prices.value - wop_prices.value) / wop_prices.value) * 100
                    ELSE 0
                END AS weekly_growth,
                CASE 
                    WHEN yop_prices.value > 0 THEN ((lp_prices.value - yop_prices.value) / yop_prices.value) * 100
                    ELSE 0
                END AS annual_growth
            FROM latest_price_dates lp
            JOIN prices lp_prices ON lp.product_id = lp_prices.product_id AND lp.latest_date = lp_prices.created_at
            JOIN week_old_price_dates wop ON lp.product_id = wop.product_id
            JOIN prices wop_prices ON wop.product_id = wop_prices.product_id AND wop.week_old_date = wop_prices.created_at
            LEFT JOIN year_old_price_dates yop ON lp.product_id = yop.product_id
            LEFT JOIN prices yop_prices ON yop.product_id = yop_prices.product_id AND yop.year_old_date = yop_prices.created_at
            WHERE wop_prices.value > 0
            ORDER BY ABS(weekly_growth) DESC
            LIMIT ?
        ", [$weekAgo, $yearAgo, $limit]);

        // Zbytek kódu zůstává stejný
        $results = [];
        foreach ($topMovers as $mover) {
            // Zaokrouhlíme hodnoty growth
            $weeklyGrowth = round($mover->weekly_growth, 1);
            $annualGrowth = $mover->year_old_value ? round($mover->annual_growth, 1) : null;

            // Uložíme trend
            $trend = Trend::updateOrCreate(
                [
                    'product_id' => $mover->product_id,
                    'type' => 'top_mover',
                    'calculated_at' => $today,
                ],
                [
                    'weekly_growth' => $weeklyGrowth,
                    'annual_growth' => $annualGrowth,
                ]
            );

            // Načteme produkt až po uložení trendu, abychom šetřili paměť
            $trend->load('product.latest_price', 'product.theme');
            $results[] = $trend;
        }

        // Uložíme do cache pro rychlé načítání
        Cache::put('top_movers', $results, Carbon::now()->addDay());

        return $results;
    }

    /**
     * Vypočítá růst ceny produktu za dané období
     *
     * @param int $productId ID produktu
     * @param int $days Počet dnů zpětně
     * @return float|null
     */
    private function calculateGrowthForProductOptimized(int $productId, int $days): ?float
    {
        $result = DB::select("
            WITH latest_price AS (
                SELECT 
                    value AS current_value
                FROM prices
                WHERE product_id = ?
                ORDER BY created_at DESC
                LIMIT 1
            ),
            old_price AS (
                SELECT 
                    value AS old_value
                FROM prices
                WHERE product_id = ? AND created_at <= ?
                ORDER BY created_at DESC
                LIMIT 1
            )
            SELECT 
                lp.current_value,
                op.old_value,
                CASE 
                    WHEN op.old_value > 0 THEN ((lp.current_value - op.old_value) / op.old_value) * 100
                    ELSE NULL
                END AS growth_percentage
            FROM latest_price lp, old_price op
        ", [$productId, $productId, Carbon::now()->subDays($days)]);

        if (empty($result) || !isset($result[0]->growth_percentage)) {
            return null;
        }

        return round($result[0]->growth_percentage, 1);
    }

    /**
     * Vypočítá procentuální změnu mezi starou a novou hodnotou
     *
     * @param float $oldValue Stará hodnota
     * @param float $newValue Nová hodnota
     * @return float
     */
    private function calculateGrowthPercentage(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) return 0;

        $difference = $newValue - $oldValue;
        return round(($difference / $oldValue) * 100, 1);
    }
}
