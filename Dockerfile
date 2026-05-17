FROM php:8.2-cli

# Instalar dependências de sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    supervisor

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Instalar e habilitar Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Permitir que o Composer seja executado como superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Definir o diretório de trabalho
WORKDIR /var/www

# Copiar os arquivos da aplicação (se não houver volume sobrescrevendo)
COPY . .

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Expor porta
EXPOSE 8000

CMD bash -c "composer clear-cache && composer install --no-interaction --prefer-dist && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"
