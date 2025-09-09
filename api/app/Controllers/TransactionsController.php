<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Responder;
use App\Core\Validate;
use App\Core\Http;

class TransactionsController {

  /**
   * GET /transactions
   * Filters:
   *   ?month=YYYY-MM        (e.g., 2025-09)
   *   ?category_id=123
   *   ?type=income|expense
   *   ?limit=50&offset=0
   */
  public function index(): void {
    $uid   = Auth::requireAuth();
    $pdo   = DB::pdo();

    $month      = $_GET['month']       ?? null;
    $categoryId = $_GET['category_id'] ?? null;
    $type       = $_GET['type']        ?? null;
    $limit      = isset($_GET['limit'])  ? max(1, min(200, (int)$_GET['limit'])) : 50;
    $offset     = isset($_GET['offset']) ? max(0, (int)$_GET['offset'])          : 0;

    $where  = ['t.user_id = ?'];
    $params = [$uid];

    // month=YYYY-MM → [from, to)
    if ($month) {
      if (!preg_match('/^\d{4}-\d{2}$/', $month)) { Responder::error('Invalid month (use YYYY-MM)', 422); return; }
      [$y, $m] = array_map('intval', explode('-', $month));
      $from = sprintf('%04d-%02d-01', $y, $m);
      $to   = date('Y-m-d', strtotime("$from +1 month"));
      $where[]  = '(t.txn_date >= ? AND t.txn_date < ?)';
      $params[] = $from; $params[] = $to;
    }

    // category_id
    if ($categoryId !== null && $categoryId !== '') {
      if (!Validate::id($categoryId)) { Responder::error('Invalid category_id', 422); return; }
      $c = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
      $c->execute([(int)$categoryId, $uid]);
      if (!$c->fetch()) { Responder::error('category_id not found', 404); return; }
      $where[]  = 't.category_id = ?';
      $params[] = (int)$categoryId;
    }

    // type
    if ($type !== null && $type !== '') {
      if (!in_array($type, ['income','expense'], true)) { Responder::error('Invalid type (income|expense)', 422); return; }
      $where[]  = 't.type = ?';
      $params[] = $type;
    }

    $sql = '
      SELECT t.id, t.category_id, t.type, t.amount, t.currency, t.txn_date, t.payee, t.note,
             t.created_at, t.updated_at,
             c.name AS category_name, c.color_hex AS category_color
        FROM transactions t
        LEFT JOIN categories c ON c.id = t.category_id
       WHERE '.implode(' AND ', $where).'
       ORDER BY t.txn_date DESC, t.id DESC
       LIMIT '.$limit.' OFFSET '.$offset;

    $stmt  = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    Responder::json(['items' => $items, 'limit' => $limit, 'offset' => $offset]);
  }

  /**
   * POST /transactions
   * JSON: { type, amount, currency?, txn_date, category_id?, payee?, note? }
   */
  public function create(): void {
    $uid = Auth::requireAuth();
    Http::requireJson();
    $b = Http::jsonBody();

    $type       = $b['type'] ?? '';
    $amount     = $b['amount'] ?? null;
    $currency   = strtoupper((string)($b['currency'] ?? 'USD'));
    $date       = $b['txn_date'] ?? null;
    $categoryId = $b['category_id'] ?? null;
    $payee      = isset($b['payee']) ? trim((string)$b['payee']) : null;
    $note       = isset($b['note'])  ? trim((string)$b['note'])  : null;

    if (!in_array($type, ['income','expense'], true))  { Responder::error('type must be income|expense', 422); return; }
    if (!Validate::money($amount))                      { Responder::error('amount invalid (max 2 decimals)', 422); return; }
    if (!Validate::currency($currency))                 { Responder::error('currency invalid (ISO-4217 like)', 422); return; }
    if (!$date || !Validate::dateYmd($date))            { Responder::error('txn_date must be YYYY-MM-DD', 422); return; }
    if ($payee !== null && !Validate::stringLen($payee, 1, 160)) { Responder::error('payee length 1..160', 422); return; }
    if ($note  !== null && !Validate::stringLen($note, 1, 1000)) { Responder::error('note length 1..1000', 422); return; }

    $catId = null;
    if ($categoryId !== null && $categoryId !== '') {
      if (!Validate::id($categoryId)) { Responder::error('Invalid category_id', 422); return; }
      $pdo = DB::pdo();
      $c = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
      $c->execute([(int)$categoryId, $uid]);
      if (!$c->fetch()) { Responder::error('category_id not found', 404); return; }
      $catId = (int)$categoryId;
    }

    $pdo = DB::pdo();
    $stmt = $pdo->prepare('
      INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note)
      VALUES (?,?,?,?,?,?,?,?)
    ');
    $stmt->execute([$uid, $catId, $type, $amount, $currency, $date, $payee, $note]);

    Responder::json(['id' => (int)$pdo->lastInsertId()], 201);
  }

  /**
   * PATCH /transactions/{id}
   */
  public function update(int $id): void {
    $uid = Auth::requireAuth();
    if (!Validate::id($id)) { Responder::error('Invalid id', 422); return; }

    Http::requireJson();
    $b = Http::jsonBody();

    $fields = [];
    $params = [];

    if (array_key_exists('type', $b)) {
      if (!in_array($b['type'], ['income','expense'], true)) { Responder::error('type must be income|expense', 422); return; }
      $fields[] = 'type = ?'; $params[] = $b['type'];
    }
    if (array_key_exists('amount', $b)) {
      if (!Validate::money($b['amount'])) { Responder::error('amount invalid', 422); return; }
      $fields[] = 'amount = ?'; $params[] = $b['amount'];
    }
    if (array_key_exists('currency', $b)) {
      $cur = strtoupper((string)$b['currency']);
      if (!Validate::currency($cur)) { Responder::error('currency invalid', 422); return; }
      $fields[] = 'currency = ?'; $params[] = $cur;
    }
    if (array_key_exists('txn_date', $b)) {
      if (!Validate::dateYmd($b['txn_date'])) { Responder::error('txn_date invalid', 422); return; }
      $fields[] = 'txn_date = ?'; $params[] = $b['txn_date'];
    }
    if (array_key_exists('category_id', $b)) {
      $catId = $b['category_id'];
      if ($catId !== null && $catId !== '') {
        if (!Validate::id($catId)) { Responder::error('Invalid category_id', 422); return; }
        $pdo = DB::pdo();
        $c = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
        $c->execute([(int)$catId, $uid]);
        if (!$c->fetch()) { Responder::error('category_id not found', 404); return; }
        $fields[] = 'category_id = ?'; $params[] = (int)$catId;
      } else {
        $fields[] = 'category_id = NULL';
      }
    }
    if (array_key_exists('payee', $b)) {
      $p = $b['payee'];
      if ($p !== null && !Validate::stringLen((string)$p, 1, 160)) { Responder::error('payee length 1..160', 422); return; }
      $fields[] = 'payee = ?'; $params[] = $p;
    }
    if (array_key_exists('note', $b)) {
      $n = $b['note'];
      if ($n !== null && !Validate::stringLen((string)$n, 1, 1000)) { Responder::error('note length 1..1000', 422); return; }
      $fields[] = 'note = ?'; $params[] = $n;
    }

    if (!$fields) { Responder::error('No fields to update', 400); return; }

    $pdo = DB::pdo();
    $own = $pdo->prepare('SELECT id FROM transactions WHERE id = ? AND user_id = ?');
    $own->execute([$id, $uid]);
    if (!$own->fetch()) { Responder::error('Not Found', 404); return; }

    $sql = 'UPDATE transactions SET '.implode(', ', $fields).' WHERE id = ?';
    $params[] = $id;
    $pdo->prepare($sql)->execute($params);

    Responder::json(['ok' => true]);
  }

  /**
   * DELETE /transactions/{id}
   */
  public function destroy(int $id): void {
    $uid = Auth::requireAuth();
    if (!Validate::id($id)) { Responder::error('Invalid id', 422); return; }

    $pdo = DB::pdo();
    $stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $uid]);

    if ($stmt->rowCount() === 0) { Responder::error('Not Found', 404); return; }
    Responder::json(['ok' => true]);
  }
}
