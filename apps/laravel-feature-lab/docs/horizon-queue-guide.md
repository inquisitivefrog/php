# Laravel Horizon & Queue System Guide

## Overview

**Horizon is FREE** - It's a beautiful dashboard for monitoring your Laravel queues. No costs involved.

## Installation Status

✅ **Horizon is installed and configured**

- Package: `laravel/horizon` v5.40
- Configuration: `config/horizon.php`
- Service Provider: `HorizonServiceProvider` registered
- Dashboard: Available at `/horizon` (when authenticated)

## What Horizon Provides

Horizon gives you a real-time dashboard to monitor:
- **Queue Jobs** - See jobs being processed in real-time
- **Job Metrics** - Throughput, wait times, processing times
- **Failed Jobs** - View and retry failed jobs
- **Workers** - Monitor queue workers
- **Job Tags** - Filter and search jobs
- **Recent Jobs** - View job history
- **Metrics** - Performance statistics

## Queue Features Demonstrated

### 1. Basic Job Dispatching
- Simple queued jobs
- Job execution
- Queue assignment

### 2. Queue Names
- Organizing jobs into queues (`emails`, `images`, `reports`)
- Priority queues
- Queue-specific workers

### 3. Job Delays
- Delayed execution
- Scheduled jobs
- Time-based job processing

### 4. Job Retries
- Automatic retries
- Retry attempts (`$tries`)
- Exponential backoff (`$backoff`)

### 5. Job Timeouts
- Timeout configuration
- Long-running job handling

### 6. Job Failures
- Failure handling
- `failed()` method
- Failed job storage

### 7. Job Chaining
- Dependent jobs
- Sequential execution
- Chain callbacks

### 8. Job Batching
- Batch processing
- Batch callbacks (`then()`, `catch()`, `finally()`)
- Batch cancellation

### 9. Queue Connections
- Multiple connections (database, redis, sync)
- Connection-specific queues

### 10. Job Priorities
- Priority queues
- Queue ordering

## Job Examples Created

### 1. `TestJob` - Basic Job
- Simple job execution
- Logging

### 2. `ProcessEmailJob` - Email Processing
- Queue: `emails`
- Retries: 3
- Backoff: 60 seconds

### 3. `ProcessImageJob` - Image Processing
- Queue: `images`
- Timeout: 120 seconds
- Retries: 2

### 4. `GenerateReportJob` - Report Generation
- Queue: `reports`
- Unique jobs
- Long-running

### 5. `FailedJobExample` - Failure Testing
- Intentional failures
- Retry logic
- Failure callbacks

### 6. `ChainedJob` - Job Chaining
- Sequential execution
- Dependent jobs

### 7. `BatchableJob` - Batch Processing
- Batch operations
- Batch cancellation handling

### 8. `DelayedJob` - Delayed Execution
- Scheduled jobs
- Time-based processing

## API Endpoints

All queue endpoints require authentication via Sanctum.

### Dispatch Test Job
```http
POST /api/queue/test
Authorization: Bearer {token}
```

### Dispatch Email Job
```http
POST /api/queue/email
Authorization: Bearer {token}
Content-Type: application/json

{
  "to": "user@example.com",
  "subject": "Test Email",
  "body": "Email content"
}
```

### Dispatch Delayed Job
```http
POST /api/queue/delayed
Authorization: Bearer {token}
Content-Type: application/json

{
  "message": "Delayed message",
  "delay_seconds": 60
}
```

### Dispatch Chained Jobs
```http
POST /api/queue/chain
Authorization: Bearer {token}
```

### Dispatch Batch Jobs
```http
POST /api/queue/batch
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": ["item1", "item2", "item3"]
}
```

### Dispatch Failed Job (for testing)
```http
POST /api/queue/failed
Authorization: Bearer {token}
```

### Get Queue Statistics
```http
GET /api/queue/stats
Authorization: Bearer {token}
```

## Accessing Horizon Dashboard

1. Start Horizon worker:
   ```bash
   docker compose run --rm workspace php artisan horizon
   ```

2. Access dashboard:
   - URL: `http://localhost:8080/horizon`
   - Requires authentication (if not in local environment)

3. View:
   - Real-time job processing
   - Queue metrics
   - Failed jobs
   - Worker status

## Queue Configuration

### Default Connection
Set in `.env`:
```env
QUEUE_CONNECTION=redis
```

Options:
- `sync` - Execute immediately (no queue)
- `database` - Store jobs in database
- `redis` - Use Redis (recommended for Horizon)

### Queue Workers

Docker Compose includes a queue worker:
```yaml
queue:
  command: php artisan queue:work --sleep=3 --tries=3
```

For Horizon:
```bash
php artisan horizon
```

## Testing

### Run All Queue Tests
```bash
docker compose run --rm workspace php artisan test --filter HorizonQueueTest
```

### Test Results
- ✅ 26 tests passing
- ✅ 35 assertions
- ✅ All queue features demonstrated

## Test Coverage

| Feature | Tests | Status |
|---------|-------|--------|
| Basic Dispatching | 1 | ✅ |
| Queue Names | 2 | ✅ |
| Job Delays | 2 | ✅ |
| Retries & Backoff | 1 | ✅ |
| Timeouts | 1 | ✅ |
| Failures | 1 | ✅ |
| Job Chaining | 1 | ✅ |
| Job Batching | 2 | ✅ |
| Queue Connections | 2 | ✅ |
| Job Priorities | 1 | ✅ |
| Unique Jobs | 1 | ✅ |
| Event Listeners | 1 | ✅ |
| Middleware | 1 | ✅ |
| Synchronous Execution | 1 | ✅ |
| After Commit | 1 | ✅ |
| Job Tags | 1 | ✅ |
| Multiple Job Types | 1 | ✅ |
| Model Serialization | 1 | ✅ |

## Common Queue Patterns

### 1. Simple Job
```php
TestJob::dispatch();
```

### 2. Job with Queue
```php
ProcessEmailJob::dispatch($to, $subject, $body);
// Automatically goes to 'emails' queue
```

### 3. Delayed Job
```php
DelayedJob::dispatch('message')->delay(now()->addMinutes(5));
```

### 4. Job on Specific Connection
```php
TestJob::dispatch()->onConnection('redis');
```

### 5. Chained Jobs
```php
Bus::chain([
    new ChainedJob(1, 'data1'),
    new ChainedJob(2, 'data2'),
])->dispatch();
```

### 6. Batch Jobs
```php
Bus::batch([
    new BatchableJob(1, 'item1'),
    new BatchableJob(2, 'item2'),
])
    ->then(function (Batch $batch) {
        // All jobs completed
    })
    ->catch(function (Batch $batch, \Throwable $e) {
        // One or more jobs failed
    })
    ->dispatch();
```

## Horizon Dashboard Features

### Monitoring
- Real-time job processing
- Queue throughput
- Job wait times
- Processing times

### Management
- Retry failed jobs
- Delete failed jobs
- Pause/Resume queues
- View job details

### Metrics
- Jobs per minute
- Throughput
- Wait times
- Processing times

## Best Practices

1. **Use Queue Names** - Organize jobs by type (`emails`, `images`, etc.)
2. **Set Appropriate Timeouts** - Prevent jobs from hanging
3. **Configure Retries** - Handle transient failures
4. **Use Batching** - Process related jobs together
5. **Monitor with Horizon** - Keep an eye on queue health
6. **Handle Failures** - Implement `failed()` methods
7. **Use Delays** - Schedule jobs appropriately

## Resources

- [Laravel Queues Documentation](https://laravel.com/docs/queues)
- [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
- [Queue Configuration](https://laravel.com/docs/queues#configuration)

