<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Translation Service API",
 *     version="1.0.0",
 *     description="API Documentation for Translation Service",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="Support Team"
 *     ),
 *     @OA\License(
 *         name="MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 * @OA\Tag(
 *     name="Languages",
 *     description="API Endpoints for language management"
 * )
 * @OA\Tag(
 *     name="Tags",
 *     description="API Endpoints for tag management"
 * )
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for translation management"
 * )
 * @OA\Tag(
 *     name="Export",
 *     description="API Endpoints for exporting translations"
 * )
 */
class SwaggerController extends Controller
{
    // This controller exists solely to host the OpenAPI annotations
} 