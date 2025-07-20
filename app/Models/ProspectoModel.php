<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class ProspectoModel
{
    private $db;
    private $tableName = 'prospectos';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los prospectos con filtros, paginación y datos relacionados.
     * 
     * @param array $filters Filtros opcionales para la búsqueda (ej. nombre, usuario_responsable_id, sucursal_id, etc.).
     * @param int $limit Límite de resultados por página.
     * @param int $offset Desplazamiento para la paginación.
     * @return array Un array de prospectos con datos relacionados. Retorna un array vacío si no hay resultados o si hay un error.
     */
    public function getAll(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT 
                p.id, p.nombre, p.celular, p.email,
                p.created_at, p.updated_at,
                u.nombre as usuario_responsable_nombre,
                s.nombre as sucursal_nombre,
                c.id as cliente_id, c.nombre_completo as cliente_nombre_asociado
            FROM {$this->tableName} p
            LEFT JOIN usuarios u ON p.usuario_responsable_id = u.id
            LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
            LEFT JOIN clientes c ON p.cliente_id = c.id
        ";

        $whereClauses = [];
        $params = [];
        $types = [];

        // Filtros
        if (!empty($filters['nombre'])) {
            $whereClauses[] = "p.nombre LIKE :nombre";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
        }

        if (!empty($filters['usuario_responsable_id'])) {
            $whereClauses[] = "p.usuario_responsable_id = :usuario_responsable_id";
            $params[':usuario_responsable_id'] = (int)$filters['usuario_responsable_id'];
            $types[':usuario_responsable_id'] = PDO::PARAM_INT;
        }

        if (!empty($filters['sucursal_id'])) {
            $whereClauses[] = "p.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$filters['sucursal_id'];
            $types[':sucursal_id'] = PDO::PARAM_INT;
        }

        $sql .= "WHERE p.activo = 1";

        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $types[':limit'] = PDO::PARAM_INT;
            $params[':offset'] = $offset;
            $types[':offset'] = PDO::PARAM_INT;
        }

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value, $types[$key] ?? PDO::PARAM_STR);
            }

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo total de prospectos con filtros.
     * 
     * @param array $filters Filtros opcionales para la búsqueda (ej. nombre, usuario_responsable_id, sucursal_id, etc.).
     * @return int El número total de prospectos que coinciden con los filtros. Retorna 0 si hay un error.
     */
    public function getTotalProspectos(array $filters = []): int
    {
        $sql = "SELECT COUNT(p.id) as total
            FROM {$this->tableName} p
            LEFT JOIN usuarios u ON p.usuario_responsable_id = u.id
            LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
        ";

        $whereClauses = [];
        $params = [];
        $types = [];

        // Filtros
        if (!empty($filters['nombre'])) {
            $whereClauses[] = "p.nombre LIKE :nombre";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
        }

        if (!empty($filters['usuario_responsable_id'])) {
            $whereClauses[] = "p.usuario_responsable_id = :usuario_responsable_id";
            $params[':usuario_responsable_id'] = (int)$filters['usuario_responsable_id'];
            $types[':usuario_responsable_id'] = PDO::PARAM_INT;
        }

        if (!empty($filters['sucursal_id'])) {
            $whereClauses[] = "p.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$filters['sucursal_id'];
            $types[':sucursal_id'] = PDO::PARAM_INT;
        }

        $sql .= "WHERE p.activo = 1";

        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value, $types[$key] ?? PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::getTotalProspectos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Encuentra un prospecto por su ID.
     * 
     * @param int $id El ID del prospecto a buscar.
     * @return array|null Un array asociativo con los datos del prospecto si se encuentra, o null si no existe.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, 
                u.nombre as usuario_responsable_nombre,
                s.nombre as sucursal_nombre,
                c.id as cliente_id, c.nombre_completo as cliente_nombre_asociado 
            FROM {$this->tableName} p
            LEFT JOIN usuarios u ON p.usuario_responsable_id = u.id
            LEFT JOIN cat_sucursales s ON p.sucursal_id = s.id
            LEFT JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = :id
            AND p.activo = 1
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo prospecto.
     * 
     * @param array $data Los datos del prospecto a crear. Debe contener:
     * - nombre
     * - celular
     * - email
     * - usuario_responsable_id (ID del usuario responsable)
     * - sucursal_id (ID de la sucursal)
     * - dial_code (código de país del celular)
     * - pais_code (código de país)
     * @return int|null El ID del nuevo prospecto creado, o null si hubo un error.
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO prospectos 
                    (nombre, celular, email, usuario_responsable_id, sucursal_id, dial_code, pais_code) 
                VALUES 
                    (:nombre, :celular, :email, :usuario_responsable_id, :sucursal_id, :dial_code, :pais_code)";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':celular', $data['celular']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':usuario_responsable_id', $data['usuario_responsable_id'], PDO::PARAM_INT);
            $stmt->bindParam(':sucursal_id', $data['sucursal_id'], PDO::PARAM_INT);
            $stmt->bindParam(':dial_code', $data['dial_code']);
            $stmt->bindParam(':pais_code', $data['pais_code']);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza un prospecto existente.
     * 
     * @param int $id El ID del prospecto a actualizar.
     * @param array $data Los datos a actualizar. Debe contener:
     * - nombre
     * - celular
     * - email
     * - usuario_responsable_id (ID del usuario responsable)
     * - sucursal_id (ID de la sucursal)
     * - dial_code (código de país del celular)
     * - pais_code (código de país)
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->tableName} SET
                nombre = :nombre,
                celular = :celular,
                email = :email,
                usuario_responsable_id = :usuario_responsable_id,
                sucursal_id = :sucursal_id
            WHERE id = :id
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':celular', $data['celular']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':usuario_responsable_id', $data['usuario_responsable_id'], PDO::PARAM_INT);
            $stmt->bindParam(':sucursal_id', $data['sucursal_id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::update: " . $e->getMessage() . " Data: " . json_encode($data));
            return false;
        }
    }

    /**
     * @param int $prospectoId
     * @param int $newStatusId
     * @return bool
     */
    public function updateGlobalStatus(int $prospectoId, int $newStatusId): bool
    {
        $sql = "UPDATE prospectos SET estatus_global_id = :new_status_id WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':new_status_id', $newStatusId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $prospectoId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::updateGlobalStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enlaza un prospecto existente a un registro de cliente recién creado.
     * @param int $prospectoId El ID del prospecto que se va a actualizar.
     * @param int $clienteId El ID del nuevo cliente al que se va a enlazar.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function linkToCliente(int $prospectoId, int $clienteId): bool
    {
        $sql = "UPDATE {$this->tableName} SET cliente_id = :cliente_id WHERE id = :prospecto_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':prospecto_id', $prospectoId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::linkToCliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un prospecto.
     * 
     * @param int $id El ID del prospecto a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->tableName} SET activo = 0 WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProspectoModel::delete: " . $e->getMessage());
            return false;
        }
    }
}
