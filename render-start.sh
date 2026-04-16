#!/usr/bin/env sh
set -eu

PORT="${PORT:-10000}"

# Configure Apache to listen on Render's assigned port.
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground