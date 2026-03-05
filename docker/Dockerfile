FROM php:8.3-cli

# Dependencies for extensions + MS ODBC
RUN apt-get update && apt-get install -y \
    curl git unzip libzip-dev gnupg2 ca-certificates apt-transport-https unixodbc-dev \
    && docker-php-ext-install pdo zip \
    && rm -rf /var/lib/apt/lists/*

# Add Microsoft package repository (no apt-key)
RUN mkdir -p /etc/apt/keyrings \
 && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor -o /etc/apt/keyrings/microsoft.gpg \
 && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/microsoft-prod.list

# Install ODBC driver + PHP sqlsrv extensions
RUN apt-get update \
 && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
 && pecl install sqlsrv pdo_sqlsrv \
 && docker-php-ext-enable sqlsrv pdo_sqlsrv \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
