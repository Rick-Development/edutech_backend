<?php

use Illuminate\Support\Facades\Route;
use Modules\Partnership\Http\Controllers\PartnerController;

// User routes
Route::prefix('partnership')->middleware('auth:sanctum')->group(function () {
    Route::post('/apply', [PartnerController::class, 'apply']);
    Route::get('/status', [PartnerController::class, 'getApplicationStatus']);
});

// Admin routes (add 'role:admin' middleware later)
Route::prefix('admin/partnership')->middleware('auth:sanctum')->group(function () {
    Route::post('/process', [PartnerController::class, 'processApplication']);
    Route::get('/applications', [PartnerController::class, 'listApplications']);
});