# Laravel Scout Guide

## Overview

**Scout is FREE**, and **Meilisearch (the search backend) is FREE and open-source**. No costs involved.

## Installation Status

✅ **Scout is installed and configured**

- Package: `laravel/scout` v10.22
- Meilisearch PHP Client: `meilisearch/meilisearch-php` v1.16
- Configuration: `config/scout.php`
- Search Backend: Meilisearch (running in Docker)
- Models: `Cow` and `User` are searchable

## What Scout Provides

Scout provides full-text search capabilities for Eloquent models with support for:

### Supported Search Engines

1. **Meilisearch** (Default) - Free, open-source, fast
2. **Algolia** - Paid service with free tier
3. **Typesense** - Free, open-source
4. **Database** - Built-in database search
5. **Collection** - For testing (no external service)

## Features Demonstrated

### 1. Basic Search
- Simple search queries
- Full-text search across model attributes
- Case-insensitive search

### 2. Paginated Search
- Search results with pagination
- Configurable results per page
- Pagination metadata

### 3. Filtered Search
- Search with additional filters
- Combine search with database queries
- Filter by model attributes

### 4. Field-Specific Search
- Search within specific fields
- Custom searchable arrays
- Index configuration

### 5. Ordered Search
- Sort search results
- Multiple sort criteria
- Ascending/descending order

### 6. Bulk Indexing
- Import all models to search index
- Batch indexing operations
- Queue support for large datasets

### 7. Index Management
- Add models to search index
- Remove models from search index
- Clear entire search index

## API Endpoints

All Scout demo endpoints require authentication via Sanctum.

### Basic Search
```http
POST /api/scout-demo/search
Authorization: Bearer {token}
Content-Type: application/json

{
  "q": "search term",
  "model": "cows"
}
```

### Paginated Search
```http
POST /api/scout-demo/search/paginated
Authorization: Bearer {token}
Content-Type: application/json

{
  "q": "search term",
  "model": "cows",
  "per_page": 15
}
```

### Filtered Search
```http
POST /api/scout-demo/search/filtered
Authorization: Bearer {token}
Content-Type: application/json

{
  "q": "search term",
  "model": "cows"
}
```

### Field-Specific Search
```http
POST /api/scout-demo/search/field
Authorization: Bearer {token}
Content-Type: application/json

{
  "q": "search term",
  "model": "cows",
  "field": "name"
}
```

### Ordered Search
```http
POST /api/scout-demo/search/ordered
Authorization: Bearer {token}
Content-Type: application/json

{
  "q": "search term",
  "model": "cows",
  "order_by": "name",
  "order_direction": "asc"
}
```

### Import All Models
```http
POST /api/scout-demo/import
Authorization: Bearer {token}
Content-Type: application/json

{
  "model": "cows"
}
```

### Remove All Models
```http
POST /api/scout-demo/remove
Authorization: Bearer {token}
Content-Type: application/json

{
  "model": "cows"
}
```

### Get Statistics
```http
GET /api/scout-demo/stats
Authorization: Bearer {token}
```

## Configuration

### Environment Variables

```env
SCOUT_DRIVER=meilisearch
SCOUT_PREFIX=
SCOUT_QUEUE=false
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=
```

### Scout Configuration

Located in `config/scout.php`:

- **Driver**: `meilisearch` (default: `collection` for testing)
- **Prefix**: Optional prefix for index names
- **Queue**: Enable queued indexing for better performance
- **Chunk Size**: Batch size for bulk operations

## Model Configuration

### Making Models Searchable

Add the `Searchable` trait to your model:

```php
use Laravel\Scout\Searchable;

class Cow extends Model
{
    use Searchable;
}
```

### Custom Searchable Array

Define what data is indexed:

```php
public function toSearchableArray(): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'breed' => $this->breed,
        // ... other searchable fields
    ];
}
```

### Custom Index Name

```php
public function searchableAs(): string
{
    return 'cows';
}
```

## Usage Examples

### Basic Search

```php
$results = Cow::search('Bessie')->get();
```

### Paginated Search

```php
$results = Cow::search('Holstein')->paginate(15);
```

### Search with Filters

```php
$results = Cow::search('Bessie')
    ->where('breed', 'Holstein')
    ->get();
```

### Make Model Searchable

```php
$cow->searchable();
```

### Remove from Search Index

```php
$cow->unsearchable();
```

### Bulk Import

```php
Cow::makeAllSearchable();
```

### Bulk Remove

```php
Cow::removeAllFromSearch();
```

## Testing

### Run All Scout Tests
```bash
docker compose run --rm workspace php artisan test --filter ScoutTest
```

### Test Results
- ✅ 20 tests passing
- ✅ 33 assertions
- ✅ All Scout features demonstrated

## Test Coverage

| Feature | Tests | Status |
|---------|-------|--------|
| Basic Search | 1 | ✅ |
| Paginated Search | 1 | ✅ |
| Empty Results | 1 | ✅ |
| Case-Insensitive | 1 | ✅ |
| Make Searchable | 1 | ✅ |
| Bulk Indexing | 1 | ✅ |
| Remove from Index | 1 | ✅ |
| Bulk Remove | 1 | ✅ |
| Multiple Models | 1 | ✅ |
| Partial Matches | 1 | ✅ |
| Custom Searchable Array | 1 | ✅ |
| Custom Index Name | 1 | ✅ |
| Search with Where | 1 | ✅ |
| Model Relationships | 1 | ✅ |
| Driver Configuration | 1 | ✅ |
| Empty Query | 1 | ✅ |
| Index Prefix | 1 | ✅ |
| Queue Configuration | 1 | ✅ |
| Special Characters | 1 | ✅ |
| Performance | 1 | ✅ |

## Meilisearch Setup

Meilisearch is already running in Docker:

```yaml
meilisearch:
  image: getmeili/meilisearch:v1.6
  ports:
    - "7700:7700"
```

### Access Meilisearch Dashboard

- URL: `http://localhost:7700`
- No authentication required in development

## Artisan Commands

### Import All Models

```bash
php artisan scout:import "App\Models\Cow"
```

### Flush All Models

```bash
php artisan scout:flush "App\Models\Cow"
```

## Best Practices

1. **Use Queued Indexing** - Enable `SCOUT_QUEUE=true` for better performance
2. **Index Only What's Needed** - Customize `toSearchableArray()` to index only relevant fields
3. **Use Prefixes** - Use index prefixes for multi-tenant applications
4. **Monitor Performance** - Use Meilisearch dashboard to monitor search performance
5. **Configure Filters** - Set up filterable attributes in Meilisearch for advanced filtering
6. **Handle Errors** - Implement error handling for search operations
7. **Test Thoroughly** - Use collection driver for fast tests

## Resources

- [Laravel Scout Documentation](https://laravel.com/docs/scout)
- [Meilisearch Documentation](https://www.meilisearch.com/docs)
- [Scout Configuration](https://laravel.com/docs/scout#configuration)

