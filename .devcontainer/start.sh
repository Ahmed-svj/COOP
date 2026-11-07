#!/usr/bin/env bash
# Start the COOP website on port 8080 (safe to run more than once).
cd "$(dirname "$0")/.."
export XDEBUG_MODE=off
if php -r 'exit(@file_get_contents("http://127.0.0.1:8080/") === false ? 1 : 0);' 2>/dev/null; then
  echo "COOP is already running on port 8080."
  exit 0
fi
mkdir -p storage/logs
XDEBUG_MODE=off setsid nohup php -S 0.0.0.0:8080 -t public public/index.php > storage/logs/server.log 2>&1 < /dev/null &
disown || true
for i in $(seq 1 20); do php -r 'exit(@file_get_contents("http://127.0.0.1:8080/") === false ? 1 : 0);' 2>/dev/null && break; sleep 0.5; done
echo "COOP is running on port 8080 (see the PORTS tab). Password for every demo account: Coop2026!"
