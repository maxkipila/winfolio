<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MinifigController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SetController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Admin Routes

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware(['auth:admins'])->group(function () {
        Route::match(['POST', 'GET'], '/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroyAdmin'])->name('auth.logout');

        Route::group(['prefix' => 'users'], function () {
            Route::match(['POST', 'GET'], '/', [UserController::class, 'index'])->name('users.index');
            Route::match(['POST', 'GET'], '/create', [UserController::class, 'create'])->name('users.create');

            Route::match(['POST', 'GET'], '/show/{user}', [UserController::class, 'show'])->name('users.show');
            Route::match(['POST', 'GET'], '/store', [UserController::class, 'store'])->name('users.store');
            Route::match(['POST', 'GET'], '/edit/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::match(['POST', 'GET'], '/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::group(['prefix' => 'awards'], function () {
            Route::match(['POST', 'GET'], '/', [AwardController::class, 'index'])->name('awards.index');
            Route::match(['POST', 'GET'], '/create', [AwardController::class, 'create'])->name('awards.create');
            Route::match(['POST', 'GET'], '/store', [AwardController::class, 'store'])->name('awards.store');
            Route::match(['POST', 'GET'], '/{award}/edit', [AwardController::class, 'edit'])->name('awards.edit');
            Route::match(['POST', 'GET'], '/{award}', [AwardController::class, 'update'])->name('awards.update');
            Route::delete('/{award}', [AwardController::class, 'destroy'])->name('awards.destroy');
        });
        Route::group(['prefix' => 'news'], function () {
            Route::match(['POST', 'GET'], '/', [NewsController::class, 'index'])->name('news.index');
            Route::match(['POST', 'GET'], '/create', [NewsController::class, 'create'])->name('news.create');
            Route::match(['POST', 'GET'], '/store', [NewsController::class, 'store'])->name('news.store');
            Route::match(['POST', 'GET'], '/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
            Route::match(['POST', 'GET'], '/{news}', [NewsController::class, 'update'])->name('news.update');
            Route::delete('/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
        });

        Route::group(['prefix' => 'news', 'as' => 'news.'], function () {
            Route::match(['POST', 'GET'], '/', [NewsController::class, 'index'])->name('index');
            Route::match(['POST', 'GET'], '/create', [NewsController::class, 'create'])->name('create');
            Route::match(['POST', 'GET'], '/store', [NewsController::class, 'store'])->name('store');
            Route::match(['POST', 'GET'], '/{news}/edit', [NewsController::class, 'edit'])->name('edit');
            Route::match(['POST', 'GET'], '/{news}', [NewsController::class, 'update'])->name('update');
            Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
        });
        Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
            Route::match(['POST', 'GET'], '/sety', [ProductController::class, 'indexSet'])->name('index.set');
            Route::match(['POST', 'GET'], '/minifig', [ProductController::class, 'indexMinifig'])->name('index.minifig');
            Route::match(['POST', 'GET'], '/set/{product}', [ProductController::class, 'showSet'])
                ->name('show.set');
            Route::match(['POST', 'GET'], '/minifig/{product}', [ProductController::class, 'showMinifig'])
                ->name('show.minifig');
            Route::match(['POST', 'GET'], '/create', [ProductController::class, 'create'])->name('create');
            /* Route::post('/', [ProductController::class, 'store'])->name('store'); */
        });
        // Route::group(['prefix' => 'sets', 'as' => 'sets.'], function () {
        //     Route::match(['POST', 'GET'], '/', [SetController::class, 'index'])->name('index');
        //     Route::match(['POST', 'GET'], '/create', [SetController::class, 'create'])->name('create');
        //     Route::match(['POST', 'GET'], '/store', [SetController::class, 'store'])->name('store');
        //     Route::get('/detail/{legoSet}', [SetController::class, 'show'])->name('show');       // Detail
        //     Route::match(['POST', 'GET'], '/edit/{set}', [SetController::class, 'edit'])->name('edit');
        //     /* Route::match(['POST', 'GET'], '/{set}', [SetController::class, 'update'])->name('update');*/
        //     /*Route::delete('/{set}', [SetController::class, 'destroy'])->name('sets.destroy'); */
        // });
        Route::group(['prefix' => 'minifigs', 'as' => 'minifigs.'], function () {
            Route::match(['POST', 'GET'], '/', [MinifigController::class, 'index'])->name('index');
            Route::match(['POST', 'GET'], '/create', [MinifigController::class, 'create'])->name('create');
            Route::match(['POST', 'GET'], '/store', [MinifigController::class, 'store'])->name('store');
            Route::match(['POST', 'GET'], '/{minifig}/edit', [MinifigController::class, 'edit'])->name('edit');
            Route::match(['POST', 'GET'], '/{minifig}', [MinifigController::class, 'update'])->name('update');
            Route::match(['POST', 'GET'], '/{minifig}/show', [MinifigController::class, 'show'])->name('show');
            Route::delete('/{minifig}', [MinifigController::class, 'destroy'])->name('destroy');
        });

        /*   Route::post('/import', [ImportController::class, 'import'])->name('import');
        Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum'); */
    })->middleware('auth:sanctum');

    /*  Route::post('/users-import', [ImportController::class, 'import']);
    Route::get('/users-export', [ImportController::class, 'export']); */
});
