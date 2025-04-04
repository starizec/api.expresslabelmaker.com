<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LanguageController;

// Public routes
Route::get('/', function () {
    return view('frontend.index.index');
})->name('frontend.index');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Admin routes
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

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
});

// Language switcher
Route::get('language/{lang}', [LanguageController::class, 'switchLang'])->name('language.switch');
