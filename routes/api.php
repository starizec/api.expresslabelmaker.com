<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['checkUserProperty', 'authenticateRequest'])->prefix('v1')->group(function () {
    Route::prefix('licence')->group(function () {
        Route::post('/activate', [App\Http\Controllers\Api\LicenceController::class, 'activate']);
        Route::post('/check', [App\Http\Controllers\Api\LicenceController::class, 'check']);
        Route::post('/buy', [App\Http\Controllers\Api\LicenceController::class, 'buy']);
    });

    Route::post('/parcel-statuses', [App\Http\Controllers\Api\StatusController::class, 'get']);

    Route::prefix('hr')->group(function () {
        Route::middleware(['checkHrDpdUserProperty'])->prefix('dpd')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'createLabel']);
                Route::post('labels', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'createLabels']);
                Route::post('collection-request', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'collectionRequest']);
            });
        });
    });
});
