FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libpq-dev libzip-dev zip unzip nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache-polmart.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-polmart.conf
COPY docker/php/entrypoint.sh /usr/local/bin/polmart-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/polmart-entrypoint.sh && chmod +x /usr/local/bin/polmart-entrypoint.sh

WORKDIR /var/www

# Pre-cache vendor di dalam image agar volume Linux polmart_vendor langsung terisi cepat
COPY composer.json composer.lock ./
COPY vendor /usr/src/polmart_vendor
RUN cp -a /usr/src/polmart_vendor/. /var/www/vendor/ \
    && sha256sum composer.lock | awk '{print $1}' > /var/www/vendor/.composer_lock_hash

ENTRYPOINT ["/usr/local/bin/polmart-entrypoint.sh"]
CMD ["php-fpm"]
