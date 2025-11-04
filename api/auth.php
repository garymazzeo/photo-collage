<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_GET['action'] ?? '';

function json_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

function csrf_check(): void {
  global $CSRF_SECRET;
  $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if (!$token || !hash_equals(hash('sha256', session_id() . $CSRF_SECRET), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'csrf']);
    exit;
  }
}

if ($method === 'POST' && $path === 'register') {
  csrf_check();
  $in = json_input();
  $email = filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL);
  $password = (string)($in['password'] ?? '');
  if (!$email || strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'validation']);
    exit;
  }
  try {
    $uid = Auth::register($email, $password);
    echo json_encode(['ok' => true, 'user_id' => $uid]);
  } catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'exists']);
  }
  exit;
}

if ($method === 'POST' && $path === 'login') {
  csrf_check();
  $in = json_input();
  $email = filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL);
  $password = (string)($in['password'] ?? '');
  if (!$email || $password === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'validation']);
    exit;
  }
  $ok = Auth::login($email, $password);
  echo json_encode(['ok' => $ok]);
  exit;
}

if ($method === 'POST' && $path === 'logout') {
  csrf_check();
  Auth::logout();
  echo json_encode(['ok' => true]);
  exit;
}

if ($method === 'GET' && $path === 'csrf') {
  global $CSRF_SECRET;
  $token = hash('sha256', session_id() . $CSRF_SECRET);
  echo json_encode(['ok' => true, 'token' => $token]);
  exit;
}

http_response_code(404);
echo json_encode(['ok' => false, 'error' => 'not_found']);


