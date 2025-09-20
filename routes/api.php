<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\SearchedMedsController;
use App\Http\Controllers\ChapaController;
use Illuminate\Support\Facades\Log;

// Guest
Route::get('/test', function () {
    return response()->json(['message' => 'OK']);
});
Route::post('/test-login', function (Request $request) {
    return response()->json([
        'received' => $request->all(),
        'headers' => $request->headers->all(),
        'server' => $_SERVER
    ]);
});
Route::post('/debug-auth', function (Request $request) {
    Log::debug('Debug Auth Route Hit');

    try {
        $user = \App\Models\User::first();
        Log::debug('User found', ['id' => $user->id]);

        Log::debug('Auth successful');

        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        Log::error('Debug Auth Failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
});
Route::middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
    // Route::put('/change-password', [NewPasswordController::class, 'change']);
});
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



// Pharmacies API

Route::prefix('pharmacies')->group(function () {
    Route::get('/', [PharmacyController::class, 'index']);
    Route::post('/', [PharmacyController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{id}', [PharmacyController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/browse_by_categories', [PharmacyController::class, function () {
        return [];
    }]);
    Route::get('/nearby', [PharmacyController::class, 'getNearby']);
    Route::get('/counts', [PharmacyController::class, function () {
        return '100';
    }]);
    Route::get('/by-user/{userId}', [PharmacyController::class, 'getByUser']);
    Route::get('/get-pharmacy-info', [PharmacyController::class, 'getPharmacyInfo']);
    Route::get('/search', [PharmacyController::class, 'searchPharmacy']);
    Route::post('/most-searched-medications', [SearchedMedsController::class, 'storeMostSearched']);
    Route::get('/most-searched-medications', [SearchedMedsController::class, 'getMostSearched']);
    Route::get('/{pharmacy}/medications/search', [PharmacyController::class, 'searchPharmacyMedications']);
    Route::get('/{pharmacy}/medications/{medication}', [PharmacyController::class, 'medicationDetail']);
    Route::get('/{pharmacy}', [PharmacyController::class, 'show']);
});
// Medications API
Route::prefix('medications')->group(function () {
    Route::post('/', [MedicationController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{medication}', [MedicationController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/', [MedicationController::class, 'index']);
    Route::delete('/{medication}', [MedicationController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/counts', [MedicationController::class, function () {
        return '100';
    }]);
    Route::get('/search', [MedicationController::class, 'search']);
});

// Categories Api

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store'])->middleware('auth:sanctum');
});

// Payment API
Route::post('/pay',[ChapaController::class,'initialize'])->name('pay');
Route::post('callback/{reference}',[ChapaController::class,'callback'])->name('callback');
Route::get('/test-chapa', function() {
    return (config('chapa.secretKey'));
});
