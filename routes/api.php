<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LicenceController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Controllers\Api\V1\HR\DpdController;

Route::prefix('v1')->group(function () {
    Route::prefix('licence')->group(function () {
        Route::post('/activate', [LicenceController::class, 'activate']);
        Route::post('/check', [LicenceController::class, 'check']);
        Route::post('/buy', [LicenceController::class, 'buy']);
    });

    Route::post('/parcel-statuses', [StatusController::class, 'get']);

    Route::prefix('hr')->group(function () {
        Route::prefix('dpd')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [DpdController::class, 'createLabel']);
                Route::post('labels', [DpdController::class, 'createLabels']);
                Route::post('collection-request', [DpdController::class, 'collectionRequest']);
            });
        });
    });
});
