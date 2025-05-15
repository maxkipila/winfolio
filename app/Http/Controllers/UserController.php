<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Favourite;
use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Http\Resources\_Trend;
use App\Http\Resources\_User;
use App\Models\Favourite;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\ProductUser;
use App\Models\Set;
use App\Models\Theme;
use App\Models\Trend;
use App\Models\User;
use App\Services\TrendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function welcome(Request $request)
    {
        $star_wars_theme = _Theme::init(Theme::where('name', 'Star Wars')->first());

        $products = _Product::collection(Product::where('theme_id', $star_wars_theme?->id)->inRandomOrder()->take(4)->get());

        // $latestDate = Trend::where('type', 'trending')->max('calculated_at');
        // $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
        //     ->where('type', 'trending')
        //     ->where('calculated_at', $latestDate)
        //     ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());

        // if ($trendingQuery->count() === 0) {
        //     $this->trendService->calculateTrendingProducts(8, 30);
        //     $latestDate = Trend::where('type', 'trending')->max('calculated_at');

        //     $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
        //         ->where('type', 'trending')
        //         ->where('calculated_at', $latestDate)
        //         ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());
        // }

        // $trending_products = _Trend::collection(
        //     $trendingQuery->paginate($request->paginate ?? 4)
        // );

        // $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

        // $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
        //     ->where('type', 'top_mover')
        //     ->where('calculated_at', $latestDateMovers)
        //     ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());

        // if ($topMoversQuery->count() === 0) {
        //     $this->trendService->calculateTopMovers();
        //     $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

        //     $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
        //         ->where('type', 'top_mover')
        //         ->where('calculated_at', $latestDateMovers)
        //         ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());
        // }

        // $top_movers = _Trend::collection(
        //     $topMoversQuery->paginate($request->paginate ?? 4)
        // );

        return Inertia::render('Welcome', compact('products'));
    }
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

    public function catalog(Request $request, TrendService $trendService)
    {
        $query = $request->search;
        $column = 'name';

        $latestDate = Trend::where('type', 'trending')->max('calculated_at');
        $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'trending')
            ->where('calculated_at', $latestDate)
            ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());

        if ($trendingQuery->count() === 0) {
            $trendService->calculateTrendingProducts(8, 30);
            $latestDate = Trend::where('type', 'trending')->max('calculated_at');

            $trendingQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
                ->where('type', 'trending')
                ->where('calculated_at', $latestDate)
                ->orderByRelation($request->sort ?? ['favorites_count' => 'desc'], ['id', 'asc'], App::getLocale());
        }

        $trending_products = _Trend::collection(
            $trendingQuery->paginate($request->paginate ?? 4)
        );

        $products = _Product::collection(
            Product::with('media')
                ->when($request->type, fn($k) => $k->where('product_type', $request->type))
                ->when(
                    $request->parent_theme || $request->theme_children,
                    fn($q) => $q->where('theme_id', [array_merge($request->theme_children ?? [], [$request->parent_theme])])
                )
                ->where($column, 'LIKE', '%' . $query . '%')
                ->orderByRaw("
            CASE WHEN $column LIKE '" . e($query) . "' THEN 1
                WHEN $column LIKE '" . e($query) . "%' THEN 2
                WHEN $column LIKE '%" . e($query) . "%' THEN 3
                WHEN $column LIKE '%" . e($query) . "' THEN 4
                ELSE 5
            END")
                ->latest()
                ->paginate($request->paginate ?? 10)
        );

        $themes = _Theme::collection(
            Theme::with('children')
                ->where('parent_id', NULL)
                ->paginate($request->paginate ?? 10)
        );

        return Inertia::render('catalog', compact('products', 'themes','trending_products'));
        /* $query = $request->search;
        $column = 'name';
        $products = _Product::collection(Product::when($request->type, fn($k) => $k->where('product_type', $request->type))->when($request->parent_theme || $request->theme_children, fn($q) => $q->where('theme_id', [array_merge($request->theme_children ?? [], [$request->parent_theme])]))->where($column, 'LIKE', '%' . $query . '%')
            ->orderByRaw("
            CASE WHEN $column LIKE '" . e($query) . "' THEN 1
                WHEN $column LIKE '" . e($query) . "%' THEN 2
                WHEN $column LIKE '%" . e($query) . "%' THEN 3
                WHEN $column LIKE '%" . e($query) . "' THEN 4
                ELSE 5
            END")->latest()->paginate($request->paginate ?? 10));

        // $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));



        $themes = _Theme::collection(Theme::with('children')->where('parent_id', NULL)->paginate($request->paginate ?? 100));
        // dd($themes);
        return Inertia::render('catalog', compact('products', 'themes')); */
    }

    public function chest(Request $request, TrendService $trendService)
    {
        $user = Auth::user();

        $range = $request->input('range', 'year');

        $fromDate = match ($range) {
            'week' => now()->subDays(7),
            'month' => now()->subMonth(),
            default => now()->subYear()
        };

        $productIds = $user->products()->pluck('product_id')->toArray();

        if (empty($productIds)) {
            return Inertia::render('chest', [
                'products' => [],
                'user_products' => [],
                'portfolioStats' => null,
                'portfolioProducts' => [],
                'portfolioValue' => 0,
                'range' => $range,
                'fromDate' => $fromDate->toDateString(),
                'portfolioHistory' => []
            ]);
        }

        $user->load('products.latest_price');

        // Výpočet aktuální hodnoty portfolia
        $portfolioValue = $user->products->sum(function ($product) {
            return $product->latest_price ? $product->latest_price->value : 0;
        });

        // Přidáme debug informace
        Log::debug('Portfolio calculation', [
            'user_id' => $user->id,
            'range' => $range,
            'fromDate' => $fromDate->toDateString(),
            'productIds' => $productIds,
            'portfolioValue' => $portfolioValue
        ]);

        // Výpočet růstu portfolia
        $portfolioStats = $trendService->calculateGrowth($productIds, $fromDate);

        if (isset($portfolioStats['total'])) {
            $portfolioStats['total']['current_value'] = $portfolioValue;
        }


        // Získání historie pro graf
        $history = match ($range) {
            'week' => $trendService->getPortfolioHistory($productIds, 'day', 7),
            'month' => $trendService->getPortfolioHistory($productIds, 'day', 30),
            default => $trendService->getPortfolioHistory($productIds, 'month', 12)
        };


        $user_products = _Product::collection($user->products);
        $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));

        return Inertia::render('chest', [
            'products' => $products,
            'user_products' => $user_products,
            'portfolioStats' => $portfolioStats['total'] ?? null,
            'portfolioProducts' => $portfolioStats['products'] ?? [],
            'portfolioValue' => $portfolioValue,
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

    public function remove_product_from_user(Product $product)
    {
        /*  dd($product); */
        Auth::user()->products()->detach($product->id);
        return back();
    }

    /*   public function remove_product_from_user(Request $request, Product $product)
    {
        $user = Auth::user();

        $user->products()->detach($product->id);
        return back();
    } */

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
