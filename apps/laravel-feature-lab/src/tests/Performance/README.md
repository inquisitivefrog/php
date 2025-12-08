# Performance Tests

This directory contains comprehensive performance tests for the Laravel application, measuring response times, query performance, and identifying potential bottlenecks.

## Test Suites

### 1. ApiPerformanceTest
Tests API endpoint response times and query counts.

**Tests:**
- Cow CRUD operations (index, show, create)
- Feature flag dashboard
- Queue stats
- Scout search
- Notification stats
- Concurrent requests
- Query count analysis

**Performance Thresholds:**
- Fast: < 100ms
- Acceptable: < 500ms
- Slow: > 1000ms

### 2. DatabasePerformanceTest
Tests database query performance and optimization.

**Tests:**
- Bulk insert performance
- Query optimization (inefficient vs efficient patterns)
- Pagination performance
- Complex queries (filters, ordering, limits)
- Transaction performance
- Count query performance
- Bulk update performance

**Performance Thresholds:**
- Fast: < 50ms
- Acceptable: < 200ms

### 3. QueuePerformanceTest
Tests queue job dispatch and processing performance.

**Tests:**
- Single job dispatch
- Batch job dispatch
- Chained job dispatch
- Multiple queue connections

**Performance Thresholds:**
- Fast: < 10ms
- Acceptable: < 50ms

### 4. SearchPerformanceTest
Tests Scout/Meilisearch search performance.

**Tests:**
- Basic search
- Paginated search
- Filtered search
- Bulk indexing
- Multiple model search
- Ordered search

**Performance Thresholds:**
- Fast: < 50ms
- Acceptable: < 200ms

### 5. FeatureFlagPerformanceTest
Tests Laravel Pennant feature flag evaluation performance.

**Tests:**
- Single feature flag check
- Multiple feature flag checks
- Feature flag value retrieval
- Feature flags for multiple users

**Performance Thresholds:**
- Fast: < 5ms
- Acceptable: < 20ms

### 6. NotificationPerformanceTest
Tests notification sending and queuing performance.

**Tests:**
- Single notification send
- Bulk notification send
- Queued notification dispatch
- Multi-channel notifications

**Performance Thresholds:**
- Fast: < 10ms
- Acceptable: < 50ms

## Running Performance Tests

### Run All Performance Tests
```bash
docker compose run --rm workspace php artisan test tests/Performance/
```

### Run Specific Test Suite
```bash
docker compose run --rm workspace php artisan test tests/Performance/ApiPerformanceTest.php
docker compose run --rm workspace php artisan test tests/Performance/DatabasePerformanceTest.php
docker compose run --rm workspace php artisan test tests/Performance/QueuePerformanceTest.php
docker compose run --rm workspace php artisan test tests/Performance/SearchPerformanceTest.php
docker compose run --rm workspace php artisan test tests/Performance/FeatureFlagPerformanceTest.php
docker compose run --rm workspace php artisan test tests/Performance/NotificationPerformanceTest.php
```

### Run Specific Test
```bash
docker compose run --rm workspace php artisan test --filter test_cow_index_performance
```

### Run with Verbose Output
```bash
docker compose run --rm workspace php artisan test tests/Performance/ --testdox
```

## Understanding Test Results

Each test outputs performance metrics in milliseconds:
- **Response time**: Time taken to complete the operation
- **Query count**: Number of database queries executed
- **Per-record time**: Average time per record for bulk operations

Example output:
```
✓ Cow index: 19.13ms (50 records)
✓ Bulk insert (100 records): 77.70ms (0.78ms per record)
✓ Feature flag check (1000 checks): 0.01ms average
```

## Performance Benchmarks

### API Endpoints
- **Fast**: < 100ms
- **Acceptable**: < 500ms
- **Slow**: > 1000ms (investigate)

### Database Operations
- **Simple queries**: < 50ms
- **Complex queries**: < 200ms
- **Bulk operations**: < 200ms per 100 records

### Queue Operations
- **Job dispatch**: < 10ms
- **Batch dispatch**: < 50ms per 50 jobs

### Search Operations
- **Basic search**: < 50ms
- **Indexing**: < 10ms per record

### Feature Flags
- **Single check**: < 5ms
- **Multiple checks**: < 20ms

### Notifications
- **Single send**: < 10ms
- **Bulk send**: < 50ms per 100 users

## Tips for Performance Optimization

1. **Database Queries**
   - Use eager loading to avoid N+1 queries
   - Use database aggregation instead of PHP loops
   - Add indexes for frequently queried columns
   - Use pagination for large datasets

2. **API Endpoints**
   - Cache frequently accessed data
   - Use database query optimization
   - Minimize response payload size
   - Use HTTP caching headers

3. **Queue Jobs**
   - Batch related jobs together
   - Use appropriate queue priorities
   - Monitor queue processing times

4. **Search**
   - Index only necessary fields
   - Use filters to narrow search scope
   - Paginate search results

5. **Feature Flags**
   - Cache feature flag values
   - Batch feature flag checks when possible

6. **Notifications**
   - Queue notifications for better performance
   - Use bulk notification sending when possible

## Continuous Performance Monitoring

Consider integrating these tests into your CI/CD pipeline to catch performance regressions early. You can set up alerts when performance thresholds are exceeded.

## Notes

- Performance tests use `RefreshDatabase` to ensure clean test data
- Some tests use `Queue::fake()` and `Notification::fake()` to avoid external dependencies
- Actual performance may vary based on system resources and database size
- These tests are designed for development/testing environments


