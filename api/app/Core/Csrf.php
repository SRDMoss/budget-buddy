<?php
namespace App\Core;

class Csrf {
  public static function token(): string {
    if (empty($_SESSION['_csrf'])) {
      $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
  }
  public static function verify(?string $token): bool {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token ?? '');
  }
  public static function requireToken(): void {
    $ok = self::verify($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null));
    if (!$ok) {
      Responder::error('Invalid CSRF token', 419);
      exit;
    }
  }
}
