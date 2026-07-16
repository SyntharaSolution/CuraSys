<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctor\Http\Controllers\DoctorController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::middleware('auth:sanctum')->prefix('v1/doctor')->group(function () {
    Route::get('/patients', [DoctorController::class, 'getPatients']);
    Route::post('/patients', [DoctorController::class, 'storePatient']);
    Route::put('/patients/{id}', [DoctorController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [DoctorController::class, 'deletePatient']);

    Route::get('/queue', [DoctorController::class, 'queue']);
    Route::post('/consultations/{id}/start', [DoctorController::class, 'startConsultation']);
    Route::post('/consultations/{id}/prescribe', [DoctorController::class, 'prescribe']);
    Route::get('/medicines/search', [DoctorController::class, 'searchMedicines']);
});
