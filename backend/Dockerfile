#FROM php:8.1-fpm
#
#RUN apt-get update && apt-get install -y \
#    libjpeg-dev \
#    libpng-dev \
#    libfreetype6-dev \
#    && docker-php-ext-configure gd --with-freetype --with-jpeg \
#    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql \
#    && apt-get clean && rm -rf /var/lib/apt/lists/*
#
#WORKDIR /var/www/html
#
#COPY ./ /var/www/html
FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copiar el archivo composer.json y composer.lock antes de copiar todo el código
# Esto ayuda a que las dependencias se instalen antes de copiar el código fuente
COPY ./composer.json ./composer.lock /var/www/html/

# Instalar dependencias con Composer
RUN composer install --no-dev --optimize-autoloader

# Copiar todo el código del backend después de instalar las dependencias
COPY ./ /var/www/html/

COPY start.sh /start.sh
RUN chmod +x /start.sh

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Iniciar PHP-FPM
CMD ["/start.sh"]





