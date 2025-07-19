<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class RolModel
{
    private $db;
    private $tableName = 'cat_roles';
    private $pivotTableName = 'rol_permiso';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene un rol por su ID.
     * @param int $id El ID del rol a buscar.
     * @return array|null
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
            error_log("Error en RolModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los roles.
     * @return array Lista de roles.
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY nombre ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en RolModel::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los IDs de los permisos asignados a un rol específico.
     * @param int $rolId
     * @return array Un array simple de IDs de permiso. Ej: [1, 2, 5, 8]
     */
    public function getPermissionIdsByRoleId(int $rolId): array
    {
        $sql = "SELECT permiso_id FROM {$this->pivotTableName} WHERE rol_id = :rol_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en RolModel::getPermissionIdsByRoleId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sincroniza los permisos para un rol.
     * Elimina los permisos antiguos e inserta los nuevos.
     * @param int $rolId
     * @param array $permissionIds Array de IDs de los permisos a asignar.
     * @return bool
     */
    public function syncPermissions(int $rolId, array $permissionIds): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtDelete = $this->db->prepare("DELETE FROM {$this->pivotTableName} WHERE rol_id = :rol_id");
            $stmtDelete->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
            $stmtDelete->execute();

            if (!empty($permissionIds)) {
                $sqlInsert = "INSERT INTO {$this->pivotTableName} (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)";
                $stmtInsert = $this->db->prepare($sqlInsert);

                foreach ($permissionIds as $permisoId) {
                    $stmtInsert->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':permiso_id', $permisoId, PDO::PARAM_INT);
                    $stmtInsert->execute();
                }
            }

            return $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en RolModel::syncPermissions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo rol.
     * @param array $data Datos del rol. Debe contener: nombre, descripcion.
     * @return int|false El ID del nuevo rol o false en caso de error.
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->tableName} (nombre, descripcion) VALUES (:nombre, :descripcion)";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error en RolModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza un rol existente.
     * @param int $id El ID del rol a actualizar.
     * @param array $data Datos actualizados del rol. Debe contener: nombre, descripcion.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->tableName} SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RolModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un rol por su ID.
     * @param int $id El ID del rol a eliminar.
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RolModel::delete: " . $e->getMessage());
            return false;
        }
    }
}
