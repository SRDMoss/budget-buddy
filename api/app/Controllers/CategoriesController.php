<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Responder;
use App\Core\Validate;
use App\Core\Http;

class CategoriesController {
  public function index(): void {
    $uid = Auth::requireAuth();
    $pdo = DB::pdo();
    $stmt = $pdo->prepare(
      'SELECT id, name, color_hex, is_archived, created_at, updated_at
         FROM categories
        WHERE user_id = ?
        ORDER BY name ASC'
    );
    $stmt->execute([$uid]);
    Responder::json(['items' => $stmt->fetchAll()]);
  }

  public function create(): void {
    $uid = Auth::requireAuth();
    Http::requireJson();
    $data  = Http::jsonBody();

    $name  = trim((string)($data['name'] ?? ''));
    $color = $data['color_hex'] ?? null;

    if (!Validate::stringLen($name, 1, 100)) { Responder::error('Invalid category name', 422); return; }
    if (!Validate::colorHex($color))         { Responder::error('Invalid color_hex', 422); return; }

    $pdo = DB::pdo();
    try {
      $pdo->prepare('INSERT INTO categories (user_id, name, color_hex) VALUES (?,?,?)')
          ->execute([$uid, $name, $color]);
      Responder::json([
        'id'        => (int)$pdo->lastInsertId(),
        'name'      => $name,
        'color_hex' => $color,
      ], 201);
    } catch (\Throwable $e) {
      // likely unique (user_id, name) violation
      Responder::error('Category already exists', 409);
    }
  }

  public function update(int $id): void {
    $uid = Auth::requireAuth();
    if (!Validate::id($id)) { Responder::error('Invalid id', 422); return; }

    Http::requireJson();
    $data = Http::jsonBody();

    $fields = [];
    $params = [];

    if (array_key_exists('name', $data)) {
      $name = trim((string)$data['name']);
      if (!Validate::stringLen($name, 1, 100)) { Responder::error('Invalid name', 422); return; }
      $fields[] = 'name = ?';      $params[] = $name;
    }
    if (array_key_exists('color_hex', $data)) {
      $color = $data['color_hex'];
      if (!Validate::colorHex($color)) { Responder::error('Invalid color_hex', 422); return; }
      $fields[] = 'color_hex = ?'; $params[] = $color;
    }
    if (array_key_exists('is_archived', $data)) {
      if (!Validate::boolLike($data['is_archived'])) { Responder::error('Invalid is_archived', 422); return; }
      $fields[] = 'is_archived = ?'; $params[] = (int)!!$data['is_archived'];
    }

    if (!$fields) { Responder::error('No fields to update', 400); return; }

    $pdo = DB::pdo();

    // Ensure it belongs to this user
    $check = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
    $check->execute([$id, $uid]);
    if (!$check->fetch()) { Responder::error('Not Found', 404); return; }

    // Perform update (catch unique violations on name change)
    try {
      $sql = 'UPDATE categories SET '.implode(', ', $fields).' WHERE id = ?';
      $params[] = $id;
      $pdo->prepare($sql)->execute($params);
    } catch (\Throwable $e) {
      Responder::error('Category already exists', 409);
      return;
    }

    Responder::json(['ok' => true]);
  }

  public function destroy(int $id): void {
    $uid = Auth::requireAuth();
    if (!Validate::id($id)) { Responder::error('Invalid id', 422); return; }

    $pdo = DB::pdo();
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);

    if ($stmt->rowCount() === 0) { Responder::error('Not Found', 404); return; }
    Responder::json(['ok' => true]);
  }
}
