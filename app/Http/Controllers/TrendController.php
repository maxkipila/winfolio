<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Product;
use App\Models\Product;
use App\Services\TrendService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class TrendController extends Controller
{
    protected $trendService;

    public function __construct(TrendService $trendService)
    {
        $this->trendService = $trendService;
    }

    /**
     * Získá historii cen pro produkt
     */
    public function getPriceHistory(Request $request, Product $product)
    {
        $condition = $request->input('condition');
        $months = $request->input('months', 24);

        $priceHistory = $this->trendService->getPriceHistoryWithMedian(
            $product->id,
            $months,
            $condition
        );

        return response()->json($priceHistory);
    }

    /**
     * Získá statistiky cen pro produkt
     */
    public function getPriceStats(Request $request, Product $product)
    {
        $condition = $request->input('condition');

        $priceStats = $this->trendService->getPriceStatistics(
            $product->id,
            $condition
        );

        return response()->json($priceStats);
    }

    /**
     * Vypočítá růst/pokles ceny mezi dvěma daty
     */
    public function calculateGrowth(Request $request, Product $product)
    {
        $request->validate([
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'condition' => 'nullable|string',
        ]);

        $growth = $this->trendService->calculateGrowth(
            $product->id,
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('condition')
        );

        return response()->json($growth);
    }

    /**
     * Získá produkty s největšími cenovými změnami
     */
    public function getTopMovers(Request $request)
    {
        $limit = $request->input('limit', 8);

        $topMovers = Cache::remember('top_movers', Carbon::now()->addHours(12), function () use ($limit) {
            return $this->trendService->calculateTopMovers($limit);
        });

        $topMoversData = collect($topMovers)->map(function ($trend) {
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
     * Získá trendující produkty
     */
    public function getTrendingProducts(Request $request)
    {
        $limit = $request->input('limit', 8);
        $days = $request->input('days', 7);

        $trendingProducts = Cache::remember('trending_products', Carbon::now()->addHours(12), function () use ($limit, $days) {
            return $this->trendService->calculateTrendingProducts($limit, $days);
        });

        $trendingData = collect($trendingProducts)->map(function ($trend) {
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
}
