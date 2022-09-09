FROM composer:2.4.1 as build
WORKDIR /app
COPY /app/composer.json /app/composer.lock  /app/index.php ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

FROM php:8.2.0RC1-fpm-buster
ENV USER=www
ENV GROUP=www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev 
    
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Setup working directory
WORKDIR /app

COPY --from=build /app .

# Create User and Group
RUN groupadd -g 1000 ${GROUP} && useradd -u 1000 -ms /bin/bash -g ${GROUP} ${USER}

# Grant Permissions
RUN chown -R ${USER} /app

# Select User
USER ${USER}

# Copy permission to selected user
COPY --chown=${USER}:${GROUP} . .

EXPOSE 9000

CMD ["php-fpm"]