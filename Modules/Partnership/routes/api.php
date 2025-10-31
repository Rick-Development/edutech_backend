<?php

use Illuminate\Support\Facades\Route;
use Modules\Partnership\Http\Controllers\PartnerController;

// User routes
Route::middleware(['auth:sanctum'])->prefix('partnership')->group(function () {
    Route::post('/apply', [PartnerController::class, 'apply']);
    Route::get('/status', [PartnerController::class, 'getApplicationStatus']);
    Route::get('/dashboard', [PartnerController::class, 'getDashboard']);
});

// Admin routes (add 'role:admin' middleware later)
Route::prefix('admin/partnership')->middleware('auth:sanctum')->group(function () {
    Route::post('/process', [PartnerController::class, 'processApplication']);
    Route::get('/applications', [PartnerController::class, 'listApplications']);
});