<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use L5Swagger\Http\Controllers\SwaggerController as L5SwaggerController;

/**
 * @OA\Info(
 *     title="Translation Management Service API",
 *     version="1.0.0",
 *     description="API documentation for Translation Management Service",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="Error",
 *     @OA\Property(property="message", type="string", example="Error message")
 * )
 */
class SwaggerController extends L5SwaggerController
{
    // This controller extends L5Swagger's controller
    // The annotations above are used for Swagger documentation generation
} 