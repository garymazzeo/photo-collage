#!/usr/bin/env bash
set -euo pipefail

REL="$1"
VPS_PATH="${VPS_PATH:-/var/www/photo-collage}"
RELEASE_DIR="$VPS_PATH/releases/$REL"

# Ensure shared files
mkdir -p "$VPS_PATH/shared/uploads"

# Symlink shared .env if present
if [ -f "$VPS_PATH/shared/.env" ]; then
  ln -sf "$VPS_PATH/shared/.env" "$RELEASE_DIR/config/.env"
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


