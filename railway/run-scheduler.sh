#!/bin/sh
set -e

cd /var/www/api

exec php artisan schedule:work
