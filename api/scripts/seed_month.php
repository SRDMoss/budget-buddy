<?php
// api/scripts/seed_month.php
declare(strict_types=1);

/**
 * Seed one month of rich demo data.
 *
 * Usage:
 *   php api/scripts/seed_month.php --month=2024-06 --email=demo@bb.local --password=password --clear
 *
 * Options:
 *   --month=YYYY-MM   (required)
 *   --email=...       (default: demo@bb.local)
 *   --password=...    (optional; sets/overwrites password for that user)
 *   --display=...     (default: Demo User)
 *   --clear           (delete existing txns for that user in the month before inserting)
 */

error_reporting(E_ALL & ~E_NOTICE);

$apiRoot = realpath(__DIR__ . '/..');
if ($apiRoot === false) {
  fwrite(STDERR, "Unable to resolve API root.\n");
  exit(1);
}

// Composer autoload + dotenv
require $apiRoot . '/vendor/autoload.php';
if (class_exists(\Dotenv\Dotenv::class)) {
  \Dotenv\Dotenv::createImmutable($apiRoot)->safeLoad();
}

// --- Parse args (supports --key=value or key=value) ---
$args = [];
parse_str(implode('&', array_map(function ($s) {
  // convert "--key=value" to "key=value"
  if (strpos($s, '--') === 0) $s = substr($s, 2);
  return $s;
}, array_slice($argv, 1))), $args);

$month    = $args['month']    ?? null;
$email    = $args['email']    ?? 'demo@bb.local';
$display  = $args['display']  ?? 'Demo User';
$newPass  = $args['password'] ?? null;
$doClear  = array_key_exists('clear', $args);

// Validate month
if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
  fwrite(STDERR, "Usage: php api/scripts/seed_month.php --month=YYYY-MM [--email=demo@bb.local] [--password=password] [--display='Demo User'] [--clear]\n");
  exit(1);
}

[$yy,$mm] = array_map('intval', explode('-', $month));
$start = new DateTime(sprintf('%04d-%02d-01', $yy, $mm));
$end   = (clone $start)->modify('last day of this month');

// --- DB connect ---
$dsn    = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
  $_ENV['DB_HOST'] ?? '127.0.0.1',
  $_ENV['DB_NAME'] ?? 'budget_buddy'
);
$dbUser = $_ENV['DB_USER'] ?? 'bb_user';
$dbPass = $_ENV['DB_PASS'] ?? '';

$pdo = new PDO($dsn, $dbUser, $dbPass, [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// --- Ensure user (and password if provided) ---
$pdo->beginTransaction();
$uidStmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
$uidStmt->execute([$email]);
$uid = $uidStmt->fetchColumn();

if (!$uid) {
  $hash = password_hash($newPass ?? 'password', PASSWORD_DEFAULT);
  $insUser = $pdo->prepare('INSERT INTO users (email, display_name, password_hash, created_at) VALUES (?,?,?,NOW())');
  $insUser->execute([$email, $display, $hash]);
  $uid = (int)$pdo->lastInsertId();
} elseif ($newPass) {
  $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
      ->execute([password_hash($newPass, PASSWORD_DEFAULT), $uid]);
}
$pdo->commit();

// --- Ensure categories (per-user unique by name) ---
$catDefs = [
  ['Rent',         '#EF4444'],
  ['Utilities',    '#3B82F6'],
  ['Groceries',    '#10B981'],
  ['Dining',       '#F59E0B'],
  ['Transport',    '#64748B'],
  ['Shopping',     '#8B5CF6'],
  ['Healthcare',   '#DC2626'],
  ['Entertainment','#22C55E'],
  ['Travel',       '#06B6D4'],
  ['Subscriptions','#0EA5E9'],
  ['Salary',       '#22C55E'],
  ['Freelance',    '#84CC16'],
  ['Car Repair',   '#7C3AED'],
  ['Gifts',        '#F472B6'],
  ['Misc',         '#94A3B8'],
];

$getCat = $pdo->prepare('SELECT id FROM categories WHERE user_id=? AND name=? LIMIT 1');
$insCat = $pdo->prepare('INSERT INTO categories (user_id, name, color_hex, is_archived, created_at) VALUES (?,?,?,?,NOW())');
$catId  = [];

foreach ($catDefs as [$name,$hex]) {
  $getCat->execute([$uid, $name]);
  $id = $getCat->fetchColumn();
  if (!$id) {
    $insCat->execute([$uid, $name, $hex, 0]);
    $id = (int)$pdo->lastInsertId();
  }
  $catId[$name] = (int)$id;
}

// --- Optionally clear the month ---
if ($doClear) {
  $pdo->prepare('DELETE FROM transactions WHERE user_id=? AND txn_date BETWEEN ? AND ?')
      ->execute([$uid, $start->format('Y-m-d'), $end->format('Y-m-d')]);
}

// --- Helpers ---
$seed = crc32($month . '|' . $email);
mt_srand($seed);

$pick = function(array $arr) { return $arr[array_rand($arr)]; };
$randAmt = function(float $min, float $max, int $cents=2) {
  $v = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
  return round($v, $cents);
};

$expenseCats = array_values(array_diff(array_keys($catId), ['Salary','Freelance']));
$payees = [
  'Market','Supermart','Corner Shop','Bistro','Cafe','Restaurant','Rideshare',
  'Fuel Station','Pharmacy','Cinema','Streaming','Gym','Bookstore','Electronics',
  'Hardware','Online Shop','Bakery','Deli','Hotel','Airline'
];

// --- Insert transactions ---
$ins = $pdo->prepare('
  INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at)
  VALUES (?,?,?,?,?,?,?,?,NOW())
');

$total = 0;
$pdo->beginTransaction();

$rentDay = 3;   // rent on 3rd
$utilDay = 10;  // utils near 10th (±2)
$subDay  = 5;   // subs on 5th

$cur = clone $start;
while ($cur <= $end) {
  $ymd = $cur->format('Y-m-d');
  $day = (int)$cur->format('j');
  $dow = (int)$cur->format('N'); // 1..7

  // Income: salary on 1st and 15th
  if ($day === 1 || $day === 15) {
    $gross = $randAmt(3800, 4300);
    $ins->execute([$uid, $catId['Salary'], 'income', $gross, 'USD', $ymd, 'Employer', 'Salary']);
    $total++;
  }

  // Occasional freelance (roughly 1 time every ~2 months, randomized per day)
  if (mt_rand(1, 60) === 1) {
    $ins->execute([$uid, $catId['Freelance'], 'income', $randAmt(400, 1800), 'USD', $ymd, 'Client', 'Freelance payment']);
    $total++;
  }

  // Fixed costs
  if ($day === $rentDay) {
    $ins->execute([$uid, $catId['Rent'], 'expense', $randAmt(1400, 1700), 'USD', $ymd, 'Landlord', 'Monthly rent']);
    $total++;
  }
  if (abs($day - $utilDay) <= 2) {
    $ins->execute([$uid, $catId['Utilities'], 'expense', $randAmt(90, 220), 'USD', $ymd, 'Utility Co', 'Utilities']);
    $total++;
  }
  if ($day === $subDay) {
    $ins->execute([$uid, $catId['Subscriptions'], 'expense', $randAmt(15, 45), 'USD', $ymd, 'Various subs', 'Subscriptions']);
    $total++;
  }

  // Daily living: 1–5 expenses
  $count = mt_rand(1, 5);
  for ($i = 0; $i < $count; $i++) {
    $catName = $pick($expenseCats);
    $amt = match ($catName) {
      'Groceries'     => $randAmt(20, 120),
      'Dining'        => $randAmt(10, 80),
      'Transport'     => $randAmt(5, 40),
      'Shopping'      => $randAmt(15, 200),
      'Healthcare'    => $randAmt(10, 180),
      'Entertainment' => $randAmt(8, 60),
      'Travel'        => $randAmt(50, 400),
      'Car Repair'    => $randAmt(80, 600),
      'Gifts'         => $randAmt(10, 150),
      default         => $randAmt(5, 120),
    };
    $ins->execute([$uid, $catId[$catName], 'expense', $amt, 'USD', $ymd, $pick($payees), $catName]);
    $total++;
  }

  // Weekend splurge sometimes
  if ($dow >= 6 && mt_rand(1, 3) === 1) {
    $ins->execute([$uid, $catId['Entertainment'], 'expense', $randAmt(15, 120), 'USD', $ymd, 'Cinema/Bar', 'Weekend fun']);
    $total++;
  }

  $cur->modify('+1 day');
}

$pdo->commit();

echo json_encode([
  'ok'        => true,
  'month'     => $month,
  'user'      => $email,
  'user_id'   => (int)$uid,
  'inserted'  => $total,
  'range'     => [$start->format('Y-m-d'), $end->format('Y-m-d')],
], JSON_PRETTY_PRINT) . PHP_EOL;
