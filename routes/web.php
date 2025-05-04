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
use App\Http\Controllers\DeliveryLocations\HR\OverseasController;
use App\Http\Controllers\DeliveryLocations\HR\DpdController;
use App\Http\Controllers\DeliveryLocations\HR\HpController;

// Public routes
Route::get('/', function () {
    return view('frontend.index.index');
})->name('frontend.index');

// Language switching route
Route::get('language/{lang}', [LanguageController::class, 'switchLang'])->name('language.switch');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password reset routes
    Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('overseas/delivery-locations', [OverseasController::class, 'getDeliveryLocations'])->name('overseas-delivery-locations');
        Route::get('dpd/delivery-locations', [DpdController::class, 'getDeliveryLocations'])->name('dpd-delivery-locations');
        Route::get('hp/delivery-locations', [HpController::class, 'getDeliveryLocations'])->name('hp-delivery-locations');
    });
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
});