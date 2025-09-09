<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Responder;

class ReportsController {

  // GET /reports/month?month=YYYY-MM
  public function month(): void {
    $uid = Auth::requireAuth();

    $month = $_GET['month'] ?? null;
    if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
      Responder::error('month required as YYYY-MM', 422); return;
    }
    [$y,$m] = array_map('intval', explode('-', $month));
    $from = sprintf('%04d-%02d-01', $y, $m);
    $to   = date('Y-m-d', strtotime("$from +1 month"));

    $pdo = DB::pdo();

    // Totals
    $stmt = $pdo->prepare('
      SELECT
        SUM(CASE WHEN type="income"  THEN amount ELSE 0 END) AS income,
        SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) AS expense
      FROM transactions
      WHERE user_id = ? AND txn_date >= ? AND txn_date < ?
    ');
    $stmt->execute([$uid, $from, $to]);
    $row = $stmt->fetch() ?: ['income' => 0, 'expense' => 0];
    $income  = (float)($row['income']  ?? 0);
    $expense = (float)($row['expense'] ?? 0);
    $net     = $income - $expense;

    // By category (separate rows for income/expense)
    $stmt = $pdo->prepare('
      SELECT t.category_id,
             COALESCE(c.name, "(Uncategorized)") AS category_name,
             t.type,
             SUM(t.amount) AS total
        FROM transactions t
        LEFT JOIN categories c ON c.id = t.category_id
       WHERE t.user_id = ? AND t.txn_date >= ? AND t.txn_date < ?
       GROUP BY t.category_id, category_name, t.type
       ORDER BY total DESC
    ');
    $stmt->execute([$uid, $from, $to]);
    $byCategory = $stmt->fetchAll();

    // Daily rollup
    $stmt = $pdo->prepare('
      SELECT txn_date AS date,
             SUM(CASE WHEN type="income"  THEN amount ELSE 0 END) AS income,
             SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) AS expense
        FROM transactions
       WHERE user_id = ? AND txn_date >= ? AND txn_date < ?
       GROUP BY txn_date
       ORDER BY txn_date ASC
    ');
    $stmt->execute([$uid, $from, $to]);
    $daily = array_map(function ($r) {
      $inc = (float)($r['income'] ?? 0);
      $exp = (float)($r['expense'] ?? 0);
      return [
        'date'    => $r['date'],
        'income'  => number_format($inc, 2, '.', ''),
        'expense' => number_format($exp, 2, '.', ''),
        'net'     => number_format($inc - $exp, 2, '.', ''),
      ];
    }, $stmt->fetchAll());

    Responder::json([
      'month'  => $month,
      'totals' => [
        'income'  => number_format($income, 2, '.', ''),
        'expense' => number_format($expense, 2, '.', ''),
        'net'     => number_format($net, 2, '.', ''),
      ],
      'byCategory' => $byCategory,
      'daily'      => $daily,
    ]);
  }
}
