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
                    (proceso_venta_id, documento_comprobante_id, estatus_validacion, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES 
                    (:proceso_venta_id, :documento_comprobante_id, :estatus_validacion, :user_id, :user_id)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindParam(':documento_comprobante_id', $data['documento_comprobante_id'], PDO::PARAM_INT);
            $stmt->bindValue(':estatus_validacion', 'pendiente'); // El estado inicial siempre es pendiente
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);

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
