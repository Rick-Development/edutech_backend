<?php

use Illuminate\Support\Facades\Route;
use Modules\AppConfigurations\Http\Controllers\AppConfigurationController;

Route::prefix('config')->group(function () {
    Route::get('/', [AppConfigurationController::class, 'index']);
    // Route::put('/update', [AppConfigurationController::class, 'update']);
});
