<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Projects.php';

header('Content-Type: application/json');

$uid = Auth::userId();
if (!$uid) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'unauthorized']);
  exit;
}

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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'list') {
  echo json_encode(['ok' => true, 'projects' => Projects::list($uid)]);
  exit;
}

if ($method === 'GET' && $action === 'get') {
  $id = (int)($_GET['id'] ?? 0);
  $p = $id ? Projects::get($id, $uid) : null;
  if (!$p) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'not_found']);
    exit;
  }
  echo json_encode(['ok' => true, 'project' => $p]);
  exit;
}

if ($method === 'POST' && $action === 'create') {
  csrf_check();
  $in = json_input();
  $title = trim((string)($in['title'] ?? 'Untitled'));
  $w = (int)($in['width_px'] ?? 3900);
  $h = (int)($in['height_px'] ?? 3000);
  $canvas = (string)($in['canvas_json'] ?? '{}');
  $id = Projects::create($uid, $title, $w, $h, $canvas);
  echo json_encode(['ok' => true, 'id' => $id]);
  exit;
}

if ($method === 'PUT' && $action === 'update') {
  csrf_check();
  $in = json_input();
  $id = (int)($in['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'validation']); exit; }
  $title = trim((string)($in['title'] ?? 'Untitled'));
  $w = (int)($in['width_px'] ?? 3900);
  $h = (int)($in['height_px'] ?? 3000);
  $canvas = (string)($in['canvas_json'] ?? '{}');
  Projects::update($id, $uid, $title, $w, $h, $canvas);
  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(404);
echo json_encode(['ok' => false, 'error' => 'not_found']);


