<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskPhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (consumed by the Flutter mobile app)
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api (see bootstrap/app.php). Authentication
| uses the stateless "api" guard — a hashed bearer token resolved by the
| custom driver in AppServiceProvider. Once authenticated, the tenant global
| scope and the model policies apply automatically, exactly as on the web.
|
| We intentionally do NOT put these behind the subscription gate: that
| middleware issues an HTML redirect meant for the browser. Billing state is
| a web concern for the MVP; the mobile app only ever sees its own company's
| data regardless.
|
*/

// Public: exchange credentials for a token.
Route::post('/login', [AuthController::class, 'login']);

// Razorpay does not send a session. Authenticity is the webhook signature.
Route::post('/webhooks/razorpay', RazorpayWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.razorpay');

Route::middleware('auth:api')->group(function () {
    // Session helpers.
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Field workers read their assigned tasks and post progress updates.
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);

    // Daily site reports (what happened, labour, note, photos).
    Route::get('/daily-reports/projects', [DailyReportController::class, 'projects']);
    Route::get('/daily-reports', [DailyReportController::class, 'index']);
    Route::post('/daily-reports', [DailyReportController::class, 'store'])->middleware('throttle:30,1');
    Route::post('/daily-reports/{report}/photos', [DailyReportController::class, 'storePhoto'])->middleware('throttle:30,1');
    Route::delete('/daily-report-photos/{photo}', [DailyReportController::class, 'destroyPhoto']);
    Route::get('/daily-report-photos/{photo}/file', [DailyReportController::class, 'photoFile'])->name('api.daily-report-photos.file');

    // Site photos on a task.
    Route::get('/tasks/{task}/photos', [TaskPhotoController::class, 'index']);
    Route::post('/tasks/{task}/photos', [TaskPhotoController::class, 'store'])->middleware('throttle:30,1');
    Route::delete('/photos/{photo}', [TaskPhotoController::class, 'destroy']);
    Route::get('/photos/{photo}/file', [TaskPhotoController::class, 'file'])->name('api.task-photos.file');
});
