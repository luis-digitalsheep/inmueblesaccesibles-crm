<?php

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', (bool) ($_ENV['APP_DEBUG'] ?? false));
define('APP_URL', $_ENV['APP_URL'] ?? null);

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'inmuebles_db');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (APP_DEBUG) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  error_reporting(0);
}
