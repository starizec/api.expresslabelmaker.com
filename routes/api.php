<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['checkUserProperty', 'checkUserLicence'])->prefix('api/v1')->group(function () {
    Route::prefix('licence')->group(function () {
        Route::post('/start-trial', [App\Http\Controllers\Api\V1\LicenceController::class, 'startTrial'])->withoutMiddleware(['checkUserLicence']);
        Route::post('/check', [App\Http\Controllers\Api\V1\LicenceController::class, 'check']);
        Route::post('/buy', [App\Http\Controllers\Api\V1\LicenceController::class, 'buy'])->withoutMiddleware(['checkUserLicence']);
    });

    Route::prefix('user')->group(function () {
        Route::post('/create', [App\Http\Controllers\Api\V1\UserController::class, 'create'])->withoutMiddleware(['checkUserLicence', 'checkUserProperty']);
    });

    Route::post('/parcel-statuses', [App\Http\Controllers\Api\V1\StatusController::class, 'get']);

    Route::prefix('hr')->group(function () {
        Route::middleware(['checkHrDpdUserProperty'])->prefix('dpd')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'createLabel']);
                Route::post('labels', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'createLabels']);
                Route::post('collection-request', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'collectionRequest']);
            });
            Route::post('delivery-locations', [App\Http\Controllers\Api\V1\HR\DpdController::class, 'getDeliveryLocations']);
        });

        Route::middleware(['checkHrOverseasUserProperty'])->prefix('overseas')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [App\Http\Controllers\Api\V1\HR\OverseasController::class, 'createLabel']);
                Route::post('labels', [App\Http\Controllers\Api\V1\HR\OverseasController::class, 'createLabels']);
                Route::post('collection-request', [App\Http\Controllers\Api\V1\HR\OverseasController::class, 'collectionRequest']);
            });
            Route::post('delivery-locations', [App\Http\Controllers\Api\V1\HR\OverseasController::class, 'getDeliveryLocations']);
        });

        Route::middleware(['checkHrHpUserProperty'])->prefix('hp')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [App\Http\Controllers\Api\V1\HR\HpController::class, 'createLabel']);
                Route::post('labels', [App\Http\Controllers\Api\V1\HR\HpController::class, 'createLabels']);
                Route::post('collection-request', [App\Http\Controllers\Api\V1\HR\HpController::class, 'collectionRequest']);
            });
        });
    });

    Route::prefix('si')->group(function () {
        Route::middleware(['checkHrDpdUserProperty'])->prefix('dpd')->group(function () {
            Route::prefix('create')->group(function () {
                Route::post('label', [App\Http\Controllers\Api\V1\SI\DpdController::class, 'createLabel']);
                Route::post('labels', [App\Http\Controllers\Api\V1\SI\DpdController::class, 'createLabels']);
                Route::post('collection-request', [App\Http\Controllers\Api\V1\SI\DpdController::class, 'collectionRequest']);
            });
        });
    });
});
