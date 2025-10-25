<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\AuthController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/biometric-login', [AuthController::class, 'biometricLogin']);
    
    // Protected routes (require Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/enable-biometric', [AuthController::class, 'enableBiometric']);
        Route::post('/disable-biometric', [AuthController::class, 'disableBiometric']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});