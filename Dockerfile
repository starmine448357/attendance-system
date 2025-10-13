FROM php:8.2-fpm

# 基本ツールのインストール
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev zip unzip

# PHP拡張
RUN docker-php-ext-install pdo_mysql gd

# Composerインストール
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
