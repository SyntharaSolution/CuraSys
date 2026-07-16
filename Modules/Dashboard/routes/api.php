<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::middleware('auth:sanctum')->prefix('v1/dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/low-stock', [DashboardController::class, 'lowStock']);
    Route::get('/recent-sales', [DashboardController::class, 'recentSales']);
});
