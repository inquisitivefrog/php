
Project: Real-time Nginx log analyzer using Docker + PHP
Goal:   Read real access.log file, show top IPs, 404s, UAs
Output: Text or JSON

Stack
-----
1. Nginx (serves PHP, logs to real file)
2. PHP-FPM (runs PHP)
3. PHP-CLI (analyzes logs)
4. Docker volumes: share code + logs
5. No symlinks, no tee, no external tools

Files
-----
1. docker-compose.yml
2. Dockerfile
3. Dockerfile.cli
4. nginx/conf.d/default.conf
5. nginx/docker-entrypoint.sh
6. php/scripts/analyze-logs.php
7. php/health.php

How to Execute
--------------
# Start
docker compose up -d --build

# Traffic
seq 100 | xargs -I{} curl -s http://localhost:8080/health.php

# Analyze
docker compose run --rm php_cli php /var/www/html/scripts/analyze-logs.php

# JSON
docker compose run --rm php_cli php /var/www/html/scripts/analyze-logs.php --json

