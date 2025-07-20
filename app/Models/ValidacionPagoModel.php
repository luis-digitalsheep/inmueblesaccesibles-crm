<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class ValidacionPagoModel
{
    private $db;
    private $tableName = 'pagos_apartado';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea una nueva solicitud de validación de pago.
     * @param array $data Datos de la solicitud.
     * @return int|false El ID de la nueva solicitud o false en caso de error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                    (proceso_venta_id, documento_comprobante_id, monto, fecha_pago, estatus_validacion, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES 
                    (:proceso_venta_id, :documento_comprobante_id, :monto, :fecha_pago, :estatus_validacion, :creado_por_usuario_id, :actualizado_por_usuario_id)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindValue(':monto', 10000);
            $stmt->bindValue(':fecha_pago', date('Y-m-d H:i:s'));
            $stmt->bindValue(':documento_comprobante_id', $data['documento_comprobante_id'], PDO::PARAM_INT);
            $stmt->bindValue(':estatus_validacion', 'pendiente');
            $stmt->bindValue(':creado_por_usuario_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':actualizado_por_usuario_id', $data['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en ValidacionPagoModel::create: " . $e->getMessage());
            return false;
        }
    }
}
