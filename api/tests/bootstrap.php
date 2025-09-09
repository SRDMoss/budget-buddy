<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load env (safe if .env missing)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// You could swap to a test DB here if/when you add one.
