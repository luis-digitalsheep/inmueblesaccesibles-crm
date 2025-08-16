<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class ValidacionContratoModel
{
    private $db;
    private $tableName = 'contrato_validaciones';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea los registros de validación requeridos para una solicitud de contrato.
     * @param int $solicitudId El ID de la solicitud de contrato.
     * @param array $rolesRequeridos Un array de IDs de los roles que deben validar.
     * @return bool True si se crearon todos los registros.
     */
    public function createValidationRequests(int $solicitudId, array $rolesRequeridos): bool
    {
        $sql = "INSERT INTO {$this->tableName} (solicitud_contrato_id, rol_id_requerido) VALUES (:solicitud_id, :rol_id)";

        try {
            $stmt = $this->db->prepare($sql);

            foreach ($rolesRequeridos as $rolId) {
                $stmt->bindParam(':solicitud_id', $solicitudId, PDO::PARAM_INT);
                $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
                $stmt->execute();
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error en ValidacionContratoModel::createValidationRequests: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las validaciones pendientes para un usuario específico, basado en su rol.
     */
    public function getAllPendingForUser(int $userId, array $userRoles): array
    {
        if (empty($userRoles)) return [];

        $placeholders = implode(',', array_fill(0, count($userRoles), '?'));

        $sql = "SELECT 
                    vc.id, vc.solicitud_contrato_id,
                    sc.created_at as fecha_solicitud,
                    pro.nombre as prospecto_nombre,
                    prop.direccion as propiedad_direccion,
                    u_solicitante.nombre as vendedor_nombre
                FROM {$this->tableName} vc
                JOIN solicitudes_contrato sc ON vc.solicitud_contrato_id = sc.id
                JOIN procesos_venta pv ON sc.proceso_venta_id = pv.id
                JOIN prospectos pro ON pv.prospecto_id = pro.id
                JOIN propiedades prop ON pv.propiedad_id = prop.id
                JOIN usuarios u_solicitante ON sc.solicitado_por_usuario_id = u_solicitante.id
                WHERE vc.estatus_validacion = 'pendiente' AND vc.rol_id_requerido IN ($placeholders)
                ORDER BY sc.created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->execute($userRoles);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ValidacionContratoModel::getAllPendingForUser: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encuentra un registro de validación por su ID.
     * @param int $id
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
            error_log("Error en ValidacionContratoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Aprueba una solicitud de validación específica.
     */
    public function approve(int $validacionId, int $userId, ?string $comentarios): bool
    {
        $sql = "UPDATE {$this->tableName} SET 
                    estatus_validacion = 'aprobado',
                    validado_por_usuario_id = :user_id,
                    comentarios = :comentarios,
                    fecha_validacion = :fecha
                WHERE id = :id AND estatus_validacion = 'pendiente'";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':comentarios', $comentarios);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $validacionId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en ValidacionContratoModel::approve: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el conteo de validaciones pendientes para un usuario/rol específico.
     */
    public function countPendingForUser(array $userRoles): int
    {
        if (empty($userRoles)) return 0;
     
        $placeholders = implode(',', array_fill(0, count($userRoles), '?'));
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE estatus_validacion = 'pendiente' AND rol_id_requerido IN ($placeholders)";
     
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($userRoles);
     
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) { /* ... */
            return 0;
        }
    }
}
