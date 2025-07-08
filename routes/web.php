<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Add Swagger documentation route
Route::get('api/documentation', function () {
    return redirect('api/documentation/index.html');
});

// Add a route for Swagger UI index.html
Route::get('api/documentation/index.html', function () {
    return view('vendor.l5-swagger.index', [
        'documentation' => 'default',
        'documentationTitle' => 'Translation Service API',
        'urlsToDocs' => [
            'Translation Service API' => url('docs/api-docs.json'),
        ],
        'operationsSorter' => null,
        'configUrl' => null,
        'validatorUrl' => null,
        'useAbsolutePath' => false,
    ]);
});

// Add a route for API docs JSON
Route::get('docs/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});

// Add a route for Swagger UI assets
Route::get('api/documentation/swagger-ui.css', function () {
    return response()->file(base_path('vendor/swagger-api/swagger-ui/dist/swagger-ui.css'));
});

Route::get('api/documentation/swagger-ui-bundle.js', function () {
    return response()->file(base_path('vendor/swagger-api/swagger-ui/dist/swagger-ui-bundle.js'));
});

Route::get('api/documentation/swagger-ui-standalone-preset.js', function () {
    return response()->file(base_path('vendor/swagger-api/swagger-ui/dist/swagger-ui-standalone-preset.js'));
});

Route::get('api/documentation/favicon-16x16.png', function () {
    return response()->file(base_path('vendor/swagger-api/swagger-ui/dist/favicon-16x16.png'));
});

Route::get('api/documentation/favicon-32x32.png', function () {
    return response()->file(base_path('vendor/swagger-api/swagger-ui/dist/favicon-32x32.png'));
});
