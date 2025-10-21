<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacebookController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\InstagramController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ScoreController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);

    Route::prefix('instagram')->group(function () {
        Route::get('/connect', [InstagramController::class, 'redirectToInstagram']);
        Route::get('/metrics', [InstagramController::class, 'fetchOrStoreMetrics']);
        Route::delete('/disconnect', [InstagramController::class, 'disconnectInstagram']);
    });

    Route::prefix('facebook')->group(function () {
        Route::get('/connect', [FacebookController::class, 'redirectToFacebook']);
        Route::get('/metrics', [FacebookController::class, 'fetchOrStoreMetrics']);
        Route::delete('/disconnect', [FacebookController::class, 'disconnectFacebook']);
    });

    Route::get('/score', [ScoreController::class, 'getScore']);
    Route::get('/leaderboard/{period}', [ScoreController::class, 'getLeaderboard']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::post('/', [ProfileController::class, 'updateProfile']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });
});
Route::get('/user/avatar/{id}', [ProfileController::class, 'getAvatarById']);

Route::get('/instagram/callback', [InstagramController::class, 'handleCallback']);
Route::get('/facebook/callback', [FacebookController::class, 'handleCallback']);
