<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\app\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    Route::put('update-password', [UserController::class, 'updatePassword']);
    // get biometric token
    Route::get('biometric-token', [UserController::class, 'getBiometricToken']);
    // notification settings
    Route::put('notification-settings', [UserController::class, 'updateNotificationSettings']);
    // two factor auth settings
    Route::put('two-factor-auth', [UserController::class, 'updateTwoFactorAuth']);
    // delete account
    Route::put('delete-account', [UserController::class, 'deleteAccount']);
});
