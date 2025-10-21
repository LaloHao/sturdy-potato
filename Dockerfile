FROM php:8.2-cli

# Install system dependencies for composer
RUN apt-get update && apt-get install -y \
  git \
  zip \
  unzip \
  curl \
  libzip-dev \
  nodejs \
  npm \
  && docker-php-ext-install zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

COPY . .
# COPY bootstrap /app/bootstrap
# COPY routes /app/routes
# COPY app artisan boostrap composer.json composer.lock routes  /app/
RUN composer install

# COPY package.json package-lock.json ./
RUN npm ci

# COPY .env ./
RUN php artisan key:generate

# Set composer as entrypoint
# CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "&", "npm", "run", "dev", "--", "--host=0.0.0.0", "&&", "wait"]
CMD ["./start-server.sh"]