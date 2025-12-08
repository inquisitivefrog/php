# Service Usage Review

## Services Status

### ✅ **app** (PHP-FPM Runtime)
**Status**: ✅ **FULLY USED**
- All PHP application code runs in this container
- All controllers, models, jobs, notifications use this service
- **Usage**: Every request, every job, every notification

### ✅ **mailpit** (Email Testing)
**Status**: ✅ **FULLY USED**
- Configured in `docker-compose.yml`: `MAIL_HOST: mailpit`
- Used by all email notifications:
  - `WelcomeEmailNotification`
  - `TaskAssignedNotification`
  - `OrderConfirmationNotification`
  - `SystemAlertNotification`
- **Usage**: All email notifications are sent to Mailpit (port 8025)
- **Code**: `src/app/Notifications/*.php`, `src/app/Http/Controllers/NotificationDemoController.php`

### ✅ **meilisearch** (Search Backend)
**Status**: ✅ **FULLY USED**
- Configured in `docker-compose.yml`: `MEILISEARCH_HOST: http://meilisearch:7700`
- Used by Laravel Scout for full-text search
- Models using Scout:
  - `Cow` model (searchable)
  - `User` model (searchable)
- **Usage**: All Scout search operations
- **Code**: `src/app/Http/Controllers/ScoutDemoController.php`, `src/app/Models/Cow.php`, `src/app/Models/User.php`

### ✅ **nginx** (Web Server)
**Status**: ✅ **FULLY USED**
- Serves all HTTP requests
- Reverse proxy to PHP-FPM (app container)
- **Usage**: Every HTTP request to the application
- **Configuration**: `docker/nginx/default.conf`

### ✅ **postgres** (Database)
**Status**: ✅ **FULLY USED**
- Configured in `docker-compose.yml`: `DB_CONNECTION: pgsql`, `DB_HOST: postgres`
- Used by:
  - All Eloquent models (`User`, `Cow`)
  - All migrations
  - Feature flags (Pennant)
  - Subscriptions (Cashier)
  - Telescope entries
  - Cache table (optional)
  - Sessions (if using database driver)
- **Usage**: All database operations
- **Code**: All models, migrations, and database queries throughout the application

### ✅ **queue** (Queue Worker)
**Status**: ✅ **FULLY USED**
- Configured in `docker-compose.yml`: `QUEUE_CONNECTION: redis`
- Running: `php artisan queue:work --sleep=3 --tries=3`
- Processes queued jobs:
  - `TestJob`
  - `ProcessEmailJob`
  - `ProcessImageJob`
  - `DelayedJob`
  - `GenerateReportJob`
  - `ChainedJob`
  - `BatchableJob`
  - `FailedJobExample`
  - Queued notifications (all notifications implement `ShouldQueue`)
- **Usage**: All background job processing
- **Code**: `src/app/Jobs/*.php`, `src/app/Notifications/*.php`, `src/app/Http/Controllers/QueueDemoController.php`

### ✅ **redis** (Cache, Sessions, Queues)
**Status**: ✅ **FULLY USED**
- Configured in `docker-compose.yml`:
  - `CACHE_DRIVER: redis`
  - `SESSION_DRIVER: redis`
  - `QUEUE_CONNECTION: redis`
  - `REDIS_HOST: redis`
- Used for:
  - **Cache**: All `Cache::` operations, feature flag caching (Pennant)
  - **Sessions**: All user sessions
  - **Queues**: All job queue storage
  - **Rate Limiting**: API rate limiting
- **Usage**: Cache operations, session storage, queue storage
- **Code**: Used throughout the application via Laravel facades

### ✅ **scheduler** (Cron Scheduler)
**Status**: ✅ **FULLY USED**
- Container is running: `php artisan schedule:work`
- **Scheduled Tasks** (defined in `routes/console.php`):
  1. **Hourly Health Check** - Application health monitoring and cache warming
  2. **Daily Telescope Cleanup** - Prune old Telescope entries (keep last 7 days)
  3. **Daily Activity Report** - Generate daily statistics (users, cows count)
  4. **Weekly Maintenance** - Database optimization and cache cleanup
  5. **Queue Health Check** - Monitor queue status every 5 minutes
  6. **Daily Notifications** - Scheduled notification digest (placeholder)
  7. **Cache Statistics** - Update cache health stats hourly
- **Usage**: All scheduled tasks run automatically via the scheduler container
- **Code**: `src/routes/console.php`

### ✅ **workspace** (Development Container)
**Status**: ✅ **FULLY USED**
- Development environment with Composer, Node, Vite
- Used for:
  - Running artisan commands
  - Running tests
  - Installing packages
  - Development tooling
- **Usage**: All development activities

---

## Summary

| Service | Status | Usage |
|---------|--------|-------|
| **app** | ✅ Used | All PHP code execution |
| **mailpit** | ✅ Used | Email notifications |
| **meilisearch** | ✅ Used | Scout search operations |
| **nginx** | ✅ Used | HTTP request serving |
| **postgres** | ✅ Used | All database operations |
| **queue** | ✅ Used | Background job processing |
| **redis** | ✅ Used | Cache, sessions, queues |
| **scheduler** | ✅ Used | 7 scheduled tasks running |
| **workspace** | ✅ Used | Development environment |

---

## All Services: ✅ Complete

All 9 services (app, mailpit, meilisearch, nginx, postgres, queue, redis, scheduler, workspace) are fully integrated and actively used by the application code.

### Scheduled Tasks Overview

View all scheduled tasks:
```bash
docker compose run --rm workspace php artisan schedule:list
```

Run scheduler manually (for testing):
```bash
docker compose run --rm workspace php artisan schedule:work
```

**Scheduled Tasks:**
1. **health-check** - Runs hourly, monitors application health
2. **telescope-cleanup** - Runs daily at 2:00 AM, prunes old Telescope entries
3. **daily-report** - Runs daily at 3:00 AM, generates activity statistics
4. **weekly-maintenance** - Runs weekly on Sundays at 4:00 AM, performs maintenance
5. **queue-health-check** - Runs every 5 minutes, monitors queue status
6. **daily-notifications** - Runs daily at 9:00 AM, sends notification digest
7. **cache-stats** - Runs hourly, updates cache statistics

