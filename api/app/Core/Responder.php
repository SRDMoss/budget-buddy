<?php
namespace App\Core;

class Responder {
  public static function json($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
  }
  public static function error(string $message, int $status = 400, array $extra = []): void {
    self::json(['error' => $message] + $extra, $status);
  }
}
