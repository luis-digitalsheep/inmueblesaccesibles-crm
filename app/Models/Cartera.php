<?php

namespace App\Models;

use App\Services\Auth\PermissionManager;
use App\Services\Database\Database;
use PDO;
use PDOException;

class Cartera {
  private $db;
  private $permissionManager;

  public function __construct() {
    $this->db = Database::getInstance()->getConnection();
    $this->permissionManager = PermissionManager::getInstance();
  }

  /**
   * Obtiene una lista de propiedades en revisión, opcionalmente filtrada y paginada.
   * Esta es la función principal para mostrar el inventario de propiedades por validar.
   *
   * @param array $filters Un array asociativo de filtros (ej. ['sucursal_id' => 1, 'estado_id' => 14, 'clasificacion_legal_id' => 2]).
   * @param int $limit Límite de resultados por página.
   * @param int $offset Desplazamiento para la paginación.
   * @return array Un array de objetos/arrays asociativos de propiedades. Retorna un array vacío si no hay resultados o si hay un error.
   */
  public function getAll(array $filters = [], int $limit = 0, int $offset = 0): array {
    try {
      $userSucursalId = $_SESSION['sucursal_id'] ?? null;
      $canViewAllProperties = $this->permissionManager->hasPermission('propiedades.ver.todo');

      $sql = "SELECT
          p.id,
          p.numero_credito,
          p.direccion,
          p.precio_lista,
          p.estatus,
          p.estado,
          p.municipio

          s.nombre as sucursal_nombre,
          adm.nombre as administradora_nombre,
          c.nombre as cartera_nombre

        FROM propiedades_revision p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id
        LEFT JOIN carteras c ON p.cartera_id = c.id
      ";

      $whereClauses = [];
      $params = [];
      $types = [];

      // Filtros

      if (isset($filters['sucursal_id']) && $filters['sucursal_id'] !== '') {
        $whereClauses[] = "p.sucursal_id = :filter_sucursal_id";
        $params[':filter_sucursal_id'] = (int) $filters['sucursal_id'];
        $types[':filter_sucursal_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['administradora_id']) && $filters['administradora_id'] !== '') {
        $whereClauses[] = "p.administradora_id = :filter_administradora_id";
        $params[':filter_administradora_id'] = (int) $filters['administradora_id'];
        $types[':filter_administradora_id'] = PDO::PARAM_INT;
      }

      $whereClauses[] = "p.estatus = 'Pendiente'";

      // Revisión de permisos
      if (!$canViewAllProperties) {
        if ($userSucursalId) {
          $whereClauses[] = "p.sucursal_id = :user_sucursal_id";
          $params[':user_sucursal_id'] = (int) $userSucursalId;
          $types[':user_sucursal_id'] = PDO::PARAM_INT;
        } else {
          // No debe ver ninguna propiedad.
          $whereClauses[] = "1 = 0";
        }
      }

      if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
      }

      $sql .= " ORDER BY p.id DESC";

      // Paginación
      if ($limit > 0) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int) $limit;
        $params[':offset'] = (int) $offset;
        $types[':limit'] = PDO::PARAM_INT;
        $types[':offset'] = PDO::PARAM_INT;
      }

      $stmt = $this->db->prepare($sql);

      foreach ($params as $key => $value) {
        $stmt->bindParam($key, $params[$key], $types[$key]);
      }

      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en Propiedad::getAll(): " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene el número total de propiedades pendientes de validación, aplicando los mismos filtros de getAll.
   * @param array $filters Un array asociativo de filtros.
   * @return int El número total de propiedades.
   */
  public function getTotalPropiedades(array $filters = []): int {
    try {
      $userSucursalId = $this->permissionManager->getSucursalId();
      $canViewAllProperties = $this->permissionManager->hasPermission('propiedades.ver.todo');

      $sql = "SELECT COUNT(p.id) AS total FROM propiedades_revision p";

      $whereClauses = [];
      $params = [];
      $types = [];

      if (isset($filters['sucursal_id']) && $filters['sucursal_id'] !== '') {
        $whereClauses[] = "p.sucursal_id = :filter_sucursal_id";
        $params[':filter_sucursal_id'] = (int) $filters['sucursal_id'];
        $types[':filter_sucursal_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['administradora_id']) && $filters['administradora_id'] !== '') {
        $whereClauses[] = "p.administradora_id = :filter_administradora_id";
        $params[':filter_administradora_id'] = (int) $filters['administradora_id'];
        $types[':filter_administradora_id'] = PDO::PARAM_INT;
      }

      $whereClauses[] = "p.estatus = 'Pendiente'";

      if (!$canViewAllProperties) {
        if ($userSucursalId) {
          $whereClauses[] = "p.sucursal_id = :user_sucursal_id";
          $params[':user_sucursal_id'] = (int) $userSucursalId;
          $types[':user_sucursal_id'] = PDO::PARAM_INT;
        } else {
          $whereClauses[] = "1 = 0";
        }
      }

      if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(' AND ', $whereClauses);
      }

      $stmt = $this->db->prepare($sql);
      foreach ($params as $key => $value) {
        $stmt->bindParam($key, $params[$key], $types[$key]);
      }
      $stmt->execute();
      return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
      error_log("Error en Propiedad::getTotalPropiedades(): " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Obtiene los detalles COMPLETOS de una propiedad pendiente de validación por su ID,
   *
   * @param int $id El ID de la propiedad.
   * @return array|null El array asociativo de la propiedad con todos sus detalles si la encuentra, o null.
   */
  public function getById(int $id): ?array {
    try {
      $sql = "SELECT
          p.id,
          p.cartera_id,
          p.numero_credito,
          p.direccion,
          p.direccion_extra,
          p.fraccionamiento,
          p.codigo_postal,
          p.estado,
          p.municipio,
          p.tipo_vivienda,
          p.tipo_inmueble,
          p.sucursal_id,
          p.administradora_id,
          p.cofinavit,
          p.avaluo_administradora
          p.precio_lista,
          p.estatus

          p.created_at,
          p.updated_at,
                      
          s.nombre as sucursal_nombre,
          s.abreviatura as sucursal_abreviatura,
          adm.nombre as administradora_nombre,
          adm.abreviatura as administradora_abreviatura,

        FROM propiedades p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id

        WHERE p.id = :id LIMIT 1
      ";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log("Error en Propiedad::getById({$id}): " . $e->getMessage());
      return null;
    }
  }
}
