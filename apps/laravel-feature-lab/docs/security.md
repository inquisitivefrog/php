# Security Hardening Guidelines

## PHP-FPM Container
- PHP 8.3+
- Non-root user
- Opcache enabled
- Only necessary extensions installed
- Composer used only during build
- No dev tools in production
- Multi-stage builds
- Use pinned minor versions
- Disable `pdo_mysql`

## Laravel Application
- Rotate APP_KEY regularly
- Use hashed or encrypted environment variables for secrets
- Move config values to `config/` files
- Enforce HTTPS & HSTS
- Apply rate limiting on public endpoints
- `SESSION_SECURE_COOKIE=true`
- CSRF protection on form POSTs
- Disable debugging in production (`APP_DEBUG=false`)

## Nginx
- Reverse proxy with TLS termination
- Hardened config (no autoindex, restrictive MIME types)
- Deny execution in `/storage` and `/public/uploads`

## PostgreSQL
- Stronger datatypes than MySQL
- Better JSON handling
- Native UUID v4/v7 support

## Redis
- Caching
- Queue management
- Rate limiting
- Session handling

