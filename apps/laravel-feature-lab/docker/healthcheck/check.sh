#!/bin/bash
# docker/healthcheck/check.sh
# Multi-container health check for Laravel + PHP-FPM + Postgres + Redis

set -e

# ------------------------------------------------------------
# 1. PHP-FPM check
# ------------------------------------------------------------
if ! pgrep -x "php-fpm" >/dev/null; then
  echo "PHP-FPM not running"
  exit 1
fi

# ------------------------------------------------------------
# 2. Laravel health endpoint
# ------------------------------------------------------------
HEALTH_URL="http://localhost/health"
if command -v curl >/dev/null 2>&1; then
  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" $HEALTH_URL || echo 000)
  if [ "$HTTP_CODE" -ne 200 ]; then
    echo "Laravel /health endpoint returned $HTTP_CODE"
    exit 1
  fi
fi

# ------------------------------------------------------------
# 3. Postgres readiness
# ------------------------------------------------------------
POSTGRES_HOST="${POSTGRES_HOST:-postgres}"
POSTGRES_PORT="${POSTGRES_PORT:-5432}"
POSTGRES_USER="${POSTGRES_USER:-laravel}"

if command -v pg_isready >/dev/null 2>&1; then
  if ! pg_isready -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" >/dev/null 2>&1; then
    echo "Postgres not ready"
    exit 1
  fi
fi

# ------------------------------------------------------------
# 4. Redis readiness
# ------------------------------------------------------------
REDIS_HOST="${REDIS_HOST:-redis}"
REDIS_PORT="${REDIS_PORT:-6379}"

if command -v redis-cli >/dev/null 2>&1; then
  if ! redis-cli -h "$REDIS_HOST" -p "$REDIS_PORT" ping | grep -q PONG; then
    echo "Redis not ready"
    exit 1
  fi
fi

echo "Healthy"
exit 0

