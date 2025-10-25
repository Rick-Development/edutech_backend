<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Default Laravel API route (optional)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 👇 AUTO-LOAD ALL MODULE API ROUTES
$modulesPath = base_path('Modules');
if (is_dir($modulesPath)) {
    $modules = array_diff(scandir($modulesPath), ['.', '..']);
    foreach ($modules as $module) {
        $apiRoutesFile = base_path("Modules/{$module}/Routes/api.php");
        if (file_exists($apiRoutesFile)) {
            require $apiRoutesFile;
        }
    }
}