
Project: a working application "Laravel Feature Lab" to demonstrate PHP Laravel

Core Setup
----------
1. PHP 8.3
2. Composer
3. Laravel 11
4. PostgreSQL 16
5. Redis (for Horizon, queues, caching)
6. Docker + Docker Compose 

Laravel Features Examined
-------------------------
1. Breeze:	         Minimal auth scaffolding
2. Pennant:	         Feature flagging	
3. Cashier (Stripe): Subscription billing
4. Horizon:          Queue dashboard & supervisor	
5. Sanctum:          Lightweight API token auth
6. Scout             Search abstraction
7. Telescope	     Debug dashboard	
8. Notifications	 Email, Slack, SMS

Security Hardening
------------------
PHP-FPM container
1. PHP 8.3+
2. Non-root user
3. Opcache enabled
4. Necessary extensions installed
5. Composer only during build (not in final image)
6. No dev tools in production images
7. Multi-stage builds
8. Use pinned minor versions
9. Disable pdo_mysql

Laravel containers
1. Use APP_KEY rotation mechanism
2. Use hashed or encrypted environment variables for secrets
3. Move config values to config/ files, not controllers
4. Enforce HTTPS and use HSTS
5. Use rate limiting middleware for public endpoints
6. Set SESSION_SECURE_COOKIE=true
7. Use csrf protection for all form-based POSTs
8. Disable debugging except in dev (APP_DEBUG=false in prod)

Nginx container
1. Reverse proxy
2. TLS termination
3. Hardened config (no autoindex, restrictive MIME types)
4. Deny execution in /storage and /public/uploads in Nginx

PostgreSQL (preferred over MySQL)
1. Stronger datatypes
2. Better JSON handling
3. Native UUID v4/v7 support

Redis
1. for Cache
2. for Queues
3. for Rate limiting
4. for Session handling

Queue Worker
1. php artisan queue:work in a dedicated container
2. Auto-restart on failure

Scheduler / Cron container
1. php artisan schedule:run every minute
2. Lightweight Alpine-based container

Laravel Development Components
------------------------------
Breeze
1. starter for auth scaffolding
2. API + Blade + Inertia + React/Vue

Laravel Cashier (Stripe)
1. subscription billing, metered billing, SCA handling

Laravel Pennant (Feature Flags)
1. gradual rollouts
2. A/B testing
3. blue/green deployment
4. per-user or per-team feature gates

Laravel Pint
1. Modern formatter

Laravel Sail
1. 

Laravel Telescope
1. Real-time debugging dashboard
2. Requests
3. Commands
4. Queues
5. Exceptions
6. DB queries
7. Cache operations

Log Viewer
1. browse Laravel logs from browser

Xdebug
1. Step debugging
2. Breakpoints
3. Debugging inside VSCode / PHPStorm
4. separate override compose file used only on demand

Automated Testing
-----------------
1. HTTP tests
2. Database transactions
3. Mocking
4. Pest support

Static Analysis
---------------
1. PHPStan
2. Larastan

