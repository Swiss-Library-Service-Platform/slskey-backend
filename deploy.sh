composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache