<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('frontend.index.index');
})->name('frontend.index');

/* Route::get('/{slug}', [App\Http\Controllers\PageController::class, 'show'])->name('page.show'); */

Route::get('language/{lang}', [App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

    Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('overseas/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\OverseasController::class, 'getDeliveryLocations'])->name('overseas-delivery-locations');
        Route::get('dpd/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\DpdController::class, 'getDeliveryLocations'])->name('dpd-delivery-locations');
        Route::get('hp/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\HpController::class, 'getDeliveryLocations'])->name('hp-delivery-locations');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\UserProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [App\Http\Controllers\Auth\LogoutController::class, 'logout'])->name('logout');
});