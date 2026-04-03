# ─────────────────────────────────────────────
#  CBS Web — PHP 8.2 + Apache + PostgreSQL
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL y PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install mysqli pgsql pdo_pgsql \
    && docker-php-ext-enable mysqli pgsql pdo_pgsql

# Habilitar mod_rewrite por si se necesita en el futuro
RUN a2enmod rewrite

# Copiar código fuente al directorio web de Apache
COPY html/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer puerto HTTP
EXPOSE 80
