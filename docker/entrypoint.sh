#!/bin/bash
# Arranque del contenedor TechGate: prepara el entorno y levanta Apache.
set -e

cd /var/www/html

# .env a partir del ejemplo si no existe (en AWS las variables se pueden
# inyectar por entorno: APP_KEY, ALLIED_PRODUCTS_URL, DB_*, etc.).
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Clave de aplicacion si falta.
if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# Base de datos SQLite.
touch database/database.sqlite
chown www-data:www-data database/database.sqlite

# Migraciones + datos de demostracion (idempotente).
php artisan migrate --force --seed || php artisan migrate --force

# Cachear configuracion y rutas para produccion.
php artisan config:cache
php artisan route:cache

exec apache2-foreground
