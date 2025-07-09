<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\MedicationController;


// CSRF Protection Route (handled by Sanctum)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->noContent();
});

// Guest
Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
// Protected
Route::middleware('auth')->group(function () {
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



// Pharmacies API

Route::prefix('pharmacies')->group(function () {
    Route::get('/', [PharmacyController::class, 'index']);

    Route::get('/browse_by_categories', [PharmacyController::class, function () {
        return [];
    }]);

    Route::get('/most-searched-medications', [PharmacyController::class, function () {
        return ['Good response'];
    }]);
    Route::get('/nearby', [PharmacyController::class, 'getNearby']);
    Route::get('/search', [PharmacyController::class, 'searchPharmacy']);
    Route::get('/{pharmacy}/medications/search', [PharmacyController::class, 'searchPharmacyMedications']);
    Route::get('/{pharmacy}/medications/{medication}', [PharmacyController::class, 'medicationDetail']);
    Route::get('/{pharmacy}', [PharmacyController::class, 'show']);
});
// Medications API
Route::prefix('medications')->group(function () {
    Route::get('/search', [MedicationController::class, 'search']);
});
