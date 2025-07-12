<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class ClienteModel
{
    private $db;
    private $tableName = 'clientes';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuevo cliente a partir de los datos de un prospecto.
     * @param array $prospectoData - Array asociativo con los datos del prospecto.
     * @return int|false El ID del nuevo cliente creado o false en caso de error.
     */
    public function createFromProspecto(array $prospectoData)
    {
        $sql = "INSERT INTO {$this->tableName} 
                    (nombre_completo, celular, email, usuario_responsable_id, sucursal_id, prospecto_origen_id, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES 
                    (:nombre, :celular, :email, :usuario_responsable_id, :sucursal_id, :prospecto_origen_id, :user_id, :user_id)";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':nombre', $prospectoData['nombre']);
            $stmt->bindParam(':celular', $prospectoData['celular']);
            $stmt->bindParam(':email', $prospectoData['email']);
            $stmt->bindParam(':usuario_responsable_id', $prospectoData['usuario_responsable_id'], PDO::PARAM_INT);
            $stmt->bindParam(':sucursal_id', $prospectoData['sucursal_id'], PDO::PARAM_INT);
            $stmt->bindParam(':prospecto_origen_id', $prospectoData['id'], PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $prospectoData['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en ClienteModel::createFromProspecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encuentra un cliente por su ID.
     * @param int $id El ID del cliente.
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT c.*, u.nombre as usuario_responsable_nombre, s.nombre as sucursal_nombre
                FROM {$this->tableName} c
                LEFT JOIN usuarios u ON c.usuario_responsable_id = u.id
                LEFT JOIN cat_sucursales s ON c.sucursal_id = s.id
                WHERE c.id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en ClienteModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los clientes con filtros y paginación.
     */
    public function getAll(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT c.id, c.nombre_completo, c.celular, c.email, s.nombre as sucursal_nombre, u.nombre as usuario_responsable_nombre
                FROM {$this->tableName} c
                LEFT JOIN cat_sucursales s ON c.sucursal_id = s.id
                LEFT JOIN usuarios u ON c.usuario_responsable_id = u.id";

        $whereClauses = [];
        $params = [];
        // TODO: Implementar lógica de filtros similar a la de ProspectoModel
        if (!empty($filters['nombre'])) {
            $whereClauses[] = "c.nombre_completo LIKE :nombre";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
        }

        if (!empty($filters['sucursal_id'])) {
            $whereClauses[] = "c.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$filters['sucursal_id'];
        }

        if (!empty($whereClauses)) $sql .= " WHERE " . implode(' AND ', $whereClauses);
        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) $stmt->bindValue($key, $value);

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene el conteo total de clientes que coinciden con los filtros aplicados.
     * @param array $filters - Array asociativo con los filtros.
     * @return int El número total de clientes.
     */
    public function getTotalClientes(array $filters = []): int
    {
        $sql = "SELECT COUNT(c.id) as total
                FROM {$this->tableName} c
                LEFT JOIN cat_sucursales s ON c.sucursal_id = s.id
                LEFT JOIN usuarios u ON c.usuario_responsable_id = u.id";

        $whereClauses = [];
        $params = [];

        if (!empty($filters['nombre'])) {
            $whereClauses[] = "c.nombre_completo LIKE :nombre";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
        }
     
        if (!empty($filters['sucursal_id'])) {
            $whereClauses[] = "c.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$filters['sucursal_id'];
        }
     
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error en ClienteModel::getTotalClientes: " . $e->getMessage());
            return 0;
        }
    }
}
