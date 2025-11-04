<?php
declare(strict_types=1);

function env(string $key, ?string $default = null): string
{
  $val = getenv($key);
  if ($val === false || $val === '') {
    if ($default !== null) return $default;
    return '';
  }
  return $val;
}

function app_env(): string { return env('APP_ENV', 'development'); }
function app_debug(): bool { return env('APP_DEBUG', 'false') === 'true'; }

// Strict required in production
function require_env(string $key): string
{
  $val = env($key);
  if ($val === '' && app_env() === 'production') {
    http_response_code(500);
    error_log("Missing required env: {$key}");
    exit('Server misconfiguration');
  }
  return $val;
}

// Database config
$DB = [
  'host' => env('DB_HOST', '127.0.0.1'),
  'port' => (int) env('DB_PORT', '3306'),
  'name' => env('DB_NAME', 'photo_collage'),
  'user' => env('DB_USER', 'photo_collage'),
  'pass' => env('DB_PASSWORD', ''),
];

// Sessions
$SESSION_NAME = env('SESSION_NAME', 'photo_sess');
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name($SESSION_NAME);
  session_start([
    'cookie_httponly' => true,
    'cookie_secure' => (!app_debug()),
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => 1,
  ]);
}

// CSRF
$CSRF_SECRET = require_env('CSRF_SECRET');


