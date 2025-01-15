<?php

use App\Http\Controllers\WingbandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('wingband')->group(function () {
    Route::post('/import-wingband', [WingbandController::class, 'importWingband']);
});
