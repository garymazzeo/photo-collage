<?php
declare(strict_types=1);

require_once __DIR__ . '/Db.php';

final class Auth
{
  public static function register(string $email, string $password): int
  {
    $pdo = Db::pdo();
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, created_at) VALUES (?, ?, NOW())');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->execute([$email, $hash]);
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
}


