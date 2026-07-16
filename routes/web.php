<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Health Check for DB
Route::get('/api/health/db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'connected', 'message' => 'Successfully connected to the database.']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'disconnected', 'message' => 'Could not connect to the database.'], 500);
    }
});

// Login Route (Vue Router handles actual rendering)
Route::get('/', function () {
    return view('app');
});

// Vue SPA Catch-all Route
Route::get('/app/{any}', function () {
    return view('app');
})->where('any', '.*');
