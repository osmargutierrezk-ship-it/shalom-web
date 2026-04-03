# ─────────────────────────────────────────────
#  CBS Web — PHP 8.2 + Apache + PostgreSQL
#  Para Render: Dockerfile en raíz, código en html/
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Instalar dependencias y extensiones para PostgreSQL (PDO)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pdo pdo_pgsql pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar código fuente — contexto de build es la raíz del repo
COPY html/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
