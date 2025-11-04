<?php
declare(strict_types=1);

require_once __DIR__ . '/Db.php';

final class Auth
{
  public static function register(string $email, string $password, ?string $name = null): int
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO users (email, name, password_hash, created_at) VALUES (?, ?, ?, NOW())');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->execute([$email, $name, $hash]);
    return (int) $pdo->lastInsertId();
  }

  public static function login(string $email, string $password): bool
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) return false;
    if (!password_verify($password, $row['password_hash'])) return false;
    $_SESSION['uid'] = (int) $row['id'];
    $_SESSION['email'] = $email;
    return true;
  }

  public static function logout(): void
  {
    unset($_SESSION['uid']);
  }

  public static function userId(): ?int
  {
    return isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
  }

  public static function userEmail(): ?string
  {
    if (!empty($_SESSION['email'])) return (string) $_SESSION['email'];
    $uid = self::userId();
    if (!$uid) return null;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    return $row ? (string) $row['email'] : null;
  }

  public static function userName(): ?string
  {
    if (!empty($_SESSION['name'])) return (string) $_SESSION['name'];
    $uid = self::userId();
    if (!$uid) return null;
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    return $row && $row['name'] !== null ? (string) $row['name'] : null;
  }

  public static function updateName(int $userId, string $name): void
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
    $stmt->execute([$name, $userId]);
    $_SESSION['name'] = $name;
  }

  public static function changePassword(int $userId, string $currentPassword, string $newPassword): bool
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($currentPassword, $row['password_hash'])) return false;
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([$newHash, $userId]);
    return true;
  }

  public static function createResetToken(string $email): ?string
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $uid = (int) $row['id'];
    $token = bin2hex(random_bytes(16));
    $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
    $upd = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
    $upd->execute([$token, $expires, $uid]);
    return $token;
  }

  public static function resetPassword(string $token, string $newPassword): bool
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ?');
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row) return false;
    if (new DateTime() > new DateTime($row['reset_expires'])) return false;
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $upd = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
    $upd->execute([$hash, (int) $row['id']]);
    return true;
  }
}


