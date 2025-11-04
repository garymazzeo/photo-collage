<?php
declare(strict_types=1);

final class Db
{
  private static ?\PDO $pdo = null;

  public static function pdo(): \PDO
  {
    if (self::$pdo) return self::$pdo;
    require_once __DIR__ . '/../config/config.php';
    global $DB;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $DB['host'], $DB['port'], $DB['name']);
    $pdo = new \PDO($dsn, $DB['user'], $DB['pass'], [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ]);
    self::$pdo = $pdo;
    return $pdo;
  }
}


