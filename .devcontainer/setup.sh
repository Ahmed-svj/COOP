#!/usr/bin/env bash
# Codespaces / Dev Container setup. The MySQL container creates the schema and app account itself
# (see docker-compose.yml); here we add the PHP MySQL driver, write .env and load the demo data.
set -euo pipefail
cd "$(dirname "$0")/.."
export XDEBUG_MODE=off
if ! php -m | grep -qi '^pdo_mysql$'; then
  echo "Installing the PHP MySQL driver..."
  sudo -E docker-php-ext-install pdo_mysql > /dev/null
fi
cp -n .env.example .env 2>/dev/null || true
sed -i 's/^DB_HOST=.*/DB_HOST=db/' .env
echo "Waiting for MySQL to finish creating the database..."
for i in $(seq 1 120); do
  php -r 'try { new PDO("mysql:host=db;dbname=coop_db", "coop_app", "ChangeThisStrongPassword!"); exit(0); } catch (Throwable $e) { exit(1); }' && break
  sleep 2
done
php database/seed.php || echo "Demo data was already loaded."
echo "Setup complete."
