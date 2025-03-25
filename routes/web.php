<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Resources\_Set;
use App\Models\Set;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth:web')->group(function () {
    Route::match(['POST', 'GET'], '/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/blog-layout', function () {
        return Inertia::render('blog');
    })->name('blog-layout');

    Route::match(['GET', 'POST'], '/chest', function () {
        $sets = _Set::collection(Set::latest()->paginate($request->paginate ?? 10));
        return Inertia::render('chest', compact('sets'));
    })->name('chest');

    Route::match(['GET', 'POST'], '/profile', [UserController::class, 'profile'])->name('profile.index');
    Route::match(['GET', 'POST'], '/catalog', function () {
        $sets = _Set::collection(Set::latest()->paginate($request->paginate ?? 10));
        return Inertia::render('catalog', compact('sets'));
    })->name('catalog');

    Route::match(['GET', 'POST'], '/product/{set}', function (Request $request, Set $set) {
        $set = _Set::init($set);
        // dd($set);
        return Inertia::render('product', compact('set'));
    })->name('product.detail');

    /*   Route::match(['GET', 'POST'], '/profile', [UserController::class, 'profile'])->name('profile.index'); */

    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/admin.php';
require __DIR__ . '/auth.php';
