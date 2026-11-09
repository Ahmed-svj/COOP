#!/usr/bin/env bash
# COOP – share the running system with a public Cloudflare link (https://....trycloudflare.com, no account needed).
# Keep this running while people use the link; Ctrl+C stops sharing (and the website, if this script started it).
set -euo pipefail
cd "$(dirname "$0")"
echo; echo "  COOP - share with a Cloudflare link"; echo "  -----------------------------------"
if ! command -v cloudflared >/dev/null 2>&1; then
  echo "  cloudflared is not installed."
  echo "    macOS:  brew install cloudflared"
  echo "    Linux:  https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/"
  exit 1
fi
if ! curl -s -o /dev/null http://127.0.0.1:8080/; then
  [ -f .env ] || { echo "  COOP is not installed yet. Run: php database/install.php"; exit 1; }
  echo "  Starting COOP on http://127.0.0.1:8080 ..."
  php -S 127.0.0.1:8080 -t public public/index.php > storage/logs/server.log 2>&1 &
  SERVER_PID=$!
  trap 'kill $SERVER_PID 2>/dev/null' EXIT
  sleep 1.5
  curl -s -o /dev/null http://127.0.0.1:8080/ || { echo "  COOP did not start. Is MySQL running?"; exit 1; }
fi
echo
echo "  Your public link appears below in a box:   https://....trycloudflare.com"
echo "  Anyone with the link can use COOP while this runs. Ctrl+C stops sharing."
echo "  Note: the demo accounts use the password Coop2026! - change them before sharing widely."
echo
cloudflared tunnel --no-autoupdate --url http://127.0.0.1:8080
