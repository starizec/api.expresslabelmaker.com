<?php

use Illuminate\Support\Facades\Route;

Route::get('language/{lang}', [App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');

Route::get('/', [App\Http\Controllers\PageController::class, 'home'])->defaults('lang', 'hr');

Route::group(['prefix' => '{lang}', 'where' => ['lang' => '[a-zA-Z]{2}'], 'defaults' => ['lang' => 'hr']], function () {

    Route::get('/', [App\Http\Controllers\PageController::class, 'home'])->name('pages.index');
    Route::get('/legal/{slug}', [App\Http\Controllers\PostController::class, 'legalPost'])->name('pages.posts');
    Route::get('/documentation/{slug}', [App\Http\Controllers\PostController::class, 'documentationPost'])->name('pages.documentations');
    Route::get('/download', [App\Http\Controllers\PageController::class, 'download'])->name('pages.download');
    Route::get('/payment/{licence_uid}', [App\Http\Controllers\PageController::class, 'payment'])->name('pages.payment');
    Route::post('/stripe-session/{licence_uid}', [App\Http\Controllers\PaymentController::class, 'createSession'])->name('payment.create-session');

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

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\UserProfileController::class, 'index'])->name('profile');
        Route::put('/profile/update', [App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
        Route::post('/logout', [App\Http\Controllers\Auth\LogoutController::class, 'logout'])->name('logout');
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('overseas/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\OverseasController::class, 'getDeliveryLocations'])->name('overseas-delivery-locations');
        Route::get('dpd/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\DpdController::class, 'getDeliveryLocations'])->name('dpd-delivery-locations');
        Route::get('hp/delivery-locations', [App\Http\Controllers\DeliveryLocations\HR\HpController::class, 'getDeliveryLocations'])->name('hp-delivery-locations');
    });
});

Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');