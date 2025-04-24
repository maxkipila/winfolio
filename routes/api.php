<?php

use App\Http\Controllers\TrendController;
use Illuminate\Support\Facades\Route;

Route::prefix('trends')->group(function () {
    // Historie cen
    Route::get('price-history/{product}', [TrendController::class, 'getPriceHistory']);

    // Statistiky cen
    Route::get('price-stats/{product}', [TrendController::class, 'getPriceStats']);

    // Růst cen
    Route::get('price-growth/{product}', [TrendController::class, 'calculateGrowth']);

    // Top movers
    Route::get('top-movers', [TrendController::class, 'getTopMovers']);

    // Trendující produkty
    Route::get('trending-products', [TrendController::class, 'getTrendingProducts']);
});
