<?php

use App\Http\Controllers\PharmacyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'welcome';
});
Route::get('/hello',function(){
    return '<h1>Hello There!</h1>';
});
// Pharmacies api
Route::prefix('api')->group(function(){
    Route::get('/pharmacies', [PharmacyController::class,'index']);
    Route::get('/browse_by_categories',function(){
        return [];
    });
    Route::get('/most-searched-medications',function(){
        return [];
    });
});