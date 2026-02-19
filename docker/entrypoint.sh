#!/bin/sh
set -e

# Stop all processes on exit
cleanup() {
    echo "Shutting down..."
    kill "$PHP_PID" "$NGINX_PID" 2>/dev/null
    wait "$PHP_PID" "$NGINX_PID" 2>/dev/null
    exit 0
}
trap cleanup TERM INT QUIT

# Start PHP-FPM
php-fpm --nodaemonize &
PHP_PID=$!

# Start Nginx
nginx -g "daemon off;" &
NGINX_PID=$!

# Wait for either process to exit — if one dies, stop the container
wait -n "$PHP_PID" "$NGINX_PID" 2>/dev/null
echo "A process exited unexpectedly, stopping container..."
cleanup
