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

# Permissions
chown -R www-data:www-data "$RELEASE_DIR"

# Swap current symlink atomically
ln -sfn "$RELEASE_DIR" "$VPS_PATH/current"

# Reset PHP opcache if available
PHP_BIN=$(command -v php || true)
if [ -n "$PHP_BIN" ]; then
  $PHP_BIN -r 'if (function_exists("opcache_reset")) { opcache_reset(); }'
fi

echo "Deployed release $REL"


