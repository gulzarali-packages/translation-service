# Translation Service API Documentation

This project uses OpenAPI (Swagger) for API documentation. The documentation is automatically generated from annotations in the controller files.

## Accessing the Documentation

The API documentation is available at:

```
http://your-domain.com/api/documentation
```

When running locally:

```
http://localhost:8000/api/documentation
```

## API Information Endpoint

A summary of available API endpoints can be accessed at:

```
http://your-domain.com/api
```

## Updating the Documentation

The documentation is generated from annotations in the controller files. To update the documentation:

1. Add or modify the OpenAPI annotations in the relevant controller files
2. Run the following command to regenerate the documentation:

```bash
php artisan l5-swagger:generate
```

## Annotation Examples

### Controller Documentation

```php
/**
 * @OA\Info(
 *     title="Translation Service API",
 *     version="1.0.0",
 *     description="API Documentation for Translation Service",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="Support Team"
 *     )
 * )
 */
```

### Endpoint Documentation

```php
/**
 * @OA\Get(
 *     path="/languages",
 *     summary="Get all languages",
 *     tags={"Languages"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of languages",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="English"),
 *                     @OA\Property(property="code", type="string", example="en")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
```

## Authentication

The API uses Bearer token authentication. In the Swagger UI, click on the "Authorize" button and enter your token to test authenticated endpoints.

## Available Tags

The API endpoints are organized into the following tags:

-   Authentication: User login, logout, and profile
-   Languages: Language management
-   Tags: Tag management
-   Translations: Translation management
-   Export: Export translations in various formats

## Configuration

The OpenAPI configuration is located in `config/l5-swagger.php`. You can customize the following:

-   Documentation title and description
-   API routes
-   Security definitions
-   UI customization
-   Documentation generation settings

## Resources

-   [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
-   [OpenAPI Specification](https://swagger.io/specification/)
-   [Swagger UI](https://swagger.io/tools/swagger-ui/)
