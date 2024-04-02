<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CourierController;

Route::get('/', function () {
    return view('frontend.index.index');
});

Route::prefix('admin')->group(function () {
    Route::prefix('countries')->group(function () {
        Route::get('index', [CountryController::class, 'index']);
        Route::post('store', [CountryController::class, 'store']);
    });
    Route::prefix('couriers')->group(function () {
        Route::get('index', [CourierController::class, 'index']);
        Route::post('store', [CourierController::class, 'store']);
    });
});
