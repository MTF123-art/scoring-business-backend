<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacebookController;
use App\Http\Controllers\Api\InstagramController;
use App\Http\Controllers\Api\ScoreController;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/instagram/connect', [InstagramController::class, 'redirectToInstagram']);
    Route::get('/instagram/metrics', [InstagramController::class, 'fetchOrStoreMetrics']);
    Route::get('/facebook/connect', [FacebookController::class, 'redirectToFacebook']);
    Route::get('/facebook/metrics', [FacebookController::class, 'fetchOrStoreMetrics']);
    Route::get('/score', [ScoreController::class, 'getScore']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/instagram/callback', [InstagramController::class, 'handleCallback']);
Route::get('/facebook/callback', [FacebookController::class, 'handleCallback']);
