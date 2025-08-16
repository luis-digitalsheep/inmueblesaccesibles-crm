<?php
// app/Models/PagoModel.php
namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class PagoModel
{
    private $db;
    private $tableName = 'pagos';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea una nueva solicitud de validación de pago en la base de datos.
     * @param array $data Datos de la solicitud.
     * @return int|false El ID de la nueva solicitud o false en caso de error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                    (proceso_venta_id, tipo_pago_id, monto, fecha_pago, documento_comprobante_id, estatus_validacion, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES 
                    (:proceso_venta_id, :tipo_pago_id, :monto, :fecha_pago, :documento_comprobante_id, :estatus_validacion, :creado_por, :actualizado_por)";
        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_pago_id', $data['tipo_pago_id'], PDO::PARAM_INT);
            $stmt->bindValue(':monto', $data['monto']); // El monto viene del controlador
            $stmt->bindValue(':fecha_pago', date('Y-m-d')); // Fecha actual
            $stmt->bindValue(':documento_comprobante_id', $data['documento_comprobante_id'], PDO::PARAM_INT);
            $stmt->bindValue(':estatus_validacion', 'pendiente'); // Estado inicial
            $stmt->bindValue(':creado_por', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':actualizado_por', $data['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en PagoModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los pagos pendientes de validación.
     */
    public function getAllPending(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT 
                    p.id, p.monto, p.fecha_pago, p.estatus_validacion, p.documento_comprobante_id,
                    pv.id as proceso_venta_id,
                    pro.nombre as prospecto_nombre,
                    prop.direccion as propiedad_direccion,
                    doc.ruta_archivo as ruta_comprobante
                FROM {$this->tableName} p
                JOIN procesos_venta pv ON p.proceso_venta_id = pv.id
                JOIN prospectos pro ON pv.prospecto_id = pro.id
                JOIN propiedades prop ON pv.propiedad_id = prop.id
                JOIN documentos_clientes doc ON p.documento_comprobante_id = doc.id
                WHERE p.estatus_validacion = 'pendiente'
                ORDER BY p.created_at ASC 
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PagoModel::getAllPending: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo total de pagos pendientes.
     */
    public function getTotalPending(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->tableName} p JOIN procesos_venta pv ON p.proceso_venta_id = pv.id
                JOIN prospectos pro ON pv.prospecto_id = pro.id
                JOIN propiedades prop ON pv.propiedad_id = prop.id
                JOIN documentos_clientes doc ON p.documento_comprobante_id = doc.id
                WHERE p.estatus_validacion = 'pendiente'
                ORDER BY p.created_at ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error en PagoModel::getTotalPending: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Encuentra un registro de pago por su ID.
     * @param int $id El ID del pago.
     * @return array|null Un array asociativo con los datos del pago o null si no se encuentra.
     */
    public function findById(int $id): ?array
    {
        // La consulta une otras tablas para obtener información contextual útil,
        // como el ID del prospecto, que es necesario para la conversión.
        $sql = "SELECT 
                    p.*,
                    pv.prospecto_id
                FROM {$this->tableName} p
                JOIN procesos_venta pv ON p.proceso_venta_id = pv.id
                WHERE p.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en PagoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza el estado de una validación de pago.
     * @param int $pagoId
     * @param string $nuevoEstatus ('aprobado' o 'rechazado')
     * @param int $adminUserId
     * @param string|null $notas
     * @return bool
     */
    public function updateValidationStatus(int $pagoId, string $nuevoEstatus, int $adminUserId, string $notas = ""): bool
    {
        $sql = "UPDATE {$this->tableName} SET
                    estatus_validacion = :estatus,
                    validado_por_usuario_id = :admin_id,
                    notas_validacion = :notas,
                    fecha_validacion = :fecha
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estatus', $nuevoEstatus);
            $stmt->bindValue(':admin_id', $adminUserId, PDO::PARAM_INT);
            $stmt->bindValue(':notas', $notas);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $pagoId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PagoModel::updateValidationStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de una validación de pago a 'rechazado'.
     */
    public function reject(int $pagoId, int $adminUserId, string $notas): bool
    {
        $sql = "UPDATE {$this->tableName} SET
                estatus_validacion = 'rechazado',
                validado_por_usuario_id = :admin_id,
                notas_validacion = :notas,
                fecha_validacion = :fecha
            WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':admin_id', $adminUserId, PDO::PARAM_INT);
            $stmt->bindValue(':notas', $notas);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $pagoId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PagoModel::reject: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el conteo de todos los pagos pendientes de validación (para Dirección).
     */
    public function countAllPending(): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE estatus_validacion = 'pendiente'";
     
        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en PagoModel::countAllPending: " . $e->getMessage());
            return 0;
        }
    }
}
