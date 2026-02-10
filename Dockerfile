FROM php:8.3-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar archivos
COPY . .
 
# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar Node.js y npm
RUN apt-get update && apt-get install -y nodejs npm

# Copiar y construir assets
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Exponer puerto
EXPOSE 10000

# Comando de inicio
CMD php artisan serve --host=0.0.0.0 --port=10000
