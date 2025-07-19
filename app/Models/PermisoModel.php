<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class PermisoModel
{
    private $db;
    private $tableName = 'cat_permisos';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los permisos con filtros y paginación.
     * @param array $filters Filtros para filtrar los resultados.
     * @param int $limit Limite de resultados.
     * @param int $offset Desplazamiento.
     * @return array Lista de permisos.
     */
    public function getAll(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY modulo, nombre ASC LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PermisoModel::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo total de permisos.
     * @return int Conteo total de permisos.
     */
    public function getTotal(): int
    {
        return (int) $this->db->query("SELECT COUNT(id) FROM {$this->tableName}")->fetchColumn();
    }

    /**
     * Crea un nuevo permiso.
     * @param array $data Datos del permiso. Debe contener: nombre, descripcion, modulo.
     * @return int|false El ID del nuevo permiso o false en caso de error.
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
            error_log("Error en PermisoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo permiso.
     * @param array $data Datos del permiso. Debe contener: nombre, descripcion, modulo.
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->tableName} (nombre, descripcion, modulo, accion) VALUES (:nombre, :descripcion, :modulo, :accion)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':modulo', $data['modulo']);
            $stmt->bindParam(':accion', $data['accion']);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error en PermisoModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza un permiso existente.
     * @param int $id El ID del permiso a actualizar.
     * @param array $data Datos actualizados del permiso.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->tableName} SET nombre = :nombre, descripcion = :descripcion, modulo = :modulo, accion = :accion WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':modulo', $data['modulo']);
            $stmt->bindParam(':accion', $data['accion']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PermisoModel::update: " . $e->getMessage());
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
        } catch (PDOException $e) {
            error_log("Error en PermisoModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los permisos y los agrupa por módulo.
     * @return array Array asociativo con los permisos agrupados. ej. ['propiedades' => [...], 'prospectos' => [...]]
     */
    public function getAllGroupedByModule(): array
    {
        $sql = "SELECT id, nombre, descripcion, modulo, accion FROM {$this->tableName} ORDER BY modulo, id";
        try {
            $stmt = $this->db->query($sql);
            $allPermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped = [];

            foreach ($allPermissions as $permiso) {
                $grouped[$permiso['modulo']][] = $permiso;
            }

            return $grouped;
        } catch (PDOException $e) {
            error_log("Error en PermisoModel::getAllGroupedByModule: " . $e->getMessage());
            return [];
        }
    }
}
