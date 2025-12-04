<?php

use App\Http\Controllers\CowController;
use App\Http\Controllers\FeatureFlagDemoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Cow CRUD Routes (no auth required for tests)
Route::apiResource('cows', CowController::class);

// Feature Flag Demo Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/demo/dashboard', [FeatureFlagDemoController::class, 'dashboard']);
    Route::get('/demo/beta-features', [FeatureFlagDemoController::class, 'betaFeatures']);
    Route::get('/demo/api-info', [FeatureFlagDemoController::class, 'apiInfo']);
    Route::get('/demo/ab-test', [FeatureFlagDemoController::class, 'abTest']);
    Route::get('/demo/theme', [FeatureFlagDemoController::class, 'theme']);
    Route::get('/demo/user-features', [FeatureFlagDemoController::class, 'userFeatures']);
    Route::post('/demo/toggle-feature/{featureName}', [FeatureFlagDemoController::class, 'toggleFeature']);
});

// Public feature flag routes (no auth required)
Route::get('/demo/system-status', [FeatureFlagDemoController::class, 'systemStatus']);
Route::get('/demo/seasonal-features', [FeatureFlagDemoController::class, 'seasonalFeatures']);
