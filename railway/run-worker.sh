#!/bin/sh
set -e

cd /var/www/api

exec php artisan queue:work --queue=default --tries=3
