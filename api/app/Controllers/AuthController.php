<?php
namespace App\Controllers;

use App\Core\Responder;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Validate;
use App\Core\Http;

class AuthController {
  public function me(): void {
    $uid = \App\Core\Auth::userId();
    if (!$uid) { Responder::json(['user' => null]); return; }
    $pdo = \App\Core\DB::pdo();
    $stmt = $pdo->prepare('SELECT id, email, display_name, created_at FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    Responder::json(['user' => $stmt->fetch()]);
  }

  public function register(): void {
    Csrf::requireToken();
    Http::requireJson();
    $b = Http::jsonBody();

    $email = trim($b['email'] ?? '');
    $pass  = (string)($b['password'] ?? '');
    $name  = isset($b['display_name']) ? trim((string)$b['display_name']) : null;

    if (!Validate::email($email)) { Responder::error('Invalid email', 422); return; }
    if (!Validate::password($pass)) { Responder::error('Password too short (min 8)', 422); return; }
    if ($name !== null && !Validate::stringLen($name, 1, 100)) { Responder::error('display_name length 1..100', 422); return; }

    if (!Auth::register($email, $pass, $name ?: null)) {
      Responder::error('Email already in use', 409); return;
    }
    Responder::json(['ok' => true], 201);
  }

  public function login(): void {
    Csrf::requireToken();
    Http::requireJson();
    $b = Http::jsonBody();

    $email = trim($b['email'] ?? '');
    $pass  = (string)($b['password'] ?? '');

    if (!Validate::email($email) || !Validate::password($pass)) {
      Responder::error('Invalid credentials', 422); return;
  }

    // tiny delay to slow brute force
    usleep(200000);

    if (!Auth::login($email, $pass)) {
      Responder::error('Invalid credentials', 401); return;
    }
    Responder::json(['ok' => true]);
  }

  

  public function logout(): void {
    Csrf::requireToken();
    Auth::logout();    
    Responder::json(['ok' => true]);
  }

  public function csrf(): void {
    // For SPAs: call this first to get a CSRF token to send in X-CSRF-Token header
    Responder::json(['csrf' => \App\Core\Csrf::token()]);
  }
}
