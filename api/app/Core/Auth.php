<?php
namespace App\Core;

use PDO;

class Auth {
  public static function userId(): ?int {
    return $_SESSION['uid'] ?? null;
  }

  public static function requireAuth(): int {
    $uid = self::userId();
    if (!$uid) {
      Responder::error('Unauthorized', 401);
      exit;
    }
    return $uid;
  }

  public static function login(string $email, string $password): bool {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) return false;

    // Rotate session ID on login
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$row['id'];
    return true;
  }

  public static function register(string $email, string $password, ?string $displayName = null): bool {
    $pdo = DB::pdo();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, display_name) VALUES (?,?,?)');
    try {
      $stmt->execute([$email, $hash, $displayName]);
      return true;
    } catch (\Throwable $e) {
      // Likely duplicate email
      return false;
    }
  }

  public static function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }
    session_destroy();
  }
}
