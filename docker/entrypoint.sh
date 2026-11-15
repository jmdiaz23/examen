#!/bin/sh
set -e

PORT="${PORT:-80}"

# Render y otros PaaS exponen el puerto en $PORT
sed -i -E "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Esperar a que la base de datos esté disponible (hasta 90 segundos).
# Neon (Postgres serverless) se suspende tras unos minutos de inactividad y
# tarda unos segundos en despertar, por eso la espera es más larga que antes.
php -r '
$driver = getenv("DB_CONNECTION") ?: "mysql";
$host = getenv("DB_HOST") ?: "127.0.0.1";
$name = getenv("DB_DATABASE") ?: "laravel";
$user = getenv("DB_USERNAME") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";

if ($driver === "pgsql") {
    $port = getenv("DB_PORT") ?: "5432";
    $sslmode = getenv("DB_SSLMODE") ?: "require";
    $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$sslmode}";
} else {
    $port = getenv("DB_PORT") ?: "3306";
    $dsn = "mysql:host={$host};port={$port};dbname={$name}";
}

$lastError = "";
for ($i = 0; $i < 90; $i++) {
    try {
        new PDO($dsn, $user, $pass);
        exit(0);
    } catch (Throwable $e) {
        $lastError = $e->getMessage();
        sleep(1);
    }
}
fwrite(STDERR, "No se pudo conectar a la base de datos ({$host}:{$port}/{$name}): {$lastError}\n");
exit(1);
'

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
