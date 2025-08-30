<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InstagramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/instagram/connect', [InstagramController::class, 'redirectToInstagram']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/instagram/callback', [InstagramController::class, 'handleCallback']);
