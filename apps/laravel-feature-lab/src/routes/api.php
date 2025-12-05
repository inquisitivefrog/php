<?php

use App\Http\Controllers\CowController;
use App\Http\Controllers\FeatureFlagDemoController;
use App\Http\Controllers\NotificationDemoController;
use App\Http\Controllers\QueueDemoController;
use App\Http\Controllers\ScoutDemoController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TelescopeDemoController;
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

// Subscription routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::post('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
    Route::get('/portal', [SubscriptionController::class, 'portal'])->name('portal');
});

// Queue demo routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('queue')->name('queue.')->group(function () {
    Route::post('/test', [QueueDemoController::class, 'dispatchTestJob'])->name('test');
    Route::post('/email', [QueueDemoController::class, 'dispatchEmailJob'])->name('email');
    Route::post('/delayed', [QueueDemoController::class, 'dispatchDelayedJob'])->name('delayed');
    Route::post('/chain', [QueueDemoController::class, 'dispatchChainedJob'])->name('chain');
    Route::post('/batch', [QueueDemoController::class, 'dispatchBatchJob'])->name('batch');
    Route::post('/failed', [QueueDemoController::class, 'dispatchFailedJob'])->name('failed');
    Route::get('/stats', [QueueDemoController::class, 'queueStats'])->name('stats');
});

// Telescope demo routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('telescope-demo')->name('telescope-demo.')->group(function () {
    Route::get('/queries', [TelescopeDemoController::class, 'databaseQueries'])->name('queries');
    Route::get('/cache', [TelescopeDemoController::class, 'cacheOperations'])->name('cache');
    Route::post('/job', [TelescopeDemoController::class, 'dispatchJob'])->name('job');
    Route::get('/logs', [TelescopeDemoController::class, 'logging'])->name('logs');
    Route::get('/exception', [TelescopeDemoController::class, 'throwException'])->name('exception');
    Route::post('/models', [TelescopeDemoController::class, 'modelOperations'])->name('models');
    Route::post('/event', [TelescopeDemoController::class, 'dispatchEvent'])->name('event');
    Route::get('/multiple', [TelescopeDemoController::class, 'multipleOperations'])->name('multiple');
    Route::get('/slow-query', [TelescopeDemoController::class, 'slowQuery'])->name('slow-query');
    Route::get('/n-plus-one', [TelescopeDemoController::class, 'nPlusOneQuery'])->name('n-plus-one');
});

// Scout demo routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('scout-demo')->name('scout-demo.')->group(function () {
    Route::post('/search', [ScoutDemoController::class, 'basicSearch'])->name('search');
    Route::post('/search/paginated', [ScoutDemoController::class, 'paginatedSearch'])->name('search.paginated');
    Route::post('/search/filtered', [ScoutDemoController::class, 'filteredSearch'])->name('search.filtered');
    Route::post('/search/field', [ScoutDemoController::class, 'fieldSearch'])->name('search.field');
    Route::post('/search/ordered', [ScoutDemoController::class, 'orderedSearch'])->name('search.ordered');
    Route::post('/import', [ScoutDemoController::class, 'importAll'])->name('import');
    Route::post('/remove', [ScoutDemoController::class, 'removeAll'])->name('remove');
    Route::get('/stats', [ScoutDemoController::class, 'stats'])->name('stats');
});

// Notification demo routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::post('/welcome', [NotificationDemoController::class, 'sendWelcomeEmail'])->name('welcome');
    Route::post('/task-assigned', [NotificationDemoController::class, 'sendTaskAssigned'])->name('task-assigned');
    Route::post('/password-reset-sms', [NotificationDemoController::class, 'sendPasswordResetSms'])->name('password-reset-sms');
    Route::post('/order-confirmation', [NotificationDemoController::class, 'sendOrderConfirmation'])->name('order-confirmation');
    Route::post('/system-alert', [NotificationDemoController::class, 'sendSystemAlert'])->name('system-alert');
    Route::post('/broadcast', [NotificationDemoController::class, 'sendToMultipleUsers'])->name('broadcast');
    Route::get('/stats', [NotificationDemoController::class, 'stats'])->name('stats');
});
