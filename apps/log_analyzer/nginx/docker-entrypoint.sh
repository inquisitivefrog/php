#!/bin/sh
set -e
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
chown nginx:nginx /var/log/nginx/access.log
exec nginx -g 'daemon off;'
