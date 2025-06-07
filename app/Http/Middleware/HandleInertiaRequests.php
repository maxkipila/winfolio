<?php

namespace App\Http\Middleware;

use App\Http\Resources\_Admin;
use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Http\Resources\_Trend;
use App\Http\Resources\_User;
use App\Models\Admin;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use App\Models\Trend;
use App\Models\User;
use App\Services\TrendService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */



    protected $trendService;
    public function __construct(TrendService $trendService)
    {
        $this->trendService = $trendService;
    }

    public function share(Request $request): array
    {


        $users = fn(): AnonymousResourceCollection => _User::collection(User::search(['first_name', 'last_name', DB::raw("(CONCAT(first_name,' ', last_name))"), 'email', 'id'], $request->q ?? '', 6, App::getLocale())->get());
        $themes = fn(): AnonymousResourceCollection => _Theme::collection(Theme::search(['id', 'name'], $request->q ?? '', 6, App::getLocale())->get());
        $products = fn(): AnonymousResourceCollection => _Product::collection(Product::search(['id', 'name', 'product_num'], $request->q ?? '', 6, App::getLocale())->get());


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



        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn() => ($request->user() instanceof Admin ? _Admin::make($request->user()) : _User::make($request->user()?->load('products'))),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            "search_all" => Inertia::lazy(
                fn() => collect()
                    ->merge($users())
                    ->merge($products())
                    ->merge($themes())

            ),
            "search_products" => Inertia::lazy(
                fn() => collect()
                    ->merge($products())
            ),
            "search_themes" => Inertia::lazy(
                fn() => collect()
                    ->merge($themes())
            ),
            'notifications' => fn() => $request->user()
                ? $request->user()->unreadNotifications
                : [],
            'awardNotifications' => fn() => $request->user() && !Gate::allows('admin')
                ? $request->user()->awards()->wherePivot('notified', false)->get()
                : [],
            'flash' => Session::get('flash'),
            'locale' => App::getLocale(),
            'trendingProducts' => $trending_products,
            // 'trendingProducts' => $trendingData,
            /*  'searchAllUsers' => Inertia::lazy($searchAllUser),
            'searchAllSets' => Inertia::lazy($this->searchByModel(Set::class, 'name', _Set::class, $request->q ?? "")),
            'searchAllMinifigs' => Inertia::lazy($this->searchByModel(Minifig::class, 'name', _Minifig::class, $request->q ?? "")), */

        ];
    }
}
