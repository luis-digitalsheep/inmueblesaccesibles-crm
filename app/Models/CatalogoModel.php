<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class CatalogoModel
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Obtiene todos los registros de una tabla de catálogo.
   * @param string $tableName El nombre completo de la tabla de catálogo (ej. 'cat_sucursales').
   * @param string $orderByColumn Columna para ordenar los resultados.
   * @return array Un array de registros del catálogo, con 'id' y 'nombre'.
   */
  public function getAll(string $tableName, string $orderByColumn = 'nombre'): array
  {
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

      $sql = "SELECT * FROM `{$tableName}` {$orderClause}";
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
  public function getMunicipiosByEstado(int $estadoId): array
  {
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

  /**
   * Busca un registro en una tabla de catálogo por su nombre.
   * @param string $tableName El nombre de la tabla de catálogo (ej. 'cat_resultados_seguimiento').
   * @param string $name El valor de la columna 'nombre' que se va a buscar.
   * @return array|null Un array asociativo con los datos del registro o null si no se encuentra.
   */
  public function findByName(string $tableName, string $name): ?array
  {
    $allowedTables = [
      'cat_sucursales',
      'cat_administradoras',
      'cat_roles',
      'cat_estatus_prospeccion',
      'cat_resultados_seguimiento'
    ];

    if (!in_array($tableName, $allowedTables)) {
      error_log("Intento de acceso a tabla no permitida en CatalogoModel::findByName: " . $tableName);
      return null;
    }

    $sql = "SELECT * FROM {$tableName} WHERE nombre = :nombre LIMIT 1";

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':nombre', $name, PDO::PARAM_STR);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ?: null;
    } catch (PDOException $e) {
      error_log("Error en CatalogoModel::findByName para la tabla {$tableName}: " . $e->getMessage());
      return null;
    }
  }
}
