<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Responder;

class ReportsController
{
    /**
     * GET /reports/month?month=YYYY-MM[&category_id=ID]
     * {
     *   totals: { income, expense, net },
     *   byCategory: [{ category_name, type, total }],
     *   daily: [{ date:'YYYY-MM-DD', income, expense, net }]
     * }
     */
    public function month(): void
    {
        $uid   = Auth::requireAuth();
        $pdo   = DB::pdo();
        $month = $_GET['month'] ?? null;

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            Responder::error('Invalid or missing month (use YYYY-MM)', 422);
            return;
        }

        // Optional category filter, validate ownership if provided
        $catId = null;
        if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
            $raw = $_GET['category_id'];
            if (!preg_match('/^\d+$/', (string)$raw)) { Responder::error('Invalid category_id', 422); return; }
            $chk = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
            $chk->execute([(int)$raw, $uid]);
            if (!$chk->fetch()) { Responder::error('category_id not found', 404); return; }
            $catId = (int)$raw;
        }

        [$y, $m] = array_map('intval', explode('-', $month));
        $from = sprintf('%04d-%02d-01', $y, $m);
        $to   = date('Y-m-d', strtotime("$from +1 month"));

        // totals
        $stmt = $pdo->prepare("
            SELECT
              SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END) AS income,
              SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
            FROM transactions t
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $t = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['income'=>0,'expense'=>0];

        $income  = (float)($t['income'] ?? 0);
        $expense = (float)($t['expense'] ?? 0);
        $totals = [
            'income'  => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
            'net'     => number_format($income - $expense, 2, '.', ''),
        ];

        // byCategory (note: still grouped by each category, even if a specific category_id is chosen)
        $stmt = $pdo->prepare("
            SELECT c.name AS category_name, t.type, SUM(t.amount) AS total
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
            GROUP BY c.name, t.type
            ORDER BY total DESC
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $byCategory = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // daily series
        $stmt = $pdo->prepare("
            SELECT
              t.txn_date AS date,
              SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END) AS income,
              SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
            FROM transactions t
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
            GROUP BY t.txn_date
            ORDER BY t.txn_date ASC
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // fill all days
        $daily = [];
        $cursor = new \DateTimeImmutable($from);
        $end    = new \DateTimeImmutable($to);
        $byDate = [];
        foreach ($rows as $r) {
            $d = $r['date'];
            $inc = (float)$r['income'];
            $exp = (float)$r['expense'];
            $byDate[$d] = [
                'date'    => $d,
                'income'  => number_format($inc, 2, '.', ''),
                'expense' => number_format($exp, 2, '.', ''),
                'net'     => number_format($inc - $exp, 2, '.', ''),
            ];
        }
        while ($cursor < $end) {
            $d = $cursor->format('Y-m-d');
            $daily[] = $byDate[$d] ?? [
                'date'    => $d,
                'income'  => '0.00',
                'expense' => '0.00',
                'net'     => '0.00',
            ];
            $cursor = $cursor->modify('+1 day');
        }

        Responder::json([
            'totals'     => $totals,
            'byCategory' => $byCategory,
            'daily'      => $daily,
        ]);
    }

    /**
     * GET /reports/year?year=YYYY[&category_id=ID]
     * {
     *   totals: { income, expense, net },
     *   byCategory: [{ category_name, type, total }],
     *   monthly: [{ month:'YYYY-MM', income, expense, net }] // 1..12
     * }
     */
    public function year(): void
    {
        $uid  = Auth::requireAuth();
        $pdo  = DB::pdo();
        $year = $_GET['year'] ?? null;

        if (!$year || !preg_match('/^\d{4}$/', $year)) {
            Responder::error('Invalid or missing year (use YYYY)', 422);
            return;
        }

        // Optional category filter, validate ownership if provided
        $catId = null;
        if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
            $raw = $_GET['category_id'];
            if (!preg_match('/^\d+$/', (string)$raw)) { Responder::error('Invalid category_id', 422); return; }
            $chk = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
            $chk->execute([(int)$raw, $uid]);
            if (!$chk->fetch()) { Responder::error('category_id not found', 404); return; }
            $catId = (int)$raw;
        }

        $from = sprintf('%04d-01-01', (int)$year);
        $to   = sprintf('%04d-01-01', (int)$year + 1);

        // totals
        $stmt = $pdo->prepare("
            SELECT
              SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END) AS income,
              SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
            FROM transactions t
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $t = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['income'=>0,'expense'=>0];

        $income  = (float)($t['income'] ?? 0);
        $expense = (float)($t['expense'] ?? 0);
        $totals = [
            'income'  => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
            'net'     => number_format($income - $expense, 2, '.', ''),
        ];

        // byCategory (year scope)
        $stmt = $pdo->prepare("
            SELECT c.name AS category_name, t.type, SUM(t.amount) AS total
            FROM transactions t
            LEFT JOIN categories c ON c.id = t.category_id
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
            GROUP BY c.name, t.type
            ORDER BY total DESC
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $byCategory = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // monthly rollup
        $stmt = $pdo->prepare("
            SELECT
              DATE_FORMAT(t.txn_date, '%Y-%m') AS ym,
              SUM(CASE WHEN t.type='income'  THEN t.amount ELSE 0 END) AS income,
              SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS expense
            FROM transactions t
            WHERE t.user_id = :uid
              AND t.txn_date >= :from AND t.txn_date < :to
              AND (:catId IS NULL OR t.category_id = :catId)
            GROUP BY DATE_FORMAT(t.txn_date, '%Y-%m')
            ORDER BY ym ASC
        ");
        $stmt->execute([':uid'=>$uid, ':from'=>$from, ':to'=>$to, ':catId'=>$catId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // fill January..December
        $monthly = [];
        $map = [];
        foreach ($rows as $r) {
            $inc = (float)$r['income'];
            $exp = (float)$r['expense'];
            $map[$r['ym']] = [
                'month'   => $r['ym'],
                'income'  => number_format($inc, 2, '.', ''),
                'expense' => number_format($exp, 2, '.', ''),
                'net'     => number_format($inc - $exp, 2, '.', ''),
            ];
        }
        for ($m = 1; $m <= 12; $m++) {
            $ym = sprintf('%04d-%02d', (int)$year, $m);
            $monthly[] = $map[$ym] ?? [
                'month'   => $ym,
                'income'  => '0.00',
                'expense' => '0.00',
                'net'     => '0.00',
            ];
        }

        Responder::json([
            'totals'     => $totals,
            'byCategory' => $byCategory,
            'monthly'    => $monthly,
        ]);
    }
}
