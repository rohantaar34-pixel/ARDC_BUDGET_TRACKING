#!/usr/bin/env sh
set -e

export PORT="${PORT:-8080}"

if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ] && [ -z "${APP_URL:-}" ]; then
  export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="${MYSQL_URL}"
fi

if [ -n "${MYSQLHOST:-}" ]; then
  export DB_CONNECTION="${DB_CONNECTION:-mysql}"
  export DB_HOST="${DB_HOST:-${MYSQLHOST}}"
  export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
  export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-laravel}}"
  export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-root}}"
  export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
fi

if [ -n "${REDISHOST:-}" ]; then
  export REDIS_HOST="${REDIS_HOST:-${REDISHOST}}"
  export REDIS_PORT="${REDIS_PORT:-${REDISPORT:-6379}}"
  export REDIS_USERNAME="${REDIS_USERNAME:-${REDISUSER:-}}"
  export REDIS_PASSWORD="${REDIS_PASSWORD:-${REDISPASSWORD:-}}"
fi

if [ -n "${RAILWAY_VOLUME_MOUNT_PATH:-}" ]; then
  VOLUME_ROOT="${RAILWAY_VOLUME_MOUNT_PATH%/}"
  export PUBLIC_DISK_ROOT="${PUBLIC_DISK_ROOT:-${VOLUME_ROOT}/app/public}"
  export LOCAL_DISK_ROOT="${LOCAL_DISK_ROOT:-${VOLUME_ROOT}/app/private}"
else
  export PUBLIC_DISK_ROOT="${PUBLIC_DISK_ROOT:-/var/www/html/storage/app/public}"
  export LOCAL_DISK_ROOT="${LOCAL_DISK_ROOT:-/var/www/html/storage/app/private}"
fi

# Fix Apache MPM conflict at runtime
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf
rm -f /etc/apache2/mods-enabled/mpm_prefork.load
rm -f /etc/apache2/mods-enabled/mpm_prefork.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        Options FollowSymLinks
    </Directory>

    ErrorLog /proc/self/fd/2
    CustomLog /proc/self/fd/1 combined
</VirtualHost>
EOF

mkdir -p \
  "${PUBLIC_DISK_ROOT}" \
  "${LOCAL_DISK_ROOT}" \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

attempt=1
max_attempts="${DB_WAIT_MAX_ATTEMPTS:-10}"
sleep_seconds="${DB_WAIT_SECONDS:-5}"

until php artisan migrate --force; do
  if [ "${attempt}" -ge "${max_attempts}" ]; then
    echo "Database migrations failed after ${max_attempts} attempts." >&2
    exit 1
  fi

  echo "Migration attempt ${attempt}/${max_attempts} failed. Retrying in ${sleep_seconds}s..." >&2
  attempt=$((attempt + 1))
  sleep "${sleep_seconds}"
done

php artisan storage:link --force

exec "$@"
