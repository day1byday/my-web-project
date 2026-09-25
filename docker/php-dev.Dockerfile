# ============================================================
# 开发环境 PHP 镜像（内置服务器，用于本地开发调试）
# 构建：docker build -f docker/php-dev.Dockerfile -t mwp-php-dev .
# 运行：见 docker-compose.yml 中的 php 服务
# ============================================================
FROM php:8.1-cli-alpine

# 系统依赖 + PHP 扩展
RUN apk add --no-cache \
        oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring bcmath

WORKDIR /app

EXPOSE 8001

# 内置服务器 + SPA 回退路由
CMD ["php", "-S", "0.0.0.0:8001", "-t", "public", "public/router.php"]
