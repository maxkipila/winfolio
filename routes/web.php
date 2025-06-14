<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\UserController;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Http\Resources\_Trend;
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use App\Models\Trend;
use App\Services\TrendService;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::middleware('guest:web')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});


Route::match(['POST', 'GET'], '/', function (Request $request, TrendService $trendService) {
    $star_wars_theme = _Theme::init(Theme::where('name', 'Star Wars')->first());

    $products = _Product::collection(Product::where('theme_id', $star_wars_theme->id)->inRandomOrder()->take(4)->get());

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

    $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

    $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
        ->where('type', 'top_mover')
        ->where('calculated_at', $latestDateMovers)
        ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());

    if ($topMoversQuery->count() === 0) {
        $trendService->calculateTopMovers();
        $latestDateMovers = Trend::where('type', 'top_mover')->max('calculated_at');

        $topMoversQuery = Trend::with(['product.latest_price', 'product.theme', 'product'])
            ->where('type', 'top_mover')
            ->where('calculated_at', $latestDateMovers)
            ->orderByRelation($request->sort ?? ['weekly_growth' => 'desc'], ['id', 'asc'], App::getLocale());
    }

    $top_movers = _Trend::collection(
        $topMoversQuery->paginate($request->paginate ?? 4)
    );


    return Inertia::render('Welcome', compact('products', 'trending_products', 'top_movers'));
})->name('welcome');

Route::match(['POST', 'GET'], '/', [UserController::class, 'welcome'])->name('welcome');


Route::middleware('auth:web')->group(function () {
    Route::match(['POST', 'GET'], '/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    /*  Route::get('/dashboard/data/calc', [DashboardController::class, 'index'])->name('dashboard.data'); */
    Route::get('/get-user', [UserController::class, 'get_user'])->name('get_user');
    Route::match(['POST', 'GET'], '/add_product_to_user/{product}', [UserController::class, 'add_product_to_user'])->name('add_product_to_user');
    Route::match(['POST', 'GET'], '/remove_product_from_user/{product}', [UserController::class, 'remove_product_from_user'])->name('remove_product_from_user');
 
    Route::get('/blog-layout', function () {
        return Inertia::render('blog');
    })->name('blog-layout');
    Route::post('/post-review', [ReviewController::class, 'submit_review'])->name('submit_review');
    Route::post('/favourite/{type}/{favouritable}', [UserController::class, 'toggleFavourite'])->name('favourites.toggle');

    Route::match(['GET', 'POST'], '/chest', [UserController::class, 'chest'])->name('chest');

    Route::match(['GET', 'POST'], '/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::match(['GET', 'POST'], '/notifications', [UserController::class, 'notifications'])->name('profile.notifications');


    Route::match(['GET', 'POST'], '/catalog', [UserController::class, 'catalog'])->name('catalog');

    Route::match(['GET', 'POST'], '/awards', [AwardController::class, 'index'])->name('awards');

    // Route::delete('/remove_product_from_user/{product}', [UserController::class, 'remove_product_from_user'])
    //     ->name('remove_product_from_user');

    Route::post('/awards/{award}/claim', [AwardController::class, 'claimBadge'])->name('awards.claim');

    Route::match(['GET', 'POST'], '/product/{product}', [ProductController::class, 'show'])->name('product.detail');
    /*  Route::match(['GET', 'POST'], '/catalog', function (Request $request) {
        // dd($request->parent_theme, $request->theme_children);
        $query = $request->search;
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
        return Inertia::render('catalog', compact('products', 'themes'));
    })->name('catalog'); */
    // V souboru routes/web.php



    /*   Route::match(['GET', 'POST'], '/product/{product}', function (Request $request, Product $product) {
        $product = _Product::init($product->load(['reviews', 'prices', 'price', 'theme', 'minifigs', 'sets.theme']));

        $similar_products = _Product::collection(Product::where('theme_id', $product->theme->id ?? NULL)->inRandomOrder()->take(4)->get());

        // dd($set);
        return Inertia::render('product', compact('product', 'similar_products'));
    })->name('product.detail'); */



    /*   Route::match(['GET', 'POST'], '/profile', [UserController::class, 'profile'])->name('profile.index'); */

    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});

Route::post('/users/{user}/update-records', [RecordController::class, 'updateRecords'])
    ->name('users.update-records');

Route::get('/products/{product}/price-history', [ProductController::class, 'getPriceHistory']);
Route::get('/products/{product}/price-statistics', [ProductController::class, 'getPriceStatistics']);


Route::get('/email-preview/{email}', function (Request $request, $email) {

    return view('emails.' . $email, ["data" => $request->all(), 'user' => User::find(1)]);
});

Route::get('/email-testing/{email}', function (Request $request, $email) {
    $user = User::find(1);
    return view('emails.' . $email, ["data" => [$request->all(), 'link' => 'in_three_days', 'email' => 'svobodnik@paradigma.so', 'code' => '123456'], 'user' => $user]);
});


require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
