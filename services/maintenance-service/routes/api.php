<?php

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['service' => 'maintenance-service', 'status' => 'ok']));

Route::middleware('auth.service')->group(function () {
    Route::get('stats', [MaintenanceController::class, 'stats']);
    Route::apiResource('maintenances', MaintenanceController::class);
});
