#!/bin/sh

# Make sure the storage directories exist
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Notice: APP_KEY not set. Generating it..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Optional: Run seeders (if needed)
# php artisan db:seed --force

# Cache configuration and routes for production
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
