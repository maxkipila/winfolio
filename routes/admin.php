<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\MinifigureController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SetController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

//Admin Routes

Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/', [AuthenticatedSessionController::class, 'createAdmin'])->name('admin.auth.login');
        Route::post('/', [AuthenticatedSessionController::class, 'storeAdmin'])->name('admin.auth.login.submit');
    });

    Route::middleware(['is_admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroyAdmin'])->name('admin.auth.logout');

        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/store', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });
        Route::group(['prefix' => 'awards'], function () {
            Route::get('/', [AwardController::class, 'index'])->name('admin.awards.index');
            Route::get('/create', [AwardController::class, 'create'])->name('admin.awards.create');
            Route::post('/store', [AwardController::class, 'store'])->name('admin.awards.store');
            Route::get('/{award}/edit', [AwardController::class, 'edit'])->name('admin.awards.edit');
            Route::put('/{award}', [AwardController::class, 'update'])->name('admin.awards.update');
            Route::delete('/{award}', [AwardController::class, 'destroy'])->name('admin.awards.destroy');
        });
        Route::group(['prefix' => 'news'], function () {
            Route::get('/', [NewsController::class, 'index'])->name('admin.news.index');
            Route::get('/create', [NewsController::class, 'create'])->name('admin.news.create');
            Route::post('/store', [NewsController::class, 'store'])->name('admin.news.store');
            Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('admin.news.edit');
            Route::put('/{news}', [NewsController::class, 'update'])->name('admin.news.update');
            Route::delete('/{news}', [NewsController::class, 'destroy'])->name('admin.news.destroy');
        });
        Route::group(['prefix' => 'sets'], function () {
            Route::get('/', [SetController::class, 'index'])->name('admin.sets.index');
            Route::get('/create', [SetController::class, 'create'])->name('admin.sets.create');
            Route::post('/store', [SetController::class, 'store'])->name('admin.sets.store');
            Route::get('/{set}/edit', [SetController::class, 'edit'])->name('admin.sets.edit');
            Route::put('/{set}', [SetController::class, 'update'])->name('admin.sets.update');
            Route::delete('/{set}', [SetController::class, 'destroy'])->name('admin.sets.destroy');
        });
        Route::group(['prefix' => 'minifigs'], function () {
            Route::get('/', [MinifigureController::class, 'index'])->name('admin.minifigs.index');
            Route::get('/create', [MinifigureController::class, 'create'])->name('admin.minifigs.create');
            Route::post('/store', [MinifigureController::class, 'store'])->name('admin.minifigs.store');
            Route::get('/{minifig}/edit', [MinifigureController::class, 'edit'])->name('admin.minifigs.edit');
            Route::put('/{minifig}', [MinifigureController::class, 'update'])->name('admin.minifigs.update');
            Route::delete('/{minifig}', [MinifigureController::class, 'destroy'])->name('admin.minifigs.destroy');
        });
    });
});
