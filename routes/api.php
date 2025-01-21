<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\StagController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WingbandController;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wingbands', [WingbandController::class, 'index']);

    Route::prefix('wingband')->group(function () {
        Route::middleware([CorsMiddleware::class])
            ->post('/import-wingband', [WingbandController::class, 'importWingband']);
        Route::post('/store-wingband', [WingbandController::class, 'storeWingband']);
        Route::post('/update/{id}', [WingbandController::class, 'update']);
        Route::delete('/delete/{id}', [WingbandController::class, 'delete']);
    });

    /* seasons */
    Route::prefix('season')->group(function () {
        Route::get('count', [SeasonController::class, 'countRegistry']);
    });

    /* summaries */
    Route::prefix('summary')->group(function () {
        Route::get('breeders', [SummaryController::class, 'getBreeders']);
        Route::get('chapters', [SummaryController::class, 'getChapters']);
        Route::get('farms', [SummaryController::class, 'getFarms']);
        Route::get('stags', [SummaryController::class, 'getStags']);
        Route::get('statistics', [SummaryController::class, 'getStatistics']);
    });

});

Route::prefix('stag')->group(function () {
    Route::post('/export-stag-summary', [StagController::class, 'exportStagSummary']);
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::any('change-password', [AuthController::class, 'changePassword']);
        Route::any('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::prefix('user')->group(function () {
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::delete('/delete/{id}', [UserController::class, 'delete']);
    });
});
