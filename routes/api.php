<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WingbandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('wingband')->middleware('auth:sanctum')->group(function () {
    Route::post('/import-wingband', [WingbandController::class, 'importWingband']);

    Route::post('/update/{id}', [WingbandController::class, 'update']);
    Route::delete('/delete/{id}', [WingbandController::class, 'delete']);
});

Route::prefix('stag')->group(function () {
    Route::post('/export-stag-summary', [StagController::class, 'exportStagSummary']);
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
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
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
});
