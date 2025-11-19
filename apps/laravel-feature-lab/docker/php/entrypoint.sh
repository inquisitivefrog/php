#!/usr/bin/env bash
set -e

ENV_FILE="/var/www/html/.env"

# --------------------------------------------------------
# If .env does NOT exist, copy the example
# --------------------------------------------------------
if [ ! -f "$ENV_FILE" ]; then
    echo "No .env found, creating from .env.example..."
    cp /var/www/html/.env.example "$ENV_FILE"
fi

# --------------------------------------------------------
# Replace APP_KEY if it points to a Docker secret file
# --------------------------------------------------------
if grep -q '^APP_KEY=/run/secrets/' "$ENV_FILE"; then
    SECRET_FILE=$(grep '^APP_KEY=' "$ENV_FILE" | cut -d= -f2)
    if [ -f "$SECRET_FILE" ]; then
        SECRET_VALUE=$(cat "$SECRET_FILE")
        sed -i "s|^APP_KEY=.*|APP_KEY=${SECRET_VALUE}|g" "$ENV_FILE"
        echo "APP_KEY loaded from Docker secret."
    else
        echo "WARNING: Secret file $SECRET_FILE missing, APP_KEY not replaced."
    fi
fi

# --------------------------------------------------------
# Replace DB_PASSWORD if pointing at a secret
# --------------------------------------------------------
if grep -q '^DB_PASSWORD=/run/secrets/' "$ENV_FILE"; then
    SECRET_FILE=$(grep '^DB_PASSWORD=' "$ENV_FILE" | cut -d= -f2)
    if [ -f "$SECRET_FILE" ]; then
        SECRET_VALUE=$(cat "$SECRET_FILE")
        sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${SECRET_VALUE}|g" "$ENV_FILE"
        echo "DB_PASSWORD loaded from Docker secret."
    else
        echo "WARNING: Secret file $SECRET_FILE missing, DB_PASSWORD not replaced."
    fi
fi

exec "$@"

