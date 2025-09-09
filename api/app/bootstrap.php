<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// --- env() helper ---
if (!function_exists('env')) {
  function env(string $key, $default = null) {
    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($val !== false && $val !== null) ? $val : $default;
  }
}

// --- Load .env (non-fatal if missing) ---
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// --- Timezone ---
date_default_timezone_set(env('APP_TZ', 'UTC'));

// --- Central error/exception handling ---
\App\Core\Errors::register();

// --- Error verbosity by env ---
if (env('APP_ENV', 'production') !== 'production') {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

// --- Session cookie + start (once) ---
// (Security/CORS headers live in public/index.php, not here)
session_set_cookie_params([
  'httponly' => true,
  'secure'   => filter_var(env('APP_COOKIE_SECURE', false), FILTER_VALIDATE_BOOL),
  'samesite' => env('APP_COOKIE_SAMESITE', 'Lax'), // 'Strict' if you never do cross-site
  'path'     => '/',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
