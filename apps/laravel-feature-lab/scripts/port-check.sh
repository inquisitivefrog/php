#!/bin/bash
# port-check.sh — checks local Docker service ports
# ------------------------------------------------
# UP    = Service is responding/listening (HTTP 200 or TCP open)
# DOWN  = Service returned an error (HTTP 4xx/5xx) or is not reachable
# Colors used for quick visual feedback, words preserved

GREEN="\033[0;32m"
RED="\033[0;31m"
NC="\033[0m"  # No Color

echo "Checking service ports..."

services=(
    "Mailpit http://localhost:8025"
    "PHP/Nginx http://localhost:8080"
    "Vite (localhost) http://localhost:5173"
    "Vite (host) http://192.168.64.16:5173"
    "MeiliSearch http://localhost:7700/health"
    "PHP-FPM 127.0.0.1:9000"
    "Redis 127.0.0.1:6379"
    "Postgres 127.0.0.1:5432"
)

for s in "${services[@]}"; do
    name=$(echo "$s" | awk '{print $1}')
    url=$(echo "$s" | awk '{print $2}')

    if [[ "$url" =~ http ]]; then
        status=$(curl -s -o /dev/null -w "%{http_code}" "$url")
        if [[ "$status" =~ ^2 ]]; then
            echo -e "[$name] $url -> ${GREEN}UP${NC} ($status)"
        else
            echo -e "[$name] $url -> ${RED}DOWN${NC} ($status)"
        fi
    else
        host=$(echo "$url" | cut -d: -f1)
        port=$(echo "$url" | cut -d: -f2)
        nc -z "$host" "$port" &>/dev/null
        if [[ $? -eq 0 ]]; then
            echo -e "[$name] $url -> ${GREEN}UP${NC}"
        else
            echo -e "[$name] $url -> ${RED}DOWN${NC}"
        fi
    fi
done

