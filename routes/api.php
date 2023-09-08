<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LicenceController;
use App\Http\Controllers\Api\Wordpress\HR\DpdController;

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::prefix('licence')->group(function () {
    Route::post('/activate', [LicenceController::class, 'activate']);
    Route::post('/check', [LicenceController::class, 'check']);
    Route::post('/buy', [LicenceController::class, 'buy']);
});

Route::prefix('wordpress')->group(function () {
    Route::prefix('hr')->group(function () {
        Route::prefix('dpd')->group(function () {
            Route::post('printLabel', [DpdController::class, 'printLabel']);
            Route::post('printLabels', [DpdController::class, 'printLabels']);
            Route::post('getParcelStatus', [DpdController::class, 'getParcelStatus']);
            Route::post('collectionRequest', [DpdController::class, 'collectionRequest']);
        });
    });
});