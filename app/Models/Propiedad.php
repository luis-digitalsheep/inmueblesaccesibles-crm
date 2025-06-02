<?php

namespace App\Models;

use App\Services\Auth\PermissionManager;
use App\Services\Database\Database;
use PDO;
use PDOException;

class Propiedad {
  private $db;
  private $permissionManager;

  public function __construct() {
    $this->db = Database::getInstance()->getConnection();
    $this->permissionManager = PermissionManager::getInstance();
  }

  /**
   * Obtiene una lista de propiedades, opcionalmente filtrada y paginada.
   * Esta es la función principal para mostrar el inventario de propiedades.
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
          p.precio_venta,
          p.mapa_url,
          p.estatus_disponibilidad,

          s.nombre as sucursal_nombre,
          adm.nombre as administradora_nombre,
          est.nombre as estado_nombre,
          mun.nombre as municipio_nombre,
          pa_cliente.nombre_completo AS cliente_nombre

        FROM propiedades p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id
        LEFT JOIN cat_estados est ON p.estado_id = est.id
        LEFT JOIN cat_municipios mun ON p.municipio_id = mun.id
        LEFT JOIN clientes pa_cliente ON p.cliente_id = pa_cliente.id
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

      if (isset($filters['estado_id']) && $filters['estado_id'] !== '') {
        $whereClauses[] = "p.estado_id = :filter_estado_id";
        $params[':filter_estado_id'] = (int) $filters['estado_id'];
        $types[':filter_estado_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['municipio_id']) && $filters['municipio_id'] !== '') {
        $whereClauses[] = "p.municipio_id = :filter_municipio_id";
        $params[':filter_municipio_id'] = (int) $filters['municipio_id'];
        $types[':filter_municipio_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['clasificacion_legal_id']) && $filters['clasificacion_legal_id'] !== '') {
        $whereClauses[] = "p.clasificacion_legal_id = :filter_clasificacion_legal_id";
        $params[':filter_clasificacion_legal_id'] = (int) $filters['clasificacion_legal_id'];
        $types[':filter_clasificacion_legal_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['estatus_disponibilidad']) && $filters['estatus_disponibilidad'] !== '') {
        $whereClauses[] = "p.estatus_disponibilidad = :filter_estatus_disponibilidad";
        $params[':filter_estatus_disponibilidad'] = (string) $filters['estatus_disponibilidad'];
        $types[':filter_estatus_disponibilidad'] = PDO::PARAM_STR;
      }

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
   * Obtiene el número total de propiedades, aplicando los mismos filtros de getAll.
   * @param array $filters Un array asociativo de filtros.
   * @return int El número total de propiedades.
   */
  public function getTotalPropiedades(array $filters = []): int {
    try {
      $userSucursalId = $this->permissionManager->getSucursalId();
      $canViewAllProperties = $this->permissionManager->hasPermission('propiedades.ver.todo');

      $sql = "SELECT COUNT(p.id) AS total FROM propiedades p";

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

      if (isset($filters['estado_id']) && $filters['estado_id'] !== '') {
        $whereClauses[] = "p.estado_id = :filter_estado_id";
        $params[':filter_estado_id'] = (int) $filters['estado_id'];
        $types[':filter_estado_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['municipio_id']) && $filters['municipio_id'] !== '') {
        $whereClauses[] = "p.municipio_id = :filter_municipio_id";
        $params[':filter_municipio_id'] = (int) $filters['municipio_id'];
        $types[':filter_municipio_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['clasificacion_legal_id']) && $filters['clasificacion_legal_id'] !== '') {
        $whereClauses[] = "p.clasificacion_legal_id = :filter_clasificacion_legal_id";
        $params[':filter_clasificacion_legal_id'] = (int) $filters['clasificacion_legal_id'];
        $types[':filter_clasificacion_legal_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['estatus_disponibilidad']) && $filters['estatus_disponibilidad'] !== '') {
        $whereClauses[] = "p.estatus_disponibilidad = :filter_estatus_disponibilidad";
        $params[':filter_estatus_disponibilidad'] = (string) $filters['estatus_disponibilidad'];
        $types[':filter_estatus_disponibilidad'] = PDO::PARAM_STR;
      }

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
   * Obtiene los detalles COMPLETOS de una propiedad por su ID,
   * incluyendo todos los datos relacionados de catálogos y el cliente actual asociado.
   *
   * @param int $id El ID de la propiedad.
   * @return array|null El array asociativo de la propiedad con todos sus detalles si la encuentra, o null.
   */
  public function getById(int $id): ?array {
    try {
      $sql = "SELECT
          p.id,
          p.numero_credito,
          p.direccion,
          p.tiempo_entrega,
          p.precio_remate,
          p.precio_venta,
          p.comentarios_admin,
          p.mapa_url,
          p.estatus_disponibilidad,
          p.sucursal_id,
          p.cliente_id,
          p.created_at,
          p.updated_at,
                      
          s.nombre as sucursal_nombre,
          s.abreviatura as sucursal_abreviatura,
          adm.nombre as administradora_nombre,
          adm.abreviatura as administradora_abreviatura,
          est.nombre as estado_nombre,
          mun.nombre as municipio_nombre,
          etj.nombre as estatus_juridico_nombre,
          cl.nombre as clasificacion_legal_nombre,
          tp.nombre as tipo_propietario_nombre,
          tdv.nombre as tipo_documento_venta_nombre,

          c.nombre_completo AS cliente_asociado_nombre,
          c.email AS cliente_asociado_email,
          c.celular AS cliente_asociado_celular,
          (SELECT GROUP_CONCAT(fp.ruta_archivo) FROM fotos_propiedades fp WHERE fp.propiedad_id = p.id) AS fotos_rutas,
          (SELECT COUNT(fp.id) FROM fotos_propiedades fp WHERE fp.propiedad_id = p.id) AS total_fotos

        FROM propiedades p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id
        LEFT JOIN cat_estados est ON p.estado_id = est.id
        LEFT JOIN cat_municipios mun ON p.municipio_id = mun.id
        LEFT JOIN cat_estatus_juridico_propiedad etj ON p.estatus_juridico_id = etj.id
        LEFT JOIN cat_clasificacion_legal_propiedad cl ON p.clasificacion_legal_id = cl.id
        LEFT JOIN cat_tipos_propietario tp ON p.tipo_propietario_id = tp.id
        LEFT JOIN cat_tipos_documento_venta tdv ON p.tipo_documento_venta_id = tdv.id
        LEFT JOIN clientes c ON p.cliente_id = c.id

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
