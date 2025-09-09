<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Core\Responder;
use App\Controllers\AuthController;
use App\Controllers\CategoriesController;
use App\Controllers\TransactionsController;
use App\Controllers\ReportsController;

// --- CORS (locked to explicit origins) ---
$allowedOrigins = array_map('trim', explode(',', env('CORS_ORIGINS', 'http://localhost:5173')));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$corsOk = ($origin && in_array($origin, $allowedOrigins, true));

if ($corsOk) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin'); // cache per origin
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-HTTP-Method-Override');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');

// Preflight handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  if (!$corsOk) { http_response_code(403); exit; } // reject disallowed origins
  http_response_code(204); exit;                   // allowed preflight
}

// For non-OPTIONS requests: if a browser sent an Origin but it's not allowed, reject early
if ($origin !== '' && !$corsOk) {
  http_response_code(403);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => 'Origin not allowed']);
  exit;
}

// --- Security Headers ---
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cross-Origin-Resource-Policy: same-site');
header('Cross-Origin-Opener-Policy: same-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

// Content Security Policy:
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'");

// --- Path & Method (with optional override) ---
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
  $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
  if ($override) $method = strtoupper($override);
}

$router = new Router();

// Health
$router->add('GET', '#^/$#', function () {
  Responder::json(['app' => 'Budget Buddy API', 'env' => env('APP_ENV','production')]);
});

// CSRF token endpoint
$router->add('GET', '#^/auth/csrf$#', [new AuthController, 'csrf']);

// --- Auth ---
$router->add('GET',  '#^/auth/me$#',       [new AuthController, 'me']);
$router->add('POST', '#^/auth/register$#', [new AuthController, 'register']);
$router->add('POST', '#^/auth/login$#',    [new AuthController, 'login']);
$router->add('POST', '#^/auth/logout$#',   [new AuthController, 'logout']);

// Instantiate controllers
$cat = new CategoriesController();
$tx  = new TransactionsController();
$rep = new ReportsController();

// --- Categories ---
$router->add('GET', '#^/categories/?$#', [$cat, 'index']);
$router->add('POST', '#^/categories/?$#', function () use ($cat) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $cat->create();
});
$router->add('PATCH', '#^/categories/(\d+)$#', function ($id) use ($cat) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $cat->update((int)$id);
});
$router->add('DELETE', '#^/categories/(\d+)$#', function ($id) use ($cat) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $cat->destroy((int)$id);
});

// --- Transactions ---
$router->add('GET', '#^/transactions/?$#', [$tx, 'index']);
$router->add('POST', '#^/transactions/?$#', function () use ($tx) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $tx->create();
});
$router->add('PATCH', '#^/transactions/(\d+)$#', function ($id) use ($tx) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $tx->update((int)$id);
});
$router->add('DELETE', '#^/transactions/(\d+)$#', function ($id) use ($tx) {
  if (strtolower((string)env('AUTH_DRIVER','session')) === 'session') \App\Core\Csrf::requireToken();
  \App\Core\Http::requireJson();
  $tx->destroy((int)$id);
});

// --- Reports ---
$router->add('GET', '#^/reports/month$#', [$rep, 'month']);
$router->add('GET', '#^/reports/year$#',  [$rep, 'year']); // ✅ add year endpoint in the same style

// 🔚 Dispatch
$router->dispatch($method, $path);
