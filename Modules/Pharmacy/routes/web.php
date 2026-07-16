<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharmacy\Http\Controllers\PharmacyController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pharmacies', PharmacyController::class)->names('pharmacy');
});
