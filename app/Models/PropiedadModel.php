<?php

namespace App\Models;

use App\Services\Database\Database;

use PDO;
use PDOException;

class PropiedadModel
{
  private $db;
  private $tableName = 'propiedades';

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
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
  public function getAll(array $filters = [], int $limit = 0, int $offset = 0): array
  {
    try {
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

        FROM {$this->tableName} p
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
  public function getById(int $id): ?array
  {
    try {
      $sql = "SELECT
          p.id,
          p.numero_credito,
          p.direccion,
          p.tiempo_entrega,
          p.precio_lista,
          p.precio_venta,
          p.cofinavit,
          p.avaluo_administradora,
          p.comentarios_admin,
          p.mapa_url,
          p.estatus_disponibilidad,
          p.sucursal_id,
          p.cliente_id,
          p.created_at,
          p.updated_at,
          p.etapa_judicial,
          p.etapa_judicial_segunda,
                      
          s.nombre as sucursal_nombre,
          s.abreviatura as sucursal_abreviatura,
          adm.nombre as administradora_nombre,
          adm.abreviatura as administradora_abreviatura,
          est.nombre as estado_nombre,
          mun.nombre as municipio_nombre,
          tp.nombre as tipo_propietario_nombre,
          tdv.nombre as tipo_documento_venta_nombre,

          c.nombre_completo AS cliente_asociado_nombre,
          c.email AS cliente_asociado_email,
          c.celular AS cliente_asociado_celular,
          (SELECT GROUP_CONCAT(fp.ruta_archivo) FROM fotos_propiedades fp WHERE fp.propiedad_id = p.id) AS fotos_rutas,
          (SELECT COUNT(fp.id) FROM fotos_propiedades fp WHERE fp.propiedad_id = p.id) AS total_fotos

        FROM {$this->tableName} p
        LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        LEFT JOIN cat_administradoras adm ON p.administradora_id = adm.id
        LEFT JOIN cat_estados est ON p.estado_id = est.id
        LEFT JOIN cat_municipios mun ON p.municipio_id = mun.id
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

  /**
   * Obtiene una lista de propiedades disponibles para ser asignadas en un proceso de venta.
   * @return array
   */
  public function findAllAvailableForSelect(): array
  {
    $sql = "SELECT 
          id,
          direccion,
          precio_venta
      FROM propiedades 
      WHERE estatus_disponibilidad = 'Disponible' 
      ORDER BY direccion ASC
    ";

    try {
      $stmt = $this->db->query($sql);

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en PropiedadModel::findAllAvailableForSelect: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Crea un nuevo registro en la tabla `propiedades` a partir de los datos validados
   * de una propiedad en revisión.
   * @param array $data Los datos del formulario de validación.
   * @return int|false El ID de la nueva propiedad creada o false en caso de error.
   */
  public function createFromRevision(int $id_usuario, array $data)
  {
    $sql = "INSERT INTO {$this->tableName} (
          numero_credito,
          cartera_id,
          direccion,
          direccion_extra,
          fraccionamiento,
          codigo_postal,
          estado_id,
          municipio_id,
          tipo_vivienda,
          tipo_inmueble,
          metros,
          sucursal_id,
          administradora_id,
          cofinavit,
          avaluo_administradora,
          precio_lista,
          precio_venta,
          etapa_judicial,
          etapa_judicial_segunda,
          fecha_etapa_judicial,
          mapa_url,
          estatus_disponibilidad,
          creado_por_usuario_id
      ) VALUES (
          :numero_credito,
          :cartera_id,
          :direccion,
          :direccion_extra,
          :fraccionamiento,
          :codigo_postal,
          :estado_id,
          :municipio_id,
          :tipo_vivienda,
          :tipo_inmueble,
          :metros,
          :sucursal_id,
          :administradora_id,
          :cofinavit,
          :avaluo_administradora,
          :precio_lista,
          :precio_venta,
          :etapa_judicial,
          :etapa_judicial_segunda,
          :fecha_etapa_judicial,
          :mapa_url,
          :estatus_disponibilidad,
          :creado_por_usuario_id
      )
    ";

    try {
      $stmt = $this->db->prepare($sql);

      $stmt->bindValue(':numero_credito', $data['numero_credito'] ?? null);
      $stmt->bindValue(':cartera_id', $data['cartera_id'] ?? null, PDO::PARAM_INT);
      $stmt->bindValue(':direccion', $data['direccion'] ?? null);
      $stmt->bindValue(':direccion_extra', $data['direccion_extra'] ?? null);
      $stmt->bindValue(':fraccionamiento', $data['fraccionamiento'] ?? null);
      $stmt->bindValue(':codigo_postal', $data['codigo_postal'] ?? null);
      $stmt->bindValue(':estado_id', $data['estado_id'] ?? null, PDO::PARAM_INT);
      $stmt->bindValue(':municipio_id', $data['municipio_id'] ?? null, PDO::PARAM_INT);
      $stmt->bindValue(':tipo_vivienda', $data['tipo_vivienda'] ?? null);
      $stmt->bindValue(':tipo_inmueble', $data['tipo_inmueble'] ?? null);
      $stmt->bindValue(':metros', $data['metros'] ?? null);
      $stmt->bindValue(':sucursal_id', $data['sucursal_id'] ?? null, PDO::PARAM_INT);
      $stmt->bindValue(':administradora_id', $data['administradora_id'] ?? null, PDO::PARAM_INT);
      $stmt->bindValue(':cofinavit', $data['cofinavit'] ?? null);
      $stmt->bindValue(':avaluo_administradora', $data['avaluo_administradora'] ?? null);
      $stmt->bindValue(':precio_lista', $data['precio_lista'] ?? null);
      $stmt->bindValue(':precio_venta', $data['precio_venta'] ?? null);
      $stmt->bindValue(':etapa_judicial', $data['etapa_judicial'] ?? null);
      $stmt->bindValue(':etapa_judicial_segunda', $data['etapa_judicial_segunda'] ?? null);
      $stmt->bindValue(':fecha_etapa_judicial', $data['fecha_etapa_judicial'] ?? null);
      $stmt->bindValue(':mapa_url', $data['mapa_url'] ?? null);
      $stmt->bindValue(':estatus_disponibilidad', 'Disponible');
      $stmt->bindValue(':creado_por_usuario_id', $id_usuario, PDO::PARAM_INT);

      if ($stmt->execute()) {
        return (int)$this->db->lastInsertId();
      }
      return false;
    } catch (PDOException $e) {
      error_log("Error en PropiedadModel::createFromRevision: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualiza el estatus de disponibilidad de una propiedad.
   * @param int $propiedadId
   * @param string $newStatus El nuevo estatus (ej. 'En Proceso', 'Disponible').
   * @return bool
   */
  public function updateStatus(int $propiedadId, string $newStatus): bool
  {
    $sql = "UPDATE propiedades SET estatus_disponibilidad = :new_status WHERE id = :id";
    try {
      $stmt = $this->db->prepare($sql);

      $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
      $stmt->bindParam(':id', $propiedadId, PDO::PARAM_INT);
      
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Error en PropiedadModel::updateStatus: " . $e->getMessage());
      return false;
    }
  }
}
