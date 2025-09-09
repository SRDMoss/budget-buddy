<?php
namespace App\Core;

final class Logger {
  private static function path(): string {
    $dir = __DIR__ . '/../../storage/logs';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    return $dir . '/app.log';
  }
  public static function info(string $msg, array $ctx = []): void { self::write('info', $msg, $ctx); }
  public static function warn(string $msg, array $ctx = []): void { self::write('warn', $msg, $ctx); }
  public static function error(string $msg, array $ctx = []): void { self::write('error', $msg, $ctx); }

  private static function write(string $lvl, string $msg, array $ctx): void {
    $line = json_encode([
      'ts'   => date('c'),
      'lvl'  => $lvl,
      'msg'  => $msg,
      'ctx'  => $ctx,
      'uid'  => $_SESSION['uid'] ?? null,
      'ip'   => $_SERVER['REMOTE_ADDR'] ?? null,
      'meth' => $_SERVER['REQUEST_METHOD'] ?? null,
      'path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
    ], JSON_UNESCAPED_SLASHES);
    @file_put_contents(self::path(), $line.PHP_EOL, FILE_APPEND);
  }
}
