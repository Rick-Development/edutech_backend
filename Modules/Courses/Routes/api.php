<?php

use Illuminate\Support\Facades\Route;
use Modules\Courses\Http\Controllers\CourseController;

Route::middleware(['auth:sanctum'])->prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);    // List all courses
    Route::get('/{id}', [CourseController::class, 'show']); // Get course by ID
});