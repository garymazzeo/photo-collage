<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Email.php';

header('Content-Type: application/json');

$uid = Auth::userId();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

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

if ($method === 'GET' && $action === 'profile') {
  if (!$uid) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
  echo json_encode(['ok' => true, 'email' => Auth::userEmail(), 'name' => Auth::userName()]);
  exit;
}

if ($method === 'POST' && $action === 'update-name') {
  if (!$uid) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
  csrf_check();
  $in = json_input();
  $name = trim((string)($in['name'] ?? ''));
  if ($name === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'validation']); exit; }
  Auth::updateName($uid, $name);
  echo json_encode(['ok' => true]);
  exit;
}

if ($method === 'POST' && $action === 'change-password') {
  if (!$uid) { http_response_code(401); echo json_encode(['ok'=>false]); exit; }
  csrf_check();
  $in = json_input();
  $current = (string)($in['current'] ?? '');
  $next = (string)($in['next'] ?? '');
  if (strlen($next) < 8) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'weak']); exit; }
  $ok = Auth::changePassword($uid, $current, $next);
  if (!$ok) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid']); exit; }
  echo json_encode(['ok' => true]);
  exit;
}

if ($method === 'POST' && $action === 'forgot') {
  csrf_check();
  $in = json_input();
  $email = filter_var($in['email'] ?? '', FILTER_VALIDATE_EMAIL);
  if (!$email) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
  $token = Auth::createResetToken($email);
  if (!$token) { http_response_code(404); echo json_encode(['ok'=>false, 'error'=>'not_found']); exit; }
  
  $sent = Email::sendPasswordReset($email, $token);
  $resp = ['ok' => $sent];
  // In development, also return token for testing
  if (app_env() !== 'production') { $resp['token'] = $token; }
  echo json_encode($resp);
  exit;
}

if ($method === 'POST' && $action === 'reset') {
  csrf_check();
  $in = json_input();
  $token = (string)($in['token'] ?? '');
  $next = (string)($in['next'] ?? '');
  if (strlen($token) < 10 || strlen($next) < 8) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
  $ok = Auth::resetPassword($token, $next);
  echo json_encode(['ok' => $ok]);
  exit;
}

if ($method === 'POST' && $action === 'test-email' && app_env() !== 'production') {
  csrf_check();
  $sent = Email::test();
  echo json_encode(['ok' => $sent]);
  exit;
}

http_response_code(404);
echo json_encode(['ok' => false, 'error' => 'not_found']);


