<?php
namespace App\Core;

final class Http {
  public static function requireJson(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') return;
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== 0) {
      Responder::error('Content-Type must be application/json', 415); exit;
    }
  }

  public static function jsonBody(): array {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) { Responder::error('Invalid JSON body', 400); exit; }
    return $data;
  }
}
