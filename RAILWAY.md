# Railway deploy notes

This repo is set up to deploy on Railway with the existing `Dockerfile` and `railway.json`.

## What was prepared

- Laravel now understands Railway MySQL variables (`MYSQL_URL`, `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`).
- Laravel now understands Railway Redis variables (`REDIS_URL`, `REDISHOST`, `REDISPORT`, `REDISUSER`, `REDISPASSWORD`) if you enable Redis later.
- `APP_URL` now falls back to `https://$RAILWAY_PUBLIC_DOMAIN` when `APP_URL` is not set manually.
- Apache logs now go to stdout and stderr, so you do not need to set `APACHE_LOG_DIR`.
- The container retries `php artisan migrate --force` while MySQL is starting.
- If a Railway volume is attached, uploads automatically move onto that volume through `RAILWAY_VOLUME_MOUNT_PATH`.

## Required Railway setup

1. Add your web service from this repo.
2. Add a MySQL service.
3. Attach a volume to the web service if you need uploaded files to persist.

Recommended volume mount path: `/data`

This app stores uploaded files locally on the `public` disk, so without a volume those files are lost on redeploy.

## Variables

Import or copy the contents of `.env.railway` into the web service variables.

Set `APP_KEY` manually before the first deploy:

```powershell
php artisan key:generate --show
```

If your Railway database service is not literally named `MySQL`, change this line in `.env.railway` before importing:

```dotenv
MYSQL_URL=${{MySQL.MYSQL_URL}}
```

## Optional

- Add SMTP variables if you want real email delivery. Without that, mail stays on the `log` driver.
- If you later add a Redis service, create a reference variable such as `REDIS_URL=${{Redis.REDIS_URL}}` and switch the relevant Laravel drivers to Redis.
