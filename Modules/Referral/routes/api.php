<?php

use Illuminate\Support\Facades\Route;
use Modules\Referral\Http\Controllers\ReferralController;


Route::middleware(['auth:sanctum'])->prefix('referral')->group(function () {
    Route::get('/dashboard', [ReferralController::class, 'getDashboard']);
    Route::get('/history', [ReferralController::class, 'getHistory']);
});