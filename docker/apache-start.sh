#!/bin/bash
# docker/apache-start.sh — bind Apache to the port Render assigns at runtime
set -e

: "${PORT:=80}"

# Rewrite the "Listen 80" directive and the default vhost's port
sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

# Render's Secret Files are mounted read-only with restrictive permissions
# that www-data (Apache's runtime user) can't read directly. Copy the CA
# cert to a location www-data owns before starting Apache.
if [ -n "$DB_SSL_CA" ] && [ -f "$DB_SSL_CA" ]; then
    mkdir -p /var/www/certs
    cp "$DB_SSL_CA" /var/www/certs/ca.pem
    chown www-data:www-data /var/www/certs/ca.pem
    chmod 644 /var/www/certs/ca.pem
    export DB_SSL_CA=/var/www/certs/ca.pem
fi

exec apache2-foreground