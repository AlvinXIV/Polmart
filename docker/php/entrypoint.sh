#!/bin/sh
set -e

cd /var/www

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache

if [ -f composer.lock ]; then
    CURRENT_HASH=$(sha256sum composer.lock | awk '{print $1}')
    STORED_HASH=""
    if [ -f /var/www/vendor/.composer_lock_hash ]; then
        STORED_HASH=$(cat /var/www/vendor/.composer_lock_hash)
    fi

    if [ ! -f /var/www/vendor/autoload.php ] || [ "$CURRENT_HASH" != "$STORED_HASH" ]; then
        if mkdir /var/www/vendor/.sync_lock 2>/dev/null; then
            trap 'rmdir /var/www/vendor/.sync_lock 2>/dev/null || true' EXIT
            if [ -d /usr/src/polmart_vendor ] && [ ! -f /var/www/vendor/autoload.php ]; then
                cp -a /usr/src/polmart_vendor/. /var/www/vendor/
            else
                composer install --optimize-autoloader --no-interaction --no-progress
            fi
            echo "$CURRENT_HASH" > /var/www/vendor/.composer_lock_hash
            rmdir /var/www/vendor/.sync_lock 2>/dev/null || true
        else
            # Wait briefly if another container is syncing vendor
            i=0
            while [ -d /var/www/vendor/.sync_lock ] && [ $i -lt 30 ]; do
                sleep 1
                i=$((i + 1))
            done
        fi
    fi
fi

exec "$@"

