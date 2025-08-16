<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class SolicitudContratoModel
{
    private $db;
    private $tableName = 'solicitudes_contrato';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea una nueva solicitud de contrato en la base de datos.
     * @param array $data Datos de la solicitud. Debe contener:
     * - proceso_venta_id
     * - solicitado_por_usuario_id
     * @return int|false El ID de la nueva solicitud o false en caso de error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                    (proceso_venta_id, solicitado_por_usuario_id, estatus_solicitud, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES 
                    (:proceso_venta_id, :solicitado_por, :estatus, :creado_por, :actualizado_por)";
        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindValue(':solicitado_por', $data['solicitado_por_usuario_id'], PDO::PARAM_INT);
            $stmt->bindValue(':estatus', 'pendiente'); // El estado inicial siempre es 'pendiente'
            $stmt->bindValue(':creado_por', $data['solicitado_por_usuario_id'], PDO::PARAM_INT);
            $stmt->bindValue(':actualizado_por', $data['solicitado_por_usuario_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las solicitudes de contrato pendientes.
     * @return array
     */
    public function getAllPending(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT 
                    sc.id, sc.created_at as fecha_solicitud, sc.estatus_solicitud,
                    pv.id as proceso_venta_id,
                    cl.nombre_completo as cliente_nombre,
                    cl.id as cliente_id,
                    prop.direccion as propiedad_direccion,
                    prop.id as propiedad_id,
                    u.nombre as vendedor_nombre
                FROM {$this->tableName} sc
                JOIN procesos_venta pv ON sc.proceso_venta_id = pv.id
                JOIN clientes cl ON pv.cliente_id = cl.id
                JOIN propiedades prop ON pv.propiedad_id = prop.id
                JOIN usuarios u ON pv.usuario_responsable_id = u.id
                WHERE sc.estatus_solicitud = 'pendiente' OR sc.estatus_solicitud = 'en_proceso'
                ORDER BY sc.created_at ASC
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::getAllPending: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo total de solicitudes pendientes para la paginación.
     * @return int
     */
    public function getTotalPending(): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE estatus_solicitud = 'pendiente' OR estatus_solicitud = 'en_proceso'";

        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::getTotalPending: " . $e->getMessage());
            return 0;
        }
    }

    /** 
     * Encuentra una solicitud de contrato por su ID.
     * @param int $id El ID de la solicitud.
     * @return array
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT 
                sc.*, 
                u_asignado.nombre as asignado_a_nombre,
                doc.id as documento_borrador_id
            FROM {$this->tableName} sc
            LEFT JOIN usuarios u_asignado ON sc.asignado_a_usuario_id = u_asignado.id
            LEFT JOIN documentos_clientes doc ON sc.proceso_venta_id = doc.proceso_venta_id AND doc.tipo_documento_id = 8
            WHERE sc.id = :id
            ORDER BY doc.created_at DESC
            LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Asigna una solicitud a un usuario y la marca como "En Proceso".
     * @param int $solicitudId
     * @param int $adminUserId
     * @return bool
     */
    public function assign(int $solicitudId, int $adminUserId): bool
    {
        $sql = "UPDATE {$this->tableName} SET 
                asignado_a_usuario_id = :admin_id, 
                estatus_solicitud = 'en_proceso' 
            WHERE id = :id AND estatus_solicitud = 'pendiente'";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':admin_id', $adminUserId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $solicitudId, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::assign: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de una solicitud a "en_validacion" y guarda la ruta del borrador del contrato.
     * @param int $solicitudId El ID de la solicitud de contrato.
     * @param string $rutaContrato La ruta al archivo del borrador del contrato.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function moveToValidation(int $solicitudId, string $rutaContrato): bool
    {
        $sql = "UPDATE {$this->tableName} SET 
                    estatus_solicitud = 'en_validacion', 
                    ruta_contrato_final = :ruta_contrato
                WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ruta_contrato', $rutaContrato);
            $stmt->bindValue(':id', $solicitudId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::moveToValidation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca una solicitud como completada y guarda la ruta del contrato final.
     * @param int $solicitudId
     * @param string $rutaContrato
     * @return bool
     */
    public function complete(int $solicitudId, string $rutaContrato): bool
    {
        $sql = "UPDATE {$this->tableName} SET 
                estatus_solicitud = 'completado', 
                ruta_contrato_final = :ruta_contrato,
                fecha_completado = :fecha
            WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ruta_contrato', $rutaContrato);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindParam(':id', $solicitudId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::complete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si todas las validaciones para una solicitud de contrato están aprobadas.
     * @param int $solicitudId
     * @return bool
     */
    public function areAllValidationsApproved(int $solicitudId): bool
    {
        // Esta consulta cuenta cuántas validaciones para esta solicitud NO están 'aprobadas'.
        // Si el conteo es 0, significa que todas lo están.
        $sql = "SELECT COUNT(id) FROM contrato_validaciones 
            WHERE solicitud_contrato_id = :solicitud_id AND estatus_validacion != 'aprobado'";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':solicitud_id', $solicitudId, PDO::PARAM_INT);
            $stmt->execute();
            $pendingCount = (int) $stmt->fetchColumn();

            return $pendingCount === 0;
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::areAllValidationsApproved: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el conteo de todas las solicitudes de contrato pendientes (para Dirección).
     */
    public function countAllPending(): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE estatus_solicitud = 'pendiente' OR estatus_solicitud = 'en_proceso'";
     
        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en SolicitudContratoModel::countAllPending: " . $e->getMessage());
            return 0;
        }
    }
}
