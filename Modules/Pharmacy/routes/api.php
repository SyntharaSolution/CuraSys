<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharmacy\Http\Controllers\POSController;
use Modules\Pharmacy\Http\Controllers\PurchasingController;

use Modules\Pharmacy\Http\Controllers\PharmacyController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::middleware('auth:sanctum')->prefix('v1/pharmacy')->group(function () {
    // POS Endpoints
    Route::prefix('pos')->group(function () {
        Route::get('/search-medicines', [POSController::class, 'searchMedicines']);
        Route::post('/checkout', [POSController::class, 'checkout']);
        Route::get('/pending', [POSController::class, 'getPendingBills']);
        Route::post('/{uuid}/complete', [POSController::class, 'completeSale']);
        Route::get('/invoices', [POSController::class, 'getInvoices']);
        
        // New POS shift session, reprint, hold & return endpoints
        Route::post('/hold', [POSController::class, 'holdBill']);
        Route::get('/held-bills', [POSController::class, 'getHeldBills']);
        Route::post('/session/open', [POSController::class, 'openSession']);
        Route::get('/session/active', [POSController::class, 'getActiveSession']);
        Route::get('/session/suggest-float', [POSController::class, 'suggestFloat']);
        Route::post('/session/update-float', [POSController::class, 'updateFloat']);
        Route::post('/session/close', [POSController::class, 'closeSession']);
        Route::post('/session/movement', [POSController::class, 'logCashMovement']);
        Route::get('/session/movement-subcategories', [POSController::class, 'getMovementSubcategories']);
        Route::post('/reprint', [POSController::class, 'reprintInvoice']);
        Route::post('/return', [POSController::class, 'processReturn']);
        Route::get('/supplier-bills', [POSController::class, 'getSupplierBills']);
        Route::post('/supplier-bills/{id}/pay', [POSController::class, 'paySupplierBill']);
    });

    // Customer Management Endpoints
    Route::prefix('customers')->group(function () {
        Route::get('/', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'index']);
        Route::post('/', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'store']);
        Route::put('/{id}', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'update']);
        Route::get('/{id}/ledger', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'getLedger']);
        Route::get('/{id}/statement', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'getStatement']);
        Route::post('/{id}/payment', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'receiveCreditPayment']);
        Route::get('/{id}/loyalty', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'getLoyalty']);
        Route::post('/merge', [\Modules\Pharmacy\Http\Controllers\CustomerController::class, 'mergeCustomers']);
    });

    Route::prefix('patients')->group(function () {
        Route::get('/', [\Modules\Pharmacy\Http\Controllers\PatientController::class, 'index']);
        Route::post('/', [\Modules\Pharmacy\Http\Controllers\PatientController::class, 'store']);
        Route::put('/{id}', [\Modules\Pharmacy\Http\Controllers\PatientController::class, 'update']);
    });

    // Pharmacy Master Data
    Route::get('/categories', [PharmacyController::class, 'getCategories']);
    Route::post('/categories', [PharmacyController::class, 'storeCategory']);
    Route::put('/categories/{id}', [PharmacyController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [PharmacyController::class, 'deleteCategory']);

    Route::get('/medicines/subcategories', [PharmacyController::class, 'getSubcategories']);
    Route::get('/medicines', [PharmacyController::class, 'getMedicines']);
    Route::post('/medicines', [PharmacyController::class, 'storeMedicine']);
    Route::put('/medicines/{id}', [PharmacyController::class, 'updateMedicine']);
    Route::delete('/medicines/{id}', [PharmacyController::class, 'deleteMedicine']);

    // Purchasing & GRN Routes
    Route::get('/suppliers', [PurchasingController::class, 'getSuppliers']);
    Route::post('/suppliers', [PurchasingController::class, 'storeSupplier']);
    Route::put('/suppliers/{id}', [PurchasingController::class, 'updateSupplier']);
    Route::delete('/suppliers/{id}', [PurchasingController::class, 'deleteSupplier']);
    
    Route::get('/purchases', [PurchasingController::class, 'getPurchases']);
    Route::post('/purchases', [PurchasingController::class, 'submitPurchase']);
    
    Route::get('/batches/expiring', [PurchasingController::class, 'getExpiringBatches']);

    // Goods Received Notes (GRN) endpoints
    Route::get('/grn', [\Modules\Pharmacy\Http\Controllers\GRNController::class, 'index']);
    Route::post('/grn', [\Modules\Pharmacy\Http\Controllers\GRNController::class, 'submitGrn']);
    Route::get('/grn/po/{poId}', [\Modules\Pharmacy\Http\Controllers\GRNController::class, 'getPoDetails']);
    Route::post('/grn/{id}/approve-variance', [\Modules\Pharmacy\Http\Controllers\GRNController::class, 'approveVariance']);
    Route::get('/grn/debit-notes', [\Modules\Pharmacy\Http\Controllers\GRNController::class, 'getDebitNotes']);
});
