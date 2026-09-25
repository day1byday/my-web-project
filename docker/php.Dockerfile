# ============================================================
# 生产环境 PHP 镜像（预留：将来部署云服务器时使用）
# 构建：docker build -f docker/php.Dockerfile -t mwp-php .
# ============================================================
FROM php:8.1-fpm-alpine

# 系统依赖（intl / zip / 图片处理 / mbstring）
RUN apk add --no-cache \
        icu-dev libzip-dev oniguruma-dev \
        freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql bcmath opcache zip gd

# redis 扩展（生产缓存/队列需要）
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# 生产依赖（安装时容器内需能访问网络）
RUN composer install --no-dev --optimize-autoloader

# 权限：TP6 需要 runtime/ 可写
RUN chown -R www-data:www-data /var/www/html/runtime

EXPOSE 9000
CMD ["php-fpm"]
