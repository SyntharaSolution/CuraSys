<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('/employees', [AdminController::class, 'getEmployees']);
    Route::post('/employees', [AdminController::class, 'storeEmployee']);
    
    Route::get('/expenses', [AdminController::class, 'getExpenses']);
    Route::post('/expenses', [AdminController::class, 'storeExpense']);
});
