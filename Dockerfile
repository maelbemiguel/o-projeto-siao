FROM php:8.4-fpm-alpine

# Extensões do sistema
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libzip-dev \
        sqlite-dev \
        oniguruma-dev \
        icu-dev \
        nodejs \
        npm \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        zip \
        bcmath \
        intl \
        gd \
        pcntl \
        opcache

# pnpm
RUN npm install -g pnpm

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Configuração do PHP
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/entrypoint.sh /usr/local/bin/siao-entrypoint
RUN chmod 755 /usr/local/bin/siao-entrypoint

# Dependências PHP (cache de camada)
COPY composer.json composer.lock ./
ARG INSTALL_DEV_DEPENDENCIES=false
RUN if [ "$INSTALL_DEV_DEPENDENCIES" = "true" ]; then \
        composer install --no-interaction --no-scripts --no-autoloader --prefer-dist; \
    else \
        composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist; \
    fi

# Dependências JS (cache de camada)
COPY package.json pnpm-workspace.yaml* package-lock.json* pnpm-lock.yaml* ./
RUN pnpm install --frozen-lockfile --ignore-scripts 2>/dev/null || \
    pnpm install --ignore-scripts

# Copia o restante do projeto
COPY . .

# Finaliza o Composer (autoload otimizado)
RUN composer dump-autoload --optimize

# Build dos assets
RUN pnpm run build

# Permissões
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/siao-entrypoint"]
CMD ["php-fpm"]
