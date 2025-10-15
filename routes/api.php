<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Models\Store;
use App\Models\Product;

// Route health
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
        'database' => DB::connection()->getDatabaseName(),
    ]);
});

// Route public
Route::post('/v1/login', [AuthController::class, 'login']);

// Get current user profile
Route::get('/v1/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
})->middleware('auth:sanctum');

Route::get('/v1/reports/summary', [ReportController::class, 'summary'])->middleware('auth:sanctum');

// Route butuh autentikasi
Route::middleware('auth:sanctum')->group(function () {
    // Master data
    Route::get('/v1/stores', fn() => Store::all());
    Route::get('/v1/products', fn() => Product::all());

    // Report endpoints
    Route::post('/v1/report/{context}', [ReportController::class, 'store']);
    Route::get('/v1/reports', [ReportController::class, 'index']);

    // Logout
    Route::post('/v1/logout', [AuthController::class, 'logout']);
});
