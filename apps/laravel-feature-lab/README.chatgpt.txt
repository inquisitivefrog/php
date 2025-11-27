
What to demonstrate & how to test each feature

Use Pest (recommended) or PHPUnit for tests. Where possible prefer feature tests that exercise HTTP endpoints and unit tests for business logic.

User Authentication — Breeze / Fortify
Tests: registration, email verification, login, password reset, rate-limiting.
How to test: hit the auth routes with HTTP tests; assert Auth::check() and DB rows; test middleware-protected pages.

Subscriptions — Cashier (Stripe)
Tests: subscribe/unsubscribe flow, webhooks handling, plan access.
How to test: use fake data and stub Stripe API (or use Stripe test keys + stripe-mock); assert subscriptions() records and plan gates.

Feature Flags — Pennant
Tests: per-user/per-role feature toggles; middleware tests asserting feature gating.
How to test: toggle flags programmatically in tests and assert behavior differences.

Email / Notifications
Tests: mail is queued/sent (use Mailtrap/Mailpit locally), notification content.
How to test: Notification::fake() / Mail::fake() then assert Mail::assertSent() or inspect Mailpit.

Queues / Jobs
Tests: dispatching and processing jobs, retry/backoff behavior.
How to test: use Queue::fake() or run with QUEUE_CONNECTION=redis and worker in CI to ensure real processing.

Caching
Tests: caching of user/profile/plan data, cache invalidation.
How to test: assert cache keys using Cache::shouldReceive() or real Redis in integration tests.

Jobs & Commands
Tests: scheduled command behavior (use artisan schedule:run --once in integration).

Policies / Gates
Tests: unit tests for policy methods and feature tests for endpoints protected by policies.

Factories & Seeders
Tests: factories produce valid models; seeder creates demo dataset. Use RefreshDatabase trait.

Frontend (Blade + Vite)
Tests: basic e2e tests (Playwright or Cypress) to exercise SPA flows (auth, dashboard, subscriptions).

Testing
Use Pest for clean syntax; keep PHPUnit config for CI compatibility.

=====================================================================

Recommended toolchain (local dev + CI)
PHP & Laravel: PHP 8.2 or 8.3 (match what your Dockerfile uses). Composer for dependencies.
Testing: Pest + PHPUnit, Laravel Dusk or Playwright for browser e2e.
Static analysis: PHPStan (level 8-9 for strictness) and/or Psalm — run in CI and locally.
Type checks: PHPStan/Psalm with baseline for legacy code.
Coding style / lint: PHP CS Fixer or phpcs (PSR-12) and pre-commit hooks with Husky (for JS) / pre-commit (for PHP).
Security / dependency checks:
composer audit (Composer v2+ has audit), roave/security-advisories in composer require-dev, and optionally Snyk or GitHub Dependabot.
Container image scanner: Trivy.
Secrets & secret scanning: GitHub secret scanning or git-secrets in pre-commit.
Container best-practices: Hadolint (Dockerfile linter).
Profiling / memory leaks: Xdebug for dev, Blackfire or Tideways for profiling. Use php-meminfo if needed.
Load testing: k6 (modern), vegeta, or wrk. For CI smoke tests, k6 with a small script.
CI runner: GitHub Actions, GitLab CI, or Drone (example below is GitHub Actions).
Code coverage: Xdebug + coverage reporting to Codecov or Coveralls.

Docker / runtime hardening & best practices
Build images with multi-stage builds; final image should be non-root (create www user).
Avoid latest tags for production; pin to specific versions.
Set APP_ENV=production and APP_DEBUG=false in prod.
Use secrets (GitHub/GitLab secrets) for keys; never commit .env.
Limit container capabilities and use read-only filesystems where possible.
Ensure storage and bootstrap/cache have correct ownership and restricted permissions (e.g., 750).
Scan images with Trivy on CI and fail on high severity findings.
Reduce image size: remove build tools in final stage, use composer install --no-dev --optimize-autoloader.

CI pipeline — what to run (order)
Build / pull services (Postgres, Redis, Meilisearch optionally) — run tests against these.
composer install --no-interaction --prefer-dist
Static analysis: PHPStan (fail on high errors)
Linting and style fixes: phpcs/php-cs-fixer (report-only or auto-fix in a separate job)
Unit & Feature tests: Pest/PHPUnit (with parallelization where possible)
Integration tests: jobs, queues, db migrations, Meili search if used
Security checks: composer audit, Trivy scan
Build frontend assets with Vite (CI artefact)
Optional: Upload coverage report and artifact (for debugging failures)
Optional: run a small k6 load smoke test against test app (on merge to main)

Required PHP Development Tools (with reasons)
✔ Pest
Clean, modern testing framework. Works with PHPUnit under the hood.
✔ PHPStan
Static analysis (finds type errors, unreachable code, undefined vars, bugs).
✔ Larastan
PHPStan extension specifically for Laravel (adds model magic, container awareness).
✔ Laravel Pint
Official Laravel code style formatter.
✔ PHP_CodeSniffer (phpcs)
Enforces style consistency and security patterns.
PHP CS Fixer
Optional, but more flexible than Pint.
✔ Trivy
Security scanning for Docker images and OS packages.
✔ K6 (Grafana k6)
Load testing tool recommended in 2025-era DevOps.

--------------------------------------------------
Direct dependencies
You currently have:
✔ Laravel Framework 12
✔ PHPUnit 11
✔ PHPStan 2.x
✔ Larastan 3.8 (correct modern package)
✔ Pint
✔ PHP-CS-Fixer
✔ CodeSniffer
✔ Pail
✔ Sail
✔ Collision
✔ Faker
✔ Mockery
This is exactly what a 2025-grade Laravel dev environment should look like.
