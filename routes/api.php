<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/trending-products', [DashboardController::class, 'getTrendingProducts']);
    Route::get('/top-movers', [DashboardController::class, 'getTopMovers']);
});
