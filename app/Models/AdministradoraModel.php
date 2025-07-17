<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class AdministradoraModel
{
    private $db;
    private $tableName = 'cat_administradoras';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las administradoras con filtros y paginación.
     */
    public function getAll(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tableName}";

        $sql .= " ORDER BY nombre ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AdministradoraModel::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo total de administradoras.
     */
    public function getTotal(array $filters = []): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en AministradoraModel::getTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Busca una administradora por su ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en AdministradoraModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea una nueva administradora.
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->tableName} (nombre, abreviatura) VALUES (:nombre, :abreviatura)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':abreviatura', $data['abreviatura']);
        
            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
        
            return null;
        } catch (PDOException $e) {
            error_log("Error en AdministradoraModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza una administradora existente.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->tableName} SET nombre = :nombre, abreviatura = :abreviatura WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':abreviatura', $data['abreviatura']);
        
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AdministradoraModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una administradora.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AdministradoraModel::delete: " . $e->getMessage());
            return false;
        }
    }
}
