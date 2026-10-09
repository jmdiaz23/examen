#!/bin/sh
set -e

PORT="${PORT:-80}"

# Render y otros PaaS exponen el puerto en $PORT
sed -i -E "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Esperar a que la base de datos esté disponible (hasta 30 segundos)
php -r '
$host = getenv("DB_HOST") ?: "127.0.0.1";
$port = getenv("DB_PORT") ?: "3306";
$name = getenv("DB_DATABASE") ?: "laravel";
$user = getenv("DB_USERNAME") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";
for ($i = 0; $i < 30; $i++) {
    try {
        new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
        exit(0);
    } catch (Throwable $e) {
        sleep(1);
    }
}
fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
exit(1);
'

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
