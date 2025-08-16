<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class SeguimientoModel
{
    private $db;
    private $tableName = 'seguimientos_prospectos_clientes';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuevo registro de seguimiento para un prospecto.
     * @param array $data Datos del seguimiento. Debe contener: proceso_venta_id, tipo_interaccion, comentarios, usuario_registra_id.
     * @return int|false El ID del nuevo registro o false en caso de error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                (proceso_venta_id, tipo_interaccion, fecha_interaccion, usuario_registra_id, comentarios, resultado) 
            VALUES 
                (:proceso_venta_id, :tipo_interaccion, :fecha_interaccion, :usuario_registra_id, :comentarios, :resultado)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $fechaActual = date('Y-m-d H:i:s');

            $stmt->bindParam(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo_interaccion', $data['tipo_interaccion']);
            $stmt->bindParam(':fecha_interaccion', $fechaActual);
            $stmt->bindParam(':usuario_registra_id', $data['usuario_registra_id'], PDO::PARAM_INT);
            $stmt->bindParam(':comentarios', $data['comentarios']);
            $stmt->bindParam(':resultado', $data['resultado']);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en SeguimientoModel::createForProspecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los seguimientos de un prospecto específico.
     * @param int $prospectoId El ID del prospecto.
     * @return array Lista de seguimientos.
     */
    public function getByProspectoId(int $prospectoId): array
    {
        $sql = "SELECT 
                s.id, s.tipo_interaccion, s.fecha_interaccion, s.comentarios,
                u.nombre as usuario_nombre
            FROM {$this->tableName} s
            LEFT JOIN usuarios u ON s.usuario_registra_id = u.id
            WHERE s.prospecto_id = :prospecto_id
            ORDER BY s.fecha_interaccion DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prospecto_id', $prospectoId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeguimientoModel::getByProspectoId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los seguimientos de un proceso de venta específico.
     * @param int $procesoVentaId
     * @return array
     */
    public function findByProcesoVentaId(int $procesoVentaId): array
    {
        $sql = "SELECT 
                    s.id, s.tipo_interaccion, s.fecha_interaccion, s.comentarios,
                    u.nombre as usuario_nombre
                FROM seguimientos_prospectos_clientes s
                LEFT JOIN usuarios u ON s.usuario_registra_id = u.id
                WHERE s.proceso_venta_id = :proceso_venta_id
                ORDER BY s.fecha_interaccion DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':proceso_venta_id', $procesoVentaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeguimientoModel::findByProcesoVentaId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los seguimientos de todos los procesos de un cliente.
     * @param int $clienteId
     * @return array
     */
    public function findAllByClienteId(int $clienteId): array
    {
        $procesosSubQuery = "SELECT id FROM procesos_venta WHERE cliente_id = :cliente_id";

        $sql = "SELECT 
                s.id, s.proceso_venta_id, s.tipo_interaccion, s.fecha_interaccion, s.comentarios,
                u.nombre as usuario_nombre
            FROM seguimientos_prospectos_clientes s
            LEFT JOIN usuarios u ON s.usuario_registra_id = u.id
            WHERE s.proceso_venta_id IN ({$procesosSubQuery})
            ORDER BY s.fecha_interaccion DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SeguimientoModel::findAllByClienteId: " . $e->getMessage());
            return [];
        }
    }
}
