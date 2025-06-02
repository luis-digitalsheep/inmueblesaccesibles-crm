<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class Catalogo {
  private $db;

  public function __construct() {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Obtiene todos los registros de una tabla de catálogo.
   * @param string $tableName El nombre completo de la tabla de catálogo (ej. 'cat_sucursales').
   * @param string $orderByColumn Columna para ordenar los resultados.
   * @return array Un array de registros del catálogo, con 'id' y 'nombre'.
   */
  public function getAll(string $tableName, string $orderByColumn = 'nombre'): array {
    try {
      if (!preg_match('/^cat_[a-z0-9_]+$/', $tableName)) {
        error_log("Intento de acceso a tabla de catálogo inválida: {$tableName}");
        return [];
      }

      if (!preg_match('/^[a-z_]+$/', $orderByColumn)) {
        error_log("Columna de ordenamiento inválida: {$orderByColumn} para tabla {$tableName}");
        return [];
      }

      $orderClause = "ORDER BY `{$orderByColumn}` ASC";

      try {
        $checkOrderStmt = $this->db->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName AND COLUMN_NAME = 'orden'");
        $checkOrderStmt->bindParam(':tableName', $tableName, PDO::PARAM_STR);
        $checkOrderStmt->execute();
        if ($checkOrderStmt->fetch()) {
          $orderClause = "ORDER BY `orden` ASC, `nombre` ASC";
        }
      } catch (PDOException $e) {
        error_log("Error al verificar columna 'orden' en {$tableName}: " . $e->getMessage());
      }

      $sql = "SELECT id, nombre FROM `{$tableName}` {$orderClause}";
      $stmt = $this->db->query($sql);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener catálogo {$tableName}: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene municipios filtrados por estado_id.
   * Método específico para cat_municipios.
   * @param int $estadoId El ID del estado.
   * @return array Un array de municipios.
   */
  public function getMunicipiosByEstado(int $estadoId): array {
    try {
      $sql = "SELECT id, nombre FROM cat_municipios WHERE estado_id = :estado_id ORDER BY nombre ASC";
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':estado_id', $estadoId, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener municipios por estado {$estadoId}: " . $e->getMessage());
      return [];
    }
  }
}
