<?php

namespace App\Services\Database;

use PDO;
use PDOException;

class Database {
  private static $instance = null;
  private $pdo;

  private function __construct() {
    $host = DB_HOST;
    $db   = DB_DATABASE;
    $user = DB_USERNAME;
    $pass = DB_PASSWORD;
    $port = DB_PORT;
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
      $this->pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
      error_log("Error de conexión a la base de datos: " . $e->getMessage());
      die("Error de conexión a la base de datos. Por favor, revisa los logs.");
    }
  }

  public static function getInstance() {
    if (self::$instance === null) {
      self::$instance = new Database();
    }
    return self::$instance;
  }

  public function getConnection() {
    return $this->pdo;
  }
}
