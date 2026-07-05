#!/bin/bash
# docker/apache-start.sh — bind Apache to the port Render assigns at runtime
set -e

: "${PORT:=80}"

# Rewrite the "Listen 80" directive and the default vhost's port
sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/g" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
