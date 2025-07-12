<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class PropiedadRevisionModel
{
  private $db;
  private $tableName = 'propiedades_revision';

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
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
  public function getAll(array $filters = [], int $limit = 0, int $offset = 0): array
  {
    try {
      $sql = "SELECT
          p.id,
          p.cartera_id,
          p.numero_credito,
          p.direccion,
          p.precio_lista,
          p.estatus,
          p.estado,
          p.municipio,

          s.nombre as sucursal_nombre,
          adm.nombre as administradora_nombre,
          c.codigo as cartera_codigo,
          c.nombre as cartera_nombre

        FROM {$this->tableName} p
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

      if (isset($filters['cartera_id']) && $filters['cartera_id'] !== '') {
        $whereClauses[] = "p.cartera_id = :filter_cartera_id";
        $params[':filter_cartera_id'] = (int) $filters['cartera_id'];
        $types[':filter_cartera_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['estatus']) && $filters['estatus'] !== '') {
        $whereClauses[] = "p.estatus = :filter_estatus";
        $params[':filter_estatus'] = $filters['estatus'];
        $types[':filter_estatus'] = PDO::PARAM_STR;
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
  public function getTotalPropiedades(array $filters = []): int
  {
    try {
      $sql = "SELECT COUNT(p.id) AS total FROM {$this->tableName} p";

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

      if (isset($filters['cartera_id']) && $filters['cartera_id'] !== '') {
        $whereClauses[] = "p.cartera_id = :filter_cartera_id";
        $params[':filter_cartera_id'] = (int) $filters['cartera_id'];
        $types[':filter_cartera_id'] = PDO::PARAM_INT;
      }

      if (isset($filters['estatus']) && $filters['estatus'] !== '') {
        $whereClauses[] = "p.estatus = :filter_estatus";
        $params[':filter_estatus'] = $filters['estatus'];
        $types[':filter_estatus'] = PDO::PARAM_STR;
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
  public function getById(int $id): ?array
  {
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
          p.metros,
          p.sucursal_id,
          p.administradora_id,
          p.etapa_judicial,
          p.etapa_judicial_segunda,
          p.fecha_etapa_judicial,
          p.cofinavit,
          p.avaluo_administradora,
          p.precio_lista,
          p.estatus,

          c.nombre as cartera_nombre,
          c.id as cartera_id,
          c.codigo as cartera_codigo,

          p.created_at,
          p.updated_at,
                      
          s.nombre as sucursal_nombre,
          s.abreviatura as sucursal_abreviatura,
          adm.nombre as administradora_nombre,
          adm.abreviatura as administradora_abreviatura

        FROM {$this->tableName} p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id
        LEFT JOIN carteras c ON p.cartera_id = c.id

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

  /**
   * Crea un nuevo registro en propiedades_revision.
   * @param array $data Datos de la propiedad a insertar.
   * @return int|false El ID del registro insertado o false en error.
   */
  public function createRevision(int $usuarioId, array $data)
  {
    $sql = "INSERT INTO {$this->tableName} (
        cartera_id,
        numero_credito,
        direccion,
        direccion_extra,
        fraccionamiento,
        codigo_postal,
        estado,
        municipio,
        tipo_vivienda,
        tipo_inmueble,
        metros,
        sucursal_id,
        administradora_id,
        etapa_judicial,
        etapa_judicial_segunda,
        fecha_etapa_judicial,
        cofinavit,
        avaluo_administradora,
        precio_lista,
        estatus,
        creado_por_usuario_id
    ) VALUES (
        :cartera_id,
        :numero_credito,
        :direccion,
        :direccion_extra,
        :fraccionamiento,
        :codigo_postal,
        :estado,
        :municipio,
        :tipo_vivienda,
        :tipo_inmueble,
        :metros,
        :sucursal_id,
        :administradora_id,
        :etapa_judicial,
        :etapa_judicial_segunda,
        :fecha_etapa_judicial,
        :cofinavit,
        :avaluo_administradora,
        :precio_lista,
        :estatus,
        :usuario_id
    )";

    try {
      $stmt = $this->db->prepare($sql);

      $stmt->bindParam(':cartera_id', $data['cartera_id'], PDO::PARAM_INT);
      $stmt->bindParam(':numero_credito', $data['numero_credito']);
      $stmt->bindParam(':direccion', $data['direccion']);
      $stmt->bindParam(':direccion_extra', $data['direccion_extra']);
      $stmt->bindParam(':fraccionamiento', $data['fraccionamiento']);
      $stmt->bindParam(':codigo_postal', $data['codigo_postal']);
      $stmt->bindParam(':estado', $data['estado']);
      $stmt->bindParam(':municipio', $data['municipio']);
      $stmt->bindParam(':tipo_vivienda', $data['tipo_vivienda']);
      $stmt->bindParam(':tipo_inmueble', $data['tipo_inmueble']);
      $stmt->bindParam(':metros', $data['metros']);
      $stmt->bindParam(':sucursal_id', $data['sucursal_id'], PDO::PARAM_INT);
      $stmt->bindParam(':administradora_id', $data['administradora_id'], PDO::PARAM_INT);
      $stmt->bindParam(':etapa_judicial', $data['etapa_judicial']);
      $stmt->bindParam(':etapa_judicial_segunda', $data['etapa_judicial_segunda']);
      $stmt->bindParam(':fecha_etapa_judicial', $data['fecha_etapa_judicial']);
      $stmt->bindParam(':cofinavit', $data['cofinavit']);
      $stmt->bindParam(':avaluo_administradora', $data['avaluo_administradora']);
      $stmt->bindParam(':precio_lista', $data['precio_lista']);
      $stmt->bindParam(':estatus', $data['estatus']);
      $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);

      if ($stmt->execute()) {
        return (int)$this->db->lastInsertId();
      }
      return false;
    } catch (PDOException $e) {
      error_log("Error en PropiedadRevisionModel::createRevision: " . $e->getMessage() . " Data: " . json_encode($data));
      return false;
    }
  }

  /**
   * Actualiza el estatus de un registro de revisión y lo asocia con la propiedad final creada.
   * @param int $revisionId El ID del registro en `propiedades_revision`.
   * @param string $newStatus El nuevo estatus (ej. 'Validado').
   * @param int $finalPropiedadId El ID de la nueva propiedad creada en la tabla `propiedades`.
   * @return bool True si la actualización fue exitosa, false en caso contrario.
   */
  public function updateStatus(int $revisionId, string $newStatus, int $finalPropiedadId): bool
  {
    $sql = "UPDATE propiedades_revision SET 
          estatus = :new_status, 
          propiedad_final_id = :final_propiedad_id 
      WHERE id = :id
    ";

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
      $stmt->bindParam(':final_propiedad_id', $finalPropiedadId, PDO::PARAM_INT);
      $stmt->bindParam(':id', $revisionId, PDO::PARAM_INT);

      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Error en PropiedadRevisionModel::updateStatus: " . $e->getMessage());
      return false;
    }
  }
}
