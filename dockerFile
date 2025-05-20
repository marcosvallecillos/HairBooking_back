# Usa PHP 8.2 con CLI
FROM php:8.2-cli

# Instala extensiones y herramientas necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    zip \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql

# Instala Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define el directorio de trabajo
WORKDIR /app

# Copia todos los archivos al contenedor
COPY . .

# Instala las dependencias de Symfony SIN ejecutar auto-scripts
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --no-progress --prefer-dist

# Expone el puerto usado por Render
EXPOSE 8080
ENV PORT=8080

# Comando para iniciar el servidor Symfony
CMD php -S 0.0.0.0:$PORT -t public
