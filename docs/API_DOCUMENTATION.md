# API Documentation Guide

This guide explains how to manage and maintain the API documentation for the NGO Backend API using Scribe.

## Overview

The API documentation is automatically generated using [Scribe](https://scribe.knuckles.wtf/), a powerful Laravel package that creates beautiful, interactive API documentation.

## Documentation Features

- **Interactive Testing**: "Try It Out" buttons for testing endpoints directly from the documentation
- **Multiple Code Examples**: Request examples in bash (curl), JavaScript, PHP, and Python
- **Automatic Response Generation**: Scribe can call your endpoints to generate real responses
- **Postman Collection**: Automatically generated Postman collection for API testing
- **OpenAPI Specification**: Generated OpenAPI (Swagger) spec for integration with other tools

## File Structure

```
├── config/scribe.php                 # Main configuration file
├── resources/views/scribe/           # Generated Blade views (laravel type)
├── public/vendor/scribe/             # CSS and JS assets
├── storage/app/private/scribe/       # Generated files
│   ├── collection.json               # Postman collection
│   └── openapi.yaml                  # OpenAPI specification
└── .scribe/                          # Intermediate extraction files
    ├── endpoints/                    # Extracted endpoint data
    ├── intro.md                      # Introduction content
    └── auth.md                       # Authentication info
```

## Generating Documentation

### Basic Generation

```bash
php artisan scribe:generate
```

### Custom Environment

You can specify a custom environment file:

```bash
php artisan scribe:generate --env=docs
```

This is useful for customizing app behavior during documentation generation (e.g., disabling notifications).

## Adding Documentation to Controllers

### Basic Endpoint Documentation

```php
/**
 * List all organizations
 * 
 * Retrieve a list of all registered organizations in the system.
 * This endpoint provides information about NGOs including their basic details.
 *
 * @group Organizations
 * @unauthenticated
 * 
 * @response 200 scenario="Success" {
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "Green Earth Foundation",
 *       "slug": "green-earth-foundation"
 *     }
 *   ]
 * }
 */
public function index()
{
    // Method implementation
}
```

### Documenting Parameters

#### URL Parameters
```php
/**
 * @urlParam organization string required The slug of the organization. Example: green-earth-foundation
 */
```

#### Query Parameters
```php
/**
 * @queryParam include string Include related data (comma-separated). Example: posts,members
 * @queryParam page integer The page number for pagination. Example: 1
 * @queryParam limit integer Number of items per page. Example: 20
 */
```

#### Body Parameters
```php
/**
 * @bodyParam name string required The organization name. Example: Green Earth Foundation
 * @bodyParam email string required The contact email. Example: contact@greenearth.org
 * @bodyParam description string optional Organization description.
 */
```

### Authentication

#### Authenticated Endpoints
```php
/**
 * @authenticated
 */
```

#### Unauthenticated Endpoints
```php
/**
 * @unauthenticated
 */
```

### Response Examples

#### Success Response
```php
/**
 * @response 200 scenario="Success" {
 *   "data": {
 *     "id": 1,
 *     "name": "Green Earth Foundation"
 *   }
 * }
 */
```

#### Error Response
```php
/**
 * @response 404 scenario="Organization not found" {
 *   "message": "Organization not found"
 * }
 */
```

#### Multiple Responses
```php
/**
 * @response 200 scenario="Success" {"data": {...}}
 * @response 401 scenario="Unauthenticated" {"message": "Unauthenticated."}
 * @response 422 scenario="Validation error" {"message": "The given data was invalid.", "errors": {...}}
 */
```

## Grouping Endpoints

Organize endpoints into logical groups:

```php
/**
 * @group Authentication
 * @group User Management
 * @group Organizations
 * @group Posts
 * @group Comments & Likes
 * @group Skills & Locations
 * @group Pages
 * @group Support Tickets
 * @group Notifications
 */
```

## Configuration Tips

### Customizing Route Matching

In `config/scribe.php`, update the routes configuration:

```php
'routes' => [
    [
        'match' => [
            'prefixes' => ['api/v1/*'],
            'domains' => ['*'],
        ],
        'include' => [
            // Add specific routes that don't match prefixes
        ],
        'exclude' => [
            // Exclude specific routes (e.g., admin endpoints)
            'api/v1/admin/*'
        ],
    ],
],
```

### Authentication Setup

```php
'auth' => [
    'enabled' => true,
    'default' => false,
    'in' => 'bearer',
    'name' => 'Authorization',
    'use_value' => env('SCRIBE_AUTH_KEY', 'Bearer sample-token'),
    'placeholder' => 'Bearer {YOUR_AUTH_TOKEN}',
],
```

### Try It Out Configuration

```php
'try_it_out' => [
    'enabled' => true,
    'base_url' => null, // Uses app URL by default
    'use_csrf' => false, // Set to true if using Sanctum with CSRF
],
```

## Best Practices

### 1. Consistent Descriptions

- Use clear, concise descriptions for endpoints
- Follow a consistent format across all controllers
- Include context about what the endpoint does and when to use it

### 2. Realistic Examples

- Use realistic data in examples
- Ensure examples match your actual data structure
- Include edge cases in response examples

### 3. Proper Grouping

- Group related endpoints together
- Use descriptive group names
- Order groups logically in the configuration

### 4. Parameter Documentation

- Document all parameters with clear descriptions
- Include validation rules in descriptions
- Provide realistic example values

### 5. Response Coverage

- Document all possible responses (success and error)
- Include HTTP status codes
- Provide meaningful error messages

## Advanced Features

### Custom Strategies

You can create custom strategies for extracting information:

```php
// In config/scribe.php
'strategies' => [
    'metadata' => [
        // Custom strategy for extracting metadata
        App\Docs\Strategies\CustomMetadataStrategy::class,
    ],
],
```

### Response Calls

Scribe can make actual HTTP calls to generate responses:

```php
'strategies' => [
    'responses' => [
        // Only make response calls for GET endpoints
        Strategies\Responses\ResponseCalls::withSettings([
            'only' => ['GET *'],
            'config' => [
                'app.debug' => false,
            ]
        ]),
    ],
],
```

### Custom Assets

You can customize the CSS and JavaScript:

```php
'laravel' => [
    'assets_directory' => 'custom-docs-assets',
],
```

## Troubleshooting

### Common Issues

1. **Missing Parameters**: Ensure your Form Request classes have `bodyParameters()` methods
2. **Authentication Errors**: Check that `SCRIBE_AUTH_KEY` is set for authenticated endpoints
3. **CORS Issues**: Enable CORS headers for "Try It Out" functionality
4. **Missing Responses**: Use response calls or manual `@response` annotations

### Regeneration

If documentation seems outdated:

```bash
# Clear the .scribe folder and regenerate
rm -rf .scribe
php artisan scribe:generate
```

### Debugging

Enable debug mode to see more detailed output:

```bash
php artisan scribe:generate --verbose
```

## Deployment

### Static Documentation

If using static type, ensure the `public/docs` folder is deployed.

### Laravel Type

Ensure the following are deployed:
- `resources/views/scribe/`
- `public/vendor/scribe/`
- `storage/app/private/scribe/` (if you want Postman/OpenAPI files accessible)

### Environment Variables

Set appropriate values in production:

```env
SCRIBE_AUTH_KEY=your-production-token
APP_URL=https://your-production-domain.com
```

## Maintenance

### Regular Updates

1. Update documentation when adding new endpoints
2. Regenerate documentation after significant changes
3. Review and update response examples periodically
4. Keep configuration in sync with API changes

### Version Control

Consider committing the `.scribe` folder to maintain consistency across environments, or exclude it and regenerate on each deployment.

## Resources

- [Scribe Documentation](https://scribe.knuckles.wtf/laravel)
- [Scribe GitHub Repository](https://github.com/knuckleswtf/scribe)
- [OpenAPI Specification](https://spec.openapis.org/oas/v3.0.3)
- [Postman Collection Format](https://schema.postman.com/)
