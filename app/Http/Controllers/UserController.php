<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Favourite;
use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Trend;
use App\Http\Resources\_User;
use App\Models\Favourite;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use App\Models\Trend;
use App\Services\TrendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    protected $trendService;

    public function __construct(TrendService $trendService)
    {
        $this->trendService = $trendService;
    }

    /*  public function dashboard(Request $request)
    {

        $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));
        return Inertia::render('Dashboard', compact('products'));
    } */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        $products = _Product::collection(
            Product::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
                ->paginate($request->paginate ?? 4)
        );

        $latestDate = Trend::where('type', 'trending')->max('calculated_at');

        $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'trending')
            ->where('calculated_at', $latestDate)
            ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());

        if ($trendingQuery->count() === 0) {
            $this->trendService->calculateTrendingProducts(8, 30);
            $latestDate = Trend::where('type', 'trending')->max('calculated_at');

            $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
                ->where('type', 'trending')
                ->where('calculated_at', $latestDate)
                ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());
        }

        $trending_products = _Trend::collection(
            $trendingQuery->paginate($request->paginate ?? 4)
        );

        $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

        $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'top_mover')
            ->where('calculated_at', $latestDateMovers)
            ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());

        if ($topMoversQuery->count() === 0) {
            $this->trendService->calculateTopMovers();
            $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

            $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
                ->where('type', 'top_mover')
                ->where('calculated_at', $latestDateMovers)
                ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());
        }

        $top_movers = _Trend::collection(
            $topMoversQuery->paginate($request->paginate ?? 4)
        );

        // Zbytek funkce zůstává stejný...
        $portfolioValue = $this->dashboardPortfolioValue();

        $portfolioStats = null;
        if ($user) {
            $productIds = $user->products()->pluck('product_id')->toArray();

            if (!empty($productIds)) {
                $portfolioStats = $this->trendService->calculateGrowth(
                    $productIds,
                    now()->subYear()->toDateString()
                )['total'];
            }
        }

        return Inertia::render('Dashboard', [
            'products' => $products,
            'trendingProducts' => $trending_products,
            'topMovers' => $top_movers,
            'portfolioValue' => $portfolioValue,
            'portfolioStats' => $portfolioStats,
        ]);
    }

    public function chest(Request $request, TrendService $trendService)
    {
        $user = Auth::user();

        $range = $request->input('range', 'week');
        // dd($request, $range);
        $fromDate = match ($range) {
            'week' => now()->subDays(7),
            'month' => now()->subMonth(),
            default => now()->subYear()
        };

        $productIds = $user->products()->pluck('product_id')->toArray();
        $portfolioStats = $trendService->calculateGrowth($productIds, $fromDate);


        $user->load('products.prices');
        /*  foreach ($user->products as $product) {
            $product->annual_growth = $this->trendService->getProductGrowth($product->id, 365);
            $product->weekly_growth = $this->trendService->getProductGrowth($product->id, 7);
            $product->monthly_growth = $this->trendService->getProductGrowth($product->id, 30);
        } */
        if ($request->range == 'week') {
            $history = $trendService->getPortfolioHistory($productIds, 'day', 7);
        } else if ($request->range == 'month') {
            $history = $trendService->getPortfolioHistory($productIds, 'week', 4);
        } else {
            $history = $trendService->getPortfolioHistory($productIds, 'month', 12);
        }


        $this->trendService->calculateTrendingProducts();

        $user_products = _Product::collection($user->products);
        $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));

        return Inertia::render('chest', [
            'products' => $products,
            'user_products' => $user_products,
            'portfolioStats' => $portfolioStats['total'],
            'portfolioProducts' => $portfolioStats['products'],
            'range' => $range,
            'fromDate' => $fromDate->toDateString(),
            'portfolioHistory' => $history
        ]);
    }
    public function profile(Request $request)
    {

        return Inertia::render('Profile/index');
    }

    public function get_user(Request $request)
    {

        return _User::init($request->user()->load('products'));
    }

    public function add_product_to_user(Request $request, Product $product)
    {
        $user = Auth::user();
        $request->validate([
            'day' => 'required',
            'month' => 'required',
            'year' => 'required',
            'price' => 'required',
            'currency' => 'required',
            'status' => 'required',

        ]);

        $user->products()->syncWithoutDetaching([$product->id => ['purchase_year' => $request->year, 'purchase_month' => $request->month, 'purchase_day' => $request->day, 'purchase_price' => $request->price, 'currency' => $request->currency, 'condition' => $request->status]]);
        // $user->products()->sync([$product->id]);
        return back();
    }

    public function remove_product_from_user(Request $request, Product $product)
    {
        $user = Auth::user();

        $user->products()->detach($product->id);
        return back();
    }

    public function toggleFavourite(Request $request, $type, $favouritable)
    {


        $favourite = Favourite::firstOrCreate([
            'favourite_id' => $favouritable,
            'favourite_type' => urldecode($type),
            'user_id' => Auth::user()->id
        ]);

        if (!$favourite->wasRecentlyCreated) {
            $favourite->delete();
        }

        return $request->wantsJson()
            ? response()->json(['favourite' => _Favourite::init($favourite->fresh())])
            : back();
    }


    private function dashboardPortfolioValue()
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
