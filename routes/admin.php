<?php

use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProductErrorController;
use Illuminate\Support\Facades\Route;

//Admin Routes

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware(['auth:admins'])->group(function () {

        Route::get('/', function () {
            return redirect(route('admin.dashboard'));
        })->name('index');

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

        Route::group(['prefix' => 'errors'], function () {
            Route::match(['POST', 'GET'], '/', [ProductErrorController::class, 'index'])->name('errors.index');
            Route::match(['POST', 'GET'], '/create', [ProductErrorController::class, 'create'])->name('errors.create');

            Route::match(['POST', 'GET'], '/show/{productError}', [ProductErrorController::class, 'show'])->name('errors.show');
            Route::match(['POST', 'GET'], '/store', [ProductErrorController::class, 'store'])->name('errors.store');
            Route::match(['POST', 'GET'], '/edit/{productError}', [ProductErrorController::class, 'edit'])->name('errors.edit');
            Route::match(['POST', 'GET'], '/{productError}', [ProductErrorController::class, 'update'])->name('errors.update');
            Route::delete('/{productError}', [ProductErrorController::class, 'destroy'])->name('errors.destroy');
        });

        Route::group(['prefix' => 'awards', 'as' => 'awards.'], function () {
            Route::match(['POST', 'GET'], '/', [AwardController::class, 'index'])->name('index');
            Route::match(['POST', 'GET'], '/create', [AwardController::class, 'create'])->name('create');
            Route::match(['POST', 'GET'], '/store', [AwardController::class, 'store'])->name('store');
            Route::match(['POST', 'GET'], '/edit/{award}', [AwardController::class, 'edit'])->name('edit');
            Route::match(['POST', 'GET'], '/{award}', [AwardController::class, 'update'])->name('update');
            Route::delete('/{award}', [AwardController::class, 'destroy'])->name('destroy');
            Route::delete(
                '/awards/{award}/conditions/{condition}/remove/{field}',
                [AwardController::class, 'removeField']
            )->name('removeField');
        });

        Route::group(['prefix' => 'news', 'as' => 'news.'], function () {
            Route::match(['POST', 'GET'], '/', [NewsController::class, 'index'])->name('index');
            Route::match(['POST', 'GET'], '/create', [NewsController::class, 'create'])->name('create');
            Route::match(['POST', 'GET'], '/store', [NewsController::class, 'store'])->name('store');
            Route::match(['POST', 'GET'], '/edit/{news}', [NewsController::class, 'edit'])->name('edit');
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



        /*   Route::post('/import', [ImportController::class, 'import'])->name('import');
        Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum'); */
    });

    /*  Route::post('/users-import', [ImportController::class, 'import']);
    Route::get('/users-export', [ImportController::class, 'export']); */
});
