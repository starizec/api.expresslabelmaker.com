<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;


Route::middleware(['auth', 'admin'])->group(function () {
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
});


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/', [UserProfileController::class, 'index'])->name('frontend.index');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
});
