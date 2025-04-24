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
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::middleware('guest:web')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');


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
    Route::match(['GET', 'POST'], '/catalog', function (Request $request) {
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



        $themes = _Theme::collection(Theme::with('children')->where('parent_id', NULL)->paginate($request->paginate ?? 10));
        // dd($themes);
        return Inertia::render('catalog', compact('products', 'themes'));
    })->name('catalog');

    Route::match(['GET', 'POST'], '/awards', [AwardController::class, 'index'])->name('awards');
    Route::post('/awards/{award}/claim', [AwardController::class, 'claimBadge'])->name('awards.claim');

    Route::match(['GET', 'POST'], '/product/{product}', [ProductController::class, 'show'])->name('product.detail');

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


require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
