FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libldap2-dev curl \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip ldap \
    && a2enmod rewrite \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN sed -i 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/intranet-new/public#' /etc/apache2/sites-available/000-default.conf

RUN { \
    echo "upload_max_filesize = 50M"; \
    echo "post_max_size = 2048M"; \
    echo "max_file_uploads = 500"; \
    echo "memory_limit = 256M"; \
  } > /usr/local/etc/php/conf.d/uploads.ini
WORKDIR /var/www/html
