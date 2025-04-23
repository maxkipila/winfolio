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
        $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));

        // $locale = App::currentLocale();
        // dd($locale);
        // App::setLocale('cs');
        

        $trends = Trend::with(['product.latest_price', 'product.theme'])
            ->where('type', 'trending')
            ->where('calculated_at', Carbon::today())
            ->orderBy('favorites_count', 'desc')
            ->limit(8)
            ->get();

        if ($trends->isEmpty()) {
            $this->trendService->calculateTrendingProducts();
        }

        $movers = Trend::with(['product.latest_price', 'product.theme'])
            ->where('type', 'top_mover')
            ->where('calculated_at', Carbon::today())
            ->orderByRaw('ABS(weekly_growth) DESC')
            ->limit(8)
            ->get();

        if ($movers->isEmpty()) {
            $this->trendService->calculateTopMovers();
        }

        // $trendingProducts = Cache::remember('trending_products', Carbon::now()->addHours(12), function () {
        //     $trends = Trend::with(['product.latest_price', 'product.theme'])
        //         ->where('type', 'trending')
        //         ->where('calculated_at', Carbon::today())
        //         ->orderBy('favorites_count', 'desc')
        //         ->limit(8)
        //         ->get();

        //     if ($trends->isEmpty()) {
        //         return $this->trendService->calculateTrendingProducts();
        //     }

        //     return $trends;
        // });

        // $topMovers = Cache::remember('top_movers', Carbon::now()->addHours(12), function () {
        //     $movers = Trend::with(['product.latest_price', 'product.theme'])
        //         ->where('type', 'top_mover')
        //         ->where('calculated_at', Carbon::today())
        //         ->orderByRaw('ABS(weekly_growth) DESC')
        //         ->limit(8)
        //         ->get();

        //     if ($movers->isEmpty()) {
        //         return $this->trendService->calculateTopMovers();
        //     }

        //     return $movers;
        // });

        // $trendingData = collect($trendingProducts)->map(function ($trend) {
        //     return [
        //         'product' => new _Product($trend->product),
        //         'weekly_growth' => $trend->weekly_growth,
        //         'annual_growth' => $trend->annual_growth,
        //     ];
        // });

        // $topMoversData = collect($topMovers)->map(function ($trend) {
        //     return [
        //         'product' => new _Product($trend->product),
        //         'weekly_growth' => $trend->weekly_growth,
        //         'annual_growth' => $trend->annual_growth,
        //     ];
        // });

        $portfolioValue = $this->dashboardPortfolioValue();

        // Načtení rekordů uživatele
        /*   $records = $user->records()->with('product')->get()->keyBy('record_type');


        $formattedRecords = [
            'highest_portfolio' => $records->get('highest_portfolio_value') ? $records->get('highest_portfolio_value')->value : 0,
            'most_items' => $records->get('most_items') ? $records->get('most_items')->value : 0,
            'best_purchase' => $records->get('best_purchase') ? [
                'value' => $records->get('best_purchase')->value,
                'product' => new _Product($records->get('best_purchase')->product)
            ] : null,
            'worst_purchase' => $records->get('worst_purchase') ? [
                'value' => $records->get('worst_purchase')->value,
                'product' => new _Product($records->get('worst_purchase')->product)
            ] : null,
        ]; */

        // Načtení odznaků uživatele
        /*    $userAwards = $user->userAwards()->with('award')->get();
        $userAwardsData = collect($userAwards)->map(function ($userAward) {
            return [
                'id' => $userAward->award->id,
                'name' => $userAward->award->name,
                'description' => $userAward->award->description,
                'category' => $userAward->award->category,
                'icon' => $userAward->award->icon,
                'earned_at' => $userAward->earned_at,
            ];
        });
 */

        $trending_products = _Trend::collection(Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'trending')
            ->orderBy('favorites_count', 'desc')
            ->paginate($request->paginate ?? 10));

        $top_movers = _Trend::collection(Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'top_mover')
            ->orderByRaw('ABS(weekly_growth) DESC')
            ->paginate($request->paginate ?? 10));


        return Inertia::render('Dashboard', [
            'products' => $products,
            'trendingProducts' => $trending_products,
            'topMovers' => $top_movers,
            'portfolioValue' => $portfolioValue,
            /*   'records' => $formattedRecords,
            'awards' => $userAwardsData, */
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
