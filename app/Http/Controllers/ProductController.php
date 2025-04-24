<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Product;
use App\Models\Product;
use App\Services\TrendService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected $trendService;

    public function __construct(TrendService $trendService)
    {
        $this->trendService = $trendService;
    }

    public function show(Product $product, Request $request)
    {
        $condition = $request->input('condition');
        $months = $request->input('months', 12);

        // Získání historických dat pro graf
        $priceHistory = $this->trendService->getPriceHistoryWithMedian(
            $product->id,
            $months,
            $condition
        );

        // Získání statistik
        $stats = $this->trendService->getPriceStatistics(
            $product->id,
            $condition
        );

        // Výpočet růstu za různá období
        $weeklyGrowth = $this->trendService->getProductGrowth($product->id, 7);
        $monthlyGrowth = $this->trendService->getProductGrowth($product->id, 30);
        $yearlyGrowth = $this->trendService->getProductGrowth($product->id, 365);

        // Načtení produktu a souvisejících dat
        /*  $product->load(['theme', 'minifigs']); */
        $product = _Product::init($product->load(['reviews', 'prices', 'price', 'theme', 'minifigs', 'sets.theme']));
        $similar_products = _Product::collection(Product::where('theme_id', $product->theme->id ?? NULL)->inRandomOrder()->take(4)->get());

        return Inertia::render('product', [
            'product' => $product,
            'similiar_products' => $similar_products,
            'priceHistory' => $priceHistory,
            'stats' => $stats,
            'growth' => [
                'weekly' => $weeklyGrowth,
                'monthly' => $monthlyGrowth,
                'yearly' => $yearlyGrowth
            ]
        ]);
    }


    /**
     * API endpoint pro získání dat o ceně
     */
    public function getPriceData(Request $request, Product $product)
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
     * API endpoint pro získání statistik o ceně
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
     * API endpoint pro výpočet růstu/poklesu ceny mezi dvěma daty
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
}
