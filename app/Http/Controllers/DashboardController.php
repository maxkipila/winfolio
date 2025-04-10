<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Product;
use App\Models\Trend;
use App\Services\TrendService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $trendService;

    public function __construct(TrendService $trendService)
    {
        $this->trendService = $trendService;
    }

    public function index()
    {
        $trendingProducts = Cache::remember('trending_products', Carbon::now()->addHours(12), function () {
            $trends = Trend::with(['product.latest_price', 'product.theme'])
                ->where('type', 'trending')
                ->where('calculated_at', Carbon::today())
                ->orderBy('favorites_count', 'desc')
                ->limit(8)
                ->get();


            if ($trends->isEmpty()) {
                return $this->trendService->calculateTrendingProducts();
            }

            return $trends;
        });

        $topMovers = Cache::remember('top_movers', Carbon::now()->addHours(12), function () {
            $movers = Trend::with(['product.latest_price', 'product.theme'])
                ->where('type', 'top_mover')
                ->where('calculated_at', Carbon::today())
                ->orderByRaw('ABS(weekly_growth) DESC')
                ->limit(8)
                ->get();

            // Pokud nemáme data za dnešek, spustíme výpočet
            if ($movers->isEmpty()) {
                return $this->trendService->calculateTopMovers();
            }

            return $movers;
        });

        // Pomocí Resource transformujeme data do správného formátu
        $trendingData = $trendingProducts->map(function ($trend) {
            return [
                'product' => new _Product($trend->product),
                'weekly_growth' => $trend->weekly_growth,
                'annual_growth' => $trend->annual_growth,
            ];
        });

        $topMoversData = $topMovers->map(function ($trend) {
            return [
                'product' => new _Product($trend->product),
                'weekly_growth' => $trend->weekly_growth,
                'annual_growth' => $trend->annual_growth,
            ];
        });

        // Získáme celkovou hodnotu portfolia (pro ukázku)
        $portfolioValue = $this->calculatePortfolioValue();

        // Vracíme data přes Inertia.js
        return Inertia::render('Dashboard', [
            'trendingProducts' => $trendingData,
            'topMovers' => $topMoversData,
            'portfolioValue' => $portfolioValue,
        ]);
    }

    /**
     * API metoda pro získání trendujících produktů
     */
    public function getTrendingProducts()
    {
        $trendingProducts = Cache::remember('trending_products', Carbon::now()->addHours(12), function () {
            $trends = Trend::with(['product.latest_price', 'product.theme'])
                ->where('type', 'trending')
                ->where('calculated_at', Carbon::today())
                ->orderBy('favorites_count', 'desc')
                ->limit(8)
                ->get();

            if ($trends->isEmpty()) {
                return $this->trendService->calculateTrendingProducts();
            }

            return $trends;
        });

        $trendingData = $trendingProducts->map(function ($trend) {
            return [
                'product' => new _Product($trend->product),
                'weekly_growth' => $trend->weekly_growth,
                'annual_growth' => $trend->annual_growth,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $trendingData,
        ]);
    }

    /**
     * API metoda pro získání produktů s největšími cenovými změnami
     */
    public function getTopMovers()
    {
        $topMovers = Cache::remember('top_movers', Carbon::now()->addHours(12), function () {
            $movers = Trend::with(['product.latest_price', 'product.theme'])
                ->where('type', 'top_mover')
                ->where('calculated_at', Carbon::today())
                ->orderByRaw('ABS(weekly_growth) DESC')
                ->limit(8)
                ->get();

            if ($movers->isEmpty()) {
                return $this->trendService->calculateTopMovers();
            }

            return $movers;
        });

        $topMoversData = $topMovers->map(function ($trend) {
            return [
                'product' => new _Product($trend->product),
                'weekly_growth' => $trend->weekly_growth,
                'annual_growth' => $trend->annual_growth,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $topMoversData,
        ]);
    }

    /**
     * Vypočítá celkovou hodnotu portfolia pro přihlášeného uživatele
     */
    private function calculatePortfolioValue()
    {

        if (!auth()->check()) return 0;

        $portfolioValue = auth()->user()->products()
            ->with('latest_price')
            ->get()
            ->sum(function ($product) {

                return $product->latest_price ? $product->latest_price->value : 0;
            });

        return $portfolioValue;
    }
}
