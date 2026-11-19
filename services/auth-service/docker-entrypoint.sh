#!/bin/bash
set -e

cd /var/www/html

# Generer .env si absent
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Securite : cle si vide
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Attente de la base (max ~30s)
php -r '
$host = getenv("DB_HOST") ?: "db";
$port = getenv("DB_PORT") ?: "5432";
$db   = getenv("DB_DATABASE") ?: "";
$user = getenv("DB_USERNAME") ?: "gpe";
$pass = getenv("DB_PASSWORD") ?: "secret";
$dsn  = "pgsql:host=$host;port=$port;dbname=$db";
for ($i = 0; $i < 30; $i++) {
    try { new PDO($dsn, $user, $pass); exit(0); }
    catch (Exception $e) { usleep(1000000); }
}
fwrite(STDERR, "DB not reachable\n"); exit(1);
'

# Migrations + seeders
php artisan migrate --force
php artisan db:seed --force || true

# Cache de config/routes en prod, pas en local
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
fi

exec "$@"
