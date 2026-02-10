FROM php:8.3-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias de frontend y compilar assets
RUN npm install && npm run build

# Configurar permisos (si usas storage/bootstrap/cache)
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer puerto
EXPOSE 10000

# Comando de inicio
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
