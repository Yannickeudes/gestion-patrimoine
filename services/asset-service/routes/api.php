<?php

use App\Http\Controllers\ActifController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\LocalisationController;
use App\Http\Controllers\RapportController;
use Illuminate\Support\Facades\Route;

Route::get('/debug-log', fn () => response()->file(storage_path('logs/laravel.log')));
Route::get('/health', fn () => response()->json(['service' => 'asset-service', 'status' => 'ok']));

Route::middleware('auth.service')->group(function () {
    // 'categories' -> parametre {category} en singulier anglais : on force {categorie}
    Route::apiResource('categories', CategorieController::class)
        ->parameters(['categories' => 'categorie']);
    Route::apiResource('localisations', LocalisationController::class);
    Route::apiResource('actifs', ActifController::class);
    Route::post('actifs/{actif}/statut', [ActifController::class, 'changeStatut']);
    Route::apiResource('affectations', AffectationController::class)->except(['update']);
    Route::patch('affectations/{affectation}/cloturer', [AffectationController::class, 'cloturer']);
    Route::apiResource('evaluations', EvaluationController::class)->except(['update']);

    Route::prefix('rapports')->group(function () {
        Route::get('patrimoine', [RapportController::class, 'patrimoine']);
        Route::get('actifs-dormants', [RapportController::class, 'actifsDormants']);
    });
});
