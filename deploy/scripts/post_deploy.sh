#!/usr/bin/env bash
set -euo pipefail

REL="$1"
VPS_PATH="${VPS_PATH:-/var/www/photo-collage}"
RELEASE_DIR="$VPS_PATH/releases/$REL"

# Ensure shared files
mkdir -p "$VPS_PATH/shared/uploads"

# Symlink .env file if present
# Check custom location first (via ENV_FILE env var), then fall back to shared location
ENV_SOURCE="${ENV_FILE:-$VPS_PATH/shared/.env}"
if [ -f "$ENV_SOURCE" ]; then
  ln -sf "$ENV_SOURCE" "$RELEASE_DIR/config/.env"
  echo "Linked .env from: $ENV_SOURCE"
fi

# Symlink uploads if used later
ln -snf "$VPS_PATH/shared/uploads" "$RELEASE_DIR/uploads"

# Permissions (only if user has sudo/root, otherwise skip)
# Note: If running as non-root, files will be owned by the SSH user
# Apache/PHP-FPM may need read access - configure via group permissions or ask admin
if command -v sudo >/dev/null 2>&1 && sudo -n true 2>/dev/null; then
  sudo chown -R www-data:www-data "$RELEASE_DIR" 2>/dev/null || true
fi

# Swap current symlink atomically
ln -sfn "$RELEASE_DIR" "$VPS_PATH/current"

# Reset PHP opcache if available
PHP_BIN=$(command -v php || true)
if [ -n "$PHP_BIN" ]; then
  $PHP_BIN -r 'if (function_exists("opcache_reset")) { opcache_reset(); }'
fi

echo "Deployed release $REL"


