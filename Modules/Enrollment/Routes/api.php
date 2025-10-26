<?php

use Illuminate\Support\Facades\Route;
use Modules\Enrollment\Http\Controllers\EnrollmentController;

Route::middleware(['auth:sanctum'])->prefix('enrollment')->group(function () {
    Route::post('/', [EnrollmentController::class, 'enroll']);
    Route::get('/matric', [EnrollmentController::class, 'getMatricNumber']);
    Route::get('/letter', [EnrollmentController::class, 'getAdmissionLetterUrl']); 
});