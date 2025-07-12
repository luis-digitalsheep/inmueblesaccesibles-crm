<?php
// app/Models/DocumentoModel.php
namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class DocumentoModel
{
    private $db;
    private $tableName = 'documentos_clientes';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene los documentos de un prospecto específico.
     * @param int $prospectoId El ID del prospecto.
     * @return array Lista de documentos.
     */
    public function getByProspectoId(int $prospectoId): array
    {
        $sql = "SELECT 
                d.id, d.nombre_archivo, d.ruta_archivo, d.created_at,
                t.nombre as tipo_documento_nombre,
                d.tipo_documento_id
            FROM {$this->tableName} d
            LEFT JOIN cat_tipos_documento_cliente t ON d.tipo_documento_id = t.id
            WHERE d.prospecto_id = :prospecto_id
            ORDER BY d.created_at DESC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prospecto_id', $prospectoId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::getByProspectoId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encuentra un registro de documento por su ID.
     * @param int $id El ID del documento.
     * @return array|null Un array asociativo con los datos del documento o null si no se encuentra.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT 
                    d.id, 
                    d.prospecto_id,
                    d.cliente_id,
                    d.proceso_venta_id,
                    d.tipo_documento_id,
                    d.nombre_archivo,
                    d.ruta_archivo,
                    d.creado_por_usuario_id,
                    d.created_at,
                    t.nombre as tipo_documento_nombre
                FROM {$this->tableName} d
                LEFT JOIN cat_tipos_documento_cliente t ON d.tipo_documento_id = t.id
                WHERE d.id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los documentos asociados a un proceso de venta.
     * @param int $procesoVentaId
     * @return array
     */
    public function findByProcesoVentaId(int $procesoVentaId): array
    {
        $sql = "SELECT 
                    d.id, d.nombre_archivo, d.ruta_archivo, d.created_at,
                    t.nombre as tipo_documento_nombre
                FROM documentos_clientes d
                LEFT JOIN cat_tipos_documento_cliente t ON d.tipo_documento_id = t.id
                WHERE d.proceso_venta_id = :proceso_venta_id
                ORDER BY d.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':proceso_venta_id', $procesoVentaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::findByProcesoVentaId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo registro de documento para un prospecto.
     * @param array $data Datos del documento. Debe contener:
     * - prospecto_id
     * - tipo_documento_id
     * - nombre_archivo
     * - ruta_archivo
     * - creado_por_usuario_id
     * @return int|false El ID del nuevo registro del documento o false en caso de error.
     */
    public function createForProspecto(int $id_usuario, array $data)
    {
        error_log("" . $id_usuario);
        error_log(json_encode($data));

        $sql = "INSERT INTO {$this->tableName} 
                (prospecto_id, tipo_documento_id, nombre_archivo, ruta_archivo, subido_por_usuario_id, creado_por_usuario_id) 
            VALUES 
                (:prospecto_id, :tipo_documento_id, :nombre_archivo, :ruta_archivo, :subido_por_usuario_id, :creado_por_usuario_id)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':prospecto_id', $data['prospecto_id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo_documento_id', $data['tipo_documento_id'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_archivo', $data['nombre_archivo']);
            $stmt->bindParam(':ruta_archivo', $data['ruta_archivo']);
            $stmt->bindParam(':subido_por_usuario_id', $id_usuario);
            $stmt->bindParam(':creado_por_usuario_id', $id_usuario);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::createForProspecto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo registro de documento para un PROCESO DE VENTA específico.
     * @param array $data Datos del documento. Debe contener:
     * - proceso_venta_id
     * - tipo_documento_id
     * - nombre_archivo
     * - ruta_archivo
     * - subido_por_usuario_id
     * @return int|false El ID del nuevo registro del documento o false en caso de error.
     */
    public function createForProceso(array $data)
    {
        $sql = "INSERT INTO documentos_clientes 
                    (proceso_venta_id, tipo_documento_id, nombre_archivo, ruta_archivo, creado_por_usuario_id, subido_por_usuario_id) 
                VALUES 
                    (:proceso_venta_id, :tipo_documento_id, :nombre_archivo, :ruta_archivo, :creado_por_usuario_id, :subido_por_usuario_id)";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':proceso_venta_id', $data['proceso_venta_id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo_documento_id', $data['tipo_documento_id'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre_archivo', $data['nombre_archivo']);
            $stmt->bindParam(':ruta_archivo', $data['ruta_archivo']);
            $stmt->bindParam(':creado_por_usuario_id', $data['subido_por_usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':subido_por_usuario_id', $data['subido_por_usuario_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::createForProceso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los documentos asociados a un cliente (globales y de sus procesos).
     * @param int $clienteId
     * @return array
     */
    public function findAllByClienteId(int $clienteId): array
    {

        $prospectoSubQuery = "SELECT id FROM prospectos WHERE cliente_id = :cliente_id_for_prospecto";
        $procesosSubQuery = "SELECT id FROM procesos_venta WHERE cliente_id = :cliente_id_for_proceso_venta";

        $sql = "SELECT 
                d.id, d.nombre_archivo, d.ruta_archivo, d.created_at, d.proceso_venta_id,
                t.nombre as tipo_documento_nombre
            FROM documentos_clientes d
            LEFT JOIN cat_tipos_documento_cliente t ON d.tipo_documento_id = t.id
            WHERE d.prospecto_id IN ({$prospectoSubQuery}) 
               OR d.proceso_venta_id IN ({$procesosSubQuery})
            ORDER BY d.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id_for_prospecto', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':cliente_id_for_proceso_venta', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en DocumentoModel::findAllByClienteId: " . $e->getMessage());
            return [];
        }
    }
}
