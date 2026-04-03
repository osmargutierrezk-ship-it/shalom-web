# ─────────────────────────────────────────────
#  CBS Web — PHP 8.2 + Apache + PostgreSQL
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Instalar dependencias necesarias y extensiones MySQL + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install mysqli pgsql pdo_pgsql \
    && docker-php-ext-enable mysqli pgsql pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar código fuente al directorio web de Apache
COPY html/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
