<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ExportController;
use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Export endpoints (can be public or protected depending on requirements)
Route::prefix('export')->group(function () {
    Route::get('/language/{languageCode}', [ExportController::class, 'exportByLanguage']);
    Route::get('/all', [ExportController::class, 'exportAll']);
    Route::get('/tags', [ExportController::class, 'exportByTags']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Language routes
    Route::apiResource('languages', LanguageController::class);

    // Tag routes
    Route::apiResource('tags', TagController::class);

    // Translation routes
    Route::apiResource('translations', TranslationController::class);
    Route::get('/translations/search', [TranslationController::class, 'search']);
}); 