#!/bin/sh
set -e

# Ensure storage and cache directories exist
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/storage/app/public

# Copy the Docker-specific env file into the standard Laravel .env path.
if [ -f /var/www/html/.env.docker ]; then
  cp -f /var/www/html/.env.docker /var/www/html/.env || true
fi

# Fix permissions for runtime
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public || true

# If APP_KEY missing, try to generate (non-interactive)
if [ -z "${APP_KEY}" ]; then
  if [ -f /var/www/html/artisan ]; then
    php /var/www/html/artisan key:generate --force || true
  fi
fi

# Run migrations and (optionally) seed database on container start.
# This is intended for local/testing containers. It will retry migrations
# a few times to wait for the DB to become available.
if [ -f /var/www/html/artisan ]; then
  MAX_RETRIES=10
  RETRY_COUNT=0
  until php /var/www/html/artisan migrate --force; do
    RETRY_COUNT=$((RETRY_COUNT+1))
    if [ "$RETRY_COUNT" -ge "$MAX_RETRIES" ]; then
      echo "php artisan migrate failed after $RETRY_COUNT attempts, continuing startup without blocking"
      break
    fi
    echo "Waiting for database... attempt $RETRY_COUNT/$MAX_RETRIES"
    sleep 5
  done

  # Seed only for non-production by default. The seeder itself handles production email.
  # Use APP_SEED=true to force seeding in any environment (e.g., CI/testing).
  if [ "${APP_ENV:-production}" != "production" ] || [ "${APP_SEED:-false}" = "true" ]; then
    php /var/www/html/artisan db:seed --class=UserSeeder --force || true
  fi
fi

# Execute supplied command (php-fpm by default)
exec "$@"
