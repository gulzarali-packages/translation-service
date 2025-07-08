<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SwaggerController;

Route::get('/', function () {
    return view('welcome');
});

// Swagger documentation
Route::get('/api/documentation', [SwaggerController::class, 'api'])
    ->defaults('documentation', 'default')
    ->name('l5-swagger.default.api');
