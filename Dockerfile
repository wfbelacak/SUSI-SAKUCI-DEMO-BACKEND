# Use official PHP 8.4 FPM image
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
        libpng-dev \
            libjpeg-turbo-dev \
                freetype-dev \
                    zip \
                        libzip-dev \
                            unzip \
                                git \
                                    curl \
                                        mysql-client \
                                            oniguruma-dev \
                                                icu-dev

                                                # Install PHP extensions
                                                RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

                                                # Get latest Composer
                                                COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

                                                # Set working directory
                                                WORKDIR /var/www

                                                # Copy existing application directory contents
                                                COPY . /var/www

                                                # Install dependencies
                                                RUN composer install --no-dev --optimize-autoloader

                                                # Copy configuration files
                                                COPY nginx.conf /etc/nginx/http.d/default.conf

                                                # Expose port 80
                                                EXPOSE 80

                                                # Start Nginx and PHP-FPM
                                                CMD php-fpm -D && nginx -g 'daemon off;'
