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
        Route::get('/', [AuthController::class, 'getProfile']);
        Route::post('/', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

});
Route::get('/user/avatar/{id}', [AuthController::class, 'getAvatarById']);

Route::get('/instagram/callback', [InstagramController::class, 'handleCallback']);
Route::get('/facebook/callback', [FacebookController::class, 'handleCallback']);
