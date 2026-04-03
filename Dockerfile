# ─────────────────────────────────────────────
#  CBS Web — PHP 8.2 + Apache
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Instalar extensión mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Habilitar mod_rewrite por si se necesita en el futuro
RUN a2enmod rewrite

# Copiar código fuente al directorio web de Apache
COPY html/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer puerto HTTP
EXPOSE 80
