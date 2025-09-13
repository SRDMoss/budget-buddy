<?php
// api/scripts/purge_demo.php
declare(strict_types=1);

/**
 * Purge a user's demo data (transactions first, then categories).
 * Usage:
 *   php api/scripts/purge_demo.php --email=demo@bb.local
 */

error_reporting(E_ALL & ~E_NOTICE);

$apiRoot = realpath(__DIR__ . '/..');
require $apiRoot . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable($apiRoot)->safeLoad();

parse_str(implode('&', array_map(function ($s) {
  if (strpos($s, '--') === 0) $s = substr($s, 2);
  return $s;
}, array_slice($argv, 1))), $args);

$email = $args['email'] ?? 'demo@bb.local';

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

$uid = $pdo->prepare('SELECT id FROM users WHERE email=?');
$uid->execute([$email]);
$uid = $uid->fetchColumn();

if (!$uid) {
  echo json_encode(['ok'=>true,'message'=>'user not found; nothing to purge','email'=>$email]) . PHP_EOL;
  exit(0);
}

$pdo->beginTransaction();

// delete transactions first
$delTx = $pdo->prepare('DELETE FROM transactions WHERE user_id=?');
$delTx->execute([$uid]);

// then categories
$delCat = $pdo->prepare('DELETE FROM categories WHERE user_id=?');
$delCat->execute([$uid]);

$pdo->commit();

echo json_encode(['ok'=>true,'email'=>$email,'user_id'=>(int)$uid,'purged'=>['transactions'=>true,'categories'=>true]]) . PHP_EOL;
