<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class SucursalModel
{
    private $db;
    private $tableName = 'cat_sucursales';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY nombre ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SucursalModel::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getTotal(): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName}";
        
        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en SucursalModel::getTotal: " . $e->getMessage());
            return 0;
        }
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) { /* ... */
            return null;
        }
    }

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
        } catch (PDOException $e) { /* ... */
            return null;
        }
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->tableName} SET nombre = :nombre, abreviatura = :abreviatura WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':abreviatura', $data['abreviatura']);
        
            return $stmt->execute();
        } catch (PDOException $e) { /* ... */
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
            return $stmt->execute();
        } catch (PDOException $e) { /* ... */
            return false;
        }
    }
}
