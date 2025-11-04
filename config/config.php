<?php
declare(strict_types=1);

// Load .env file if it exists (for local development)
$envFile = __DIR__ . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
  $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue; // Skip comments
    if (strpos($line, '=') === false) continue;
    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value, " \t\n\r\0\x0B\"'"); // Remove quotes and whitespace
    if ($key !== '' && !isset($_ENV[$key])) {
      $_ENV[$key] = $value;
      putenv("$key=$value");
    }
  }
}

function env(string $key, ?string $default = null): string
{
  // Check $_ENV first (from .env file), then getenv() (system env vars)
  $val = $_ENV[$key] ?? getenv($key);
  if ($val === false || $val === '') {
    if ($default !== null) return $default;
    return '';
  }
  return (string) $val;
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


