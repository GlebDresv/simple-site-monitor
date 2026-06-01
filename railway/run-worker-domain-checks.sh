#!/bin/sh
set -e

cd /var/www/api

exec php artisan queue:work --queue=domain-checks --tries=3 --timeout=30
