# Translation Management Service

A scalable and high-performance API-driven service for managing translations across multiple languages.

## Features

-   Store translations for multiple locales (e.g., en, fr, es)
-   Tag translations for context (e.g., mobile, desktop, web)
-   CRUD operations for translations, languages, and tags
-   Search translations by keys, content, or tags
-   JSON export endpoint for frontend applications
-   Optimized for high performance (response times < 200ms)
-   Support for 100k+ records with efficient pagination and caching
-   OpenAPI (Swagger) documentation for all endpoints

## Tech Stack

-   Laravel 12
-   MySQL/PostgreSQL
-   Redis (for caching)
-   OpenAPI/Swagger for API documentation

## Requirements

-   PHP 8.1+
-   Composer
-   MySQL 8.0+ or PostgreSQL 12+
-   Redis (optional but recommended for performance)

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd translation-service
```

2. Install dependencies:

```bash
composer install
```

3. Set up the environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in the `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=translation_service
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations:

```bash
php artisan migrate
```

6. (Optional) Seed the database with test data:

```bash
php artisan translation:seed --count=100000
```

7. Start the server:

```bash
php artisan serve
```

## API Documentation

The API provides endpoints for managing translations, languages, and tags.

### OpenAPI Documentation

Interactive API documentation is available at:

```
http://localhost:8000/api/documentation
```

This documentation is generated from annotations in the controller files and provides:

-   Detailed endpoint descriptions
-   Request/response examples
-   Interactive testing capabilities
-   Authentication support

For more details on the API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Authentication

The API uses Laravel Sanctum for token-based authentication.

#### Login

```
POST /api/login
```

Request:

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

Response:

```json
{
    "token": "1|12345abcde...",
    "user": {
        "id": 1,
        "name": "User",
        "email": "user@example.com"
    }
}
```

### Translations

#### List Translations

```
GET /api/translations
```

Query parameters:

-   `language_id`: Filter by language ID
-   `tag`: Filter by tag name
-   `key`: Filter by key (partial match)
-   `content`: Filter by content (partial match)
-   `per_page`: Number of items per page (default: 15)

#### Get Translation

```
GET /api/translations/{id}
```

#### Create Translation

```
POST /api/translations
```

Request:

```json
{
    "language_id": 1,
    "key": "welcome_message",
    "content": "Welcome to our application",
    "tags": [1, 2]
}
```

#### Update Translation

```
PUT /api/translations/{id}
```

#### Delete Translation

```
DELETE /api/translations/{id}
```

#### Search Translations

```
GET /api/translations/search
```

Query parameters:

-   `key`: Search by key (partial match)
-   `content`: Search by content (partial match)
-   `language_id`: Filter by language ID
-   `tags`: Comma-separated tag IDs

### Export Endpoints

#### Export by Language

```
GET /api/export/language/{languageCode}
```

#### Export All

```
GET /api/export/all
```

#### Export by Tags

```
GET /api/export/tags?tags=web,mobile&language=en
```

## Performance Optimization

This service implements several performance optimization techniques:

1. **Caching**: Responses are cached to reduce database load
2. **Indexing**: Proper database indexes for faster queries
3. **Query optimization**: Efficient SQL queries with eager loading
4. **Pagination**: Results are paginated to handle large datasets
5. **Resource transformation**: Lightweight API resources

## Design Choices

-   **Service Pattern**: The application uses a service layer pattern to separate business logic from controllers
-   **Repository Pattern**: Not implemented as it would add unnecessary abstraction for this scale
-   **SOLID Principles**: Each class has a single responsibility and proper dependency injection
-   **Performance Focus**: The system is designed with performance in mind, with special attention to the JSON export endpoint

## Testing

Run the test suite:

```bash
php artisan test
```

Performance tests:

```bash
php artisan test --filter=PerformanceTest
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
