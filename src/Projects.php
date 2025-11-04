<?php
declare(strict_types=1);

require_once __DIR__ . '/Db.php';

final class Projects
{
  public static function create(int $userId, string $title, int $widthPx, int $heightPx, string $canvasJson): int
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO projects (user_id, title, width_px, height_px, canvas_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    $stmt->execute([$userId, $title, $widthPx, $heightPx, $canvasJson]);
    return (int) $pdo->lastInsertId();
  }

  public static function update(int $projectId, int $userId, string $title, int $widthPx, int $heightPx, string $canvasJson): void
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE projects SET title = ?, width_px = ?, height_px = ?, canvas_json = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
    $stmt->execute([$title, $widthPx, $heightPx, $canvasJson, $projectId, $userId]);
  }

  public static function get(int $projectId, int $userId): ?array
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$projectId, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  public static function list(int $userId): array
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id, title, width_px, height_px, created_at, updated_at FROM projects WHERE user_id = ? ORDER BY updated_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }
}


