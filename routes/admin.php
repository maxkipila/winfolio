<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\MinifigController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SetController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Admin Routes

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', [AuthenticatedSessionController::class, 'createAdmin'])->name('admin.auth.login');
        Route::post('/', [AuthenticatedSessionController::class, 'storeAdmin'])->name('admin.auth.login.submit');
    });

    Route::middleware(['is_admin'])->group(function () {
        Route::match(['POST', 'GET'], '/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroyAdmin'])->name('admin.auth.logout');

        Route::group(['prefix' => 'users'], function () {
            Route::match(['POST', 'GET'], '/', [UserController::class, 'index'])->name('admin.users.index');
            Route::match(['POST', 'GET'], '/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::match(['POST', 'GET'], '/store', [UserController::class, 'store'])->name('admin.users.store');
            Route::match(['POST', 'GET'], '/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::match(['POST', 'GET'], '/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });
        Route::group(['prefix' => 'awards'], function () {
            Route::match(['POST', 'GET'], '/', [AwardController::class, 'index'])->name('admin.awards.index');
            Route::match(['POST', 'GET'], '/create', [AwardController::class, 'create'])->name('admin.awards.create');
            Route::match(['POST', 'GET'], '/store', [AwardController::class, 'store'])->name('admin.awards.store');
            Route::match(['POST', 'GET'], '/{award}/edit', [AwardController::class, 'edit'])->name('admin.awards.edit');
            Route::match(['POST', 'GET'], '/{award}', [AwardController::class, 'update'])->name('admin.awards.update');
            Route::delete('/{award}', [AwardController::class, 'destroy'])->name('admin.awards.destroy');
        });
        Route::group(['prefix' => 'news'], function () {
            Route::match(['POST', 'GET'], '/', [NewsController::class, 'index'])->name('admin.news.index');
            Route::match(['POST', 'GET'], '/create', [NewsController::class, 'create'])->name('admin.news.create');
            Route::match(['POST', 'GET'], '/store', [NewsController::class, 'store'])->name('admin.news.store');
            Route::match(['POST', 'GET'], '/{news}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
            Route::match(['POST', 'GET'], '/{news}', [NewsController::class, 'update'])->name('admin.news.update');
            Route::delete('/{news}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
        });
        Route::group(['prefix' => 'sets'], function () {
            Route::match(['POST', 'GET'], '/', [SetController::class, 'index'])->name('admin.sets.index');
            Route::match(['POST', 'GET'], '/create', [SetController::class, 'create'])->name('admin.sets.create');
            Route::match(['POST', 'GET'], '/store', [SetController::class, 'store'])->name('admin.sets.store');
            Route::match(['POST', 'GET'], '/{set}/edit', [SetController::class, 'edit'])->name('admin.sets.edit');
            Route::match(['POST', 'GET'], '/{set}', [SetController::class, 'update'])->name('admin.sets.update');
            Route::delete('/{set}', [SetController::class, 'destroy'])->name('admin.sets.destroy');
            /* Route::post('/import-sets', [SetController::class, 'importSets'])->name('sets.import'); */
        });
        Route::group(['prefix' => 'minifigs'], function () {
            Route::match(['POST', 'GET'], '/', [MinifigController::class, 'index'])->name('admin.minifigs.index');
            Route::match(['POST', 'GET'], '/create', [MinifigController::class, 'create'])->name('admin.minifigs.create');
            Route::match(['POST', 'GET'], '/store', [MinifigController::class, 'store'])->name('admin.minifigs.store');
            Route::match(['POST', 'GET'], '/{minifig}/edit', [MinifigController::class, 'edit'])->name('admin.minifigs.edit');
            Route::match(['POST', 'GET'], '/{minifig}', [MinifigController::class, 'update'])->name('admin.minifigs.update');
            Route::delete('/{minifig}', [MinifigController::class, 'destroy'])->name('admin.minifigs.destroy');
        });

        /*   Route::post('/import', [ImportController::class, 'import'])->name('import');
        Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum'); */
    })->middleware('auth:sanctum');

    /*  Route::post('/users-import', [ImportController::class, 'import']);
    Route::get('/users-export', [ImportController::class, 'export']); */
});
