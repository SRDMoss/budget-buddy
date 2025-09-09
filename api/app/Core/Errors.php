<?php
namespace App\Core;

final class Errors {
  public static function register(): void {
    set_error_handler(function ($severity, $message, $file, $line) {
      // Convert warnings/notices to exceptions for a single handler
      if (!(error_reporting() & $severity)) return false;
      throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(function ($e) {
      $isProd = env('APP_ENV','production') === 'production';
      Logger::error('uncaught', [
        'type' => get_class($e),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'msg'  => $e->getMessage(),
        // stack is logged only in non-prod to avoid PII in logs
        'stack' => $isProd ? null : $e->getTrace(),
      ]);
      http_response_code(500);
      Responder::json([
        'error' => $isProd ? 'Internal Server Error' : $e->getMessage(),
      ]);
    });

    register_shutdown_function(function () {
      $err = error_get_last();
      if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Logger::error('shutdown_fatal', $err);
        http_response_code(500);
        Responder::json(['error' => 'Internal Server Error']);
      }
    });
  }
}
