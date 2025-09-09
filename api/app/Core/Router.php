<?php
namespace App\Core;

class Router {
  /** @var array<array{method:string, pattern:string, handler:callable}> */
  private array $routes = [];

  public function add(string $method, string $pattern, callable $handler): void {
    $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler];
  }

  public function dispatch(string $method, string $path): void {
    foreach ($this->routes as $r) {
      if ($r['method'] !== strtoupper($method)) continue;
      if (preg_match($r['pattern'], $path, $m)) {
        array_shift($m);
        $r['handler'](...$m);
        return;
      }
    }
    Responder::error('Not Found', 404);
  }
}
