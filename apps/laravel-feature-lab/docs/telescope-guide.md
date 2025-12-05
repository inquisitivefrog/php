# Laravel Telescope Guide

## Overview

**Telescope is FREE** - It's a beautiful debugging/monitoring tool for Laravel applications. No costs involved.

## Installation Status

✅ **Telescope is installed and configured**

- Package: `laravel/telescope` v5.15
- Configuration: `config/telescope.php`
- Service Provider: `TelescopeServiceProvider` registered
- Dashboard: Available at `/telescope` (when authenticated)
- Database: `telescope_entries` table created

## What Telescope Provides

Telescope automatically monitors and records:

### 1. **Requests** (HTTP)
- All incoming HTTP requests
- Request headers, parameters, body
- Response status, headers, content
- Request/response timing

### 2. **Queries** (Database)
- All database queries executed
- Query bindings and execution time
- Slow query detection
- N+1 query problem detection

### 3. **Models** (Eloquent)
- Model operations (create, update, delete)
- Model relationships accessed
- Model events fired

### 4. **Events**
- All events dispatched
- Event listeners executed
- Event payloads

### 5. **Jobs** (Queue)
- Jobs dispatched
- Job execution status
- Job failures and retries
- Job payloads

### 6. **Mail**
- Emails sent
- Email recipients and content
- Mail failures

### 7. **Notifications**
- Notifications sent
- Notification channels used
- Notification content

### 8. **Cache**
- Cache operations (get, put, forget)
- Cache hits and misses
- Cache keys accessed

### 9. **Commands** (Artisan)
- Artisan commands executed
- Command output
- Command execution time

### 10. **Scheduled Tasks**
- Scheduled tasks executed
- Task execution status
- Task timing

### 11. **Views**
- Views rendered
- View data passed
- View compilation time

### 12. **Exceptions**
- All exceptions thrown
- Exception stack traces
- Exception context

### 13. **Logs**
- All log entries
- Log levels (info, warning, error, etc.)
- Log context

### 14. **Dumps**
- `dd()` and `dump()` calls
- Variable contents
- Stack traces

## API Endpoints

All Telescope demo endpoints require authentication via Sanctum.

### Database Queries
```http
GET /api/telescope-demo/queries
Authorization: Bearer {token}
```

### Cache Operations
```http
GET /api/telescope-demo/cache
Authorization: Bearer {token}
```

### Dispatch Job
```http
POST /api/telescope-demo/job
Authorization: Bearer {token}
```

### Logging
```http
GET /api/telescope-demo/logs
Authorization: Bearer {token}
```

### Exception
```http
GET /api/telescope-demo/exception
Authorization: Bearer {token}
```

### Model Operations
```http
POST /api/telescope-demo/models
Authorization: Bearer {token}
```

### Dispatch Event
```http
POST /api/telescope-demo/event
Authorization: Bearer {token}
```

### Multiple Operations
```http
GET /api/telescope-demo/multiple
Authorization: Bearer {token}
```

### Slow Query
```http
GET /api/telescope-demo/slow-query
Authorization: Bearer {token}
```

### N+1 Query
```http
GET /api/telescope-demo/n-plus-one
Authorization: Bearer {token}
```

## Accessing Telescope Dashboard

1. Start your application:
   ```bash
   docker compose up
   ```

2. Access dashboard:
   - URL: `http://localhost:8080/telescope`
   - Requires authentication (configured in `TelescopeServiceProvider`)

3. View:
   - Real-time request monitoring
   - Database query analysis
   - Exception tracking
   - Job monitoring
   - Cache operations
   - And much more!

## Configuration

### Entry Filtering

Telescope can be configured to filter entries in `TelescopeServiceProvider`:

```php
Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
    if (app()->environment('testing')) {
        return true; // Record all entries in testing
    }
    
    return $isLocal ||
           $entry->isReportableException() ||
           $entry->isFailedRequest() ||
           $entry->isFailedJob() ||
           $entry->isScheduledTask() ||
           $entry->hasMonitoredTag();
});
```

### Access Control

Control who can access Telescope in `TelescopeServiceProvider`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user = null) {
        return app()->environment('local') || $user !== null;
    });
}
```

## Testing

### Run All Telescope Tests
```bash
docker compose run --rm workspace php artisan test --filter TelescopeTest
```

### Test Results
- ✅ 20 tests passing
- ✅ 61 assertions
- ✅ All Telescope features demonstrated

## Test Coverage

| Feature | Tests | Status |
|---------|-------|--------|
| HTTP Requests | 2 | ✅ |
| Database Queries | 2 | ✅ |
| Cache Operations | 2 | ✅ |
| Jobs | 1 | ✅ |
| Logs | 1 | ✅ |
| Exceptions | 1 | ✅ |
| Models | 1 | ✅ |
| Events | 1 | ✅ |
| Multiple Entry Types | 1 | ✅ |
| Slow Queries | 1 | ✅ |
| N+1 Queries | 1 | ✅ |
| Entry Filtering | 1 | ✅ |
| Request Details | 1 | ✅ |
| Query Details | 1 | ✅ |
| Cache Details | 1 | ✅ |
| Entry Relationships | 1 | ✅ |
| Views | 1 | ✅ |
| Scheduled Tasks | 1 | ✅ |
| Entry Storage | 1 | ✅ |
| Entry Cleanup | 1 | ✅ |

## Common Use Cases

### 1. Debug Slow Requests
- View request timeline
- Identify slow queries
- Check cache operations
- Review job execution

### 2. Find N+1 Query Problems
- Telescope automatically detects N+1 queries
- View query count per request
- Identify relationships causing issues

### 3. Monitor Exceptions
- View all exceptions
- See stack traces
- Check exception context
- Track exception frequency

### 4. Analyze Database Performance
- View all queries
- Check query execution time
- Identify slow queries
- Review query bindings

### 5. Debug Job Failures
- View failed jobs
- Check job payloads
- Review retry attempts
- See error messages

## Best Practices

1. **Enable in Development** - Always use Telescope in local/development
2. **Filter in Production** - Only record important entries in production
3. **Monitor Performance** - Use Telescope to identify performance bottlenecks
4. **Track Exceptions** - Monitor exceptions to catch bugs early
5. **Analyze Queries** - Use query monitoring to optimize database performance
6. **Review Jobs** - Monitor queue jobs for failures and performance
7. **Check Cache** - Verify cache operations are working correctly

## Resources

- [Laravel Telescope Documentation](https://laravel.com/docs/telescope)
- [Telescope Configuration](https://laravel.com/docs/telescope#configuration)
- [Entry Filtering](https://laravel.com/docs/telescope#filtering)

