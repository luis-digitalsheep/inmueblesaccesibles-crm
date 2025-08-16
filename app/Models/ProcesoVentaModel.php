<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class ProcesoVentaModel
{
    private $db;
    private $tableName = 'procesos_venta';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuevo proceso de venta para un prospecto.
     * @param array $data Debe contener: prospecto_id, propiedad_id, estatus_proceso_id, usuario_responsable_id.
     * @return int|false El ID del nuevo proceso o false en error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                (prospecto_id, propiedad_id, estatus_proceso_id, usuario_responsable_id, is_active) 
            VALUES 
                (:prospecto_id, :propiedad_id, :estatus_proceso_id, :usuario_responsable_id, 1)
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prospecto_id', $data['prospecto_id'], PDO::PARAM_INT);
            $stmt->bindParam(':propiedad_id', $data['propiedad_id'], PDO::PARAM_INT);
            $stmt->bindParam(':estatus_proceso_id', $data['estatus_proceso_id'], PDO::PARAM_INT);
            $stmt->bindParam(':usuario_responsable_id', $data['usuario_responsable_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los procesos de venta asociados a un prospecto.
     * @param int $prospectoId
     * @return array
     */
    public function findAllByProspectoId(int $prospectoId): array
    {
        $sql = "SELECT 
                    pv.id, pv.estatus_proceso_id, pv.is_active, pv.created_at,
                    p.id as propiedad_id, p.direccion as propiedad_direccion,
                    cep.nombre as estatus_nombre
                FROM procesos_venta pv
                LEFT JOIN propiedades p ON pv.propiedad_id = p.id
                LEFT JOIN cat_estatus_prospeccion cep ON pv.estatus_proceso_id = cep.id
                WHERE pv.prospecto_id = :prospecto_id
                ORDER BY pv.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':prospecto_id', $prospectoId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::findAllByProspectoId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encuentra un proceso de venta por su ID y devuelve todos los datos relacionados.
     * @param int $id El ID del proceso de venta.
     * @return array|null El proceso de venta como un array asociativo, o null si no se encuentra.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT 
                pv.id, 
                pv.prospecto_id,
                pv.cliente_id,
                pv.propiedad_id,
                pv.estatus_proceso_id,
                pv.usuario_responsable_id,
                pv.folio_apartado,
                pv.is_active,
                pv.created_at,
                pv.updated_at,
                pro.nombre as prospecto_nombre,
                cli.nombre_completo as cliente_nombre,
                prop.direccion as propiedad_direccion,
                cep.nombre as estatus_nombre,
                usr.nombre as usuario_responsable_nombre
            FROM {$this->tableName} pv
            LEFT JOIN prospectos pro ON pv.prospecto_id = pro.id
            LEFT JOIN clientes cli ON pv.cliente_id = cli.id
            LEFT JOIN propiedades prop ON pv.propiedad_id = prop.id
            LEFT JOIN cat_estatus_prospeccion cep ON pv.estatus_proceso_id = cep.id
            LEFT JOIN usuarios usr ON pv.usuario_responsable_id = usr.id
            WHERE pv.id = :id
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza el estado de un proceso de venta específico.
     * @param int $procesoVentaId
     * @param int $newStatusId
     * @return bool
     */
    public function updateStatus(int $procesoVentaId, int $newStatusId): bool
    {
        $sql = "UPDATE {$this->tableName} SET estatus_proceso_id = :new_status_id WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':new_status_id', $newStatusId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $procesoVentaId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::updateStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asocia un folio de apartado y actualiza el estado de un proceso de venta.
     * @param int $procesoVentaId
     * @param string $folio
     * @param int $nextStatusId
     * @return bool
     */
    public function assignFolioAndUpdateStatus(int $procesoVentaId, string $folio, int $nextStatusId): bool
    {
        $sql = "UPDATE procesos_venta 
                SET folio_apartado = :folio, estatus_proceso_id = :next_status_id 
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':folio', $folio);
            $stmt->bindParam(':next_status_id', $nextStatusId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $procesoVentaId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::assignFolioAndUpdateStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los datos necesarios para generar el folio y el PDF.
     * @param int $id El ID del proceso de venta.
     * @return array|null Datos del proceso con abreviaturas.
     */
    public function findForFolioGeneration(int $id): ?array
    {
        $sql = "SELECT 
                    pv.id,
                    pv.usuario_responsable_id,
                    pro.nombre as prospecto_nombre,
                    prop.direccion as propiedad_direccion,
                    prop.precio_venta as propiedad_precio_venta,
                    s.abreviatura as sucursal_abreviatura,
                    s.id as sucursal_id,
                    a.abreviatura as administradora_abreviatura
                FROM procesos_venta pv
                JOIN prospectos pro ON pv.prospecto_id = pro.id
                JOIN propiedades prop ON pv.propiedad_id = prop.id
                JOIN cat_sucursales s ON pro.sucursal_id = s.id
                JOIN cat_administradoras a ON prop.administradora_id = a.id
                WHERE pv.id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::findForFolioGeneration: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reasigna todos los procesos de venta de un prospecto a un nuevo cliente.
     * @param int $prospectoId El ID del prospecto de origen.
     * @param int $clienteId El ID del nuevo cliente.
     * @return bool True si la actualización fue exitosa.
     */
    public function reassignProcesosToCliente(int $prospectoId, int $clienteId): bool
    {
        $sql = "UPDATE procesos_venta SET cliente_id = :cliente_id WHERE prospecto_id = :prospecto_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindParam(':prospecto_id', $prospectoId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::reassignProcesosToCliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los procesos de venta asociados a un cliente.
     * @param int $clienteId
     * @return array
     */
    public function findAllByClienteId(int $clienteId): array
    {
        $sql = "SELECT 
                pv.id, pv.estatus_proceso_id, pv.is_active, pv.created_at,
                p.id as propiedad_id, p.direccion as propiedad_direccion,
                cep.nombre as estatus_nombre
            FROM procesos_venta pv
            LEFT JOIN propiedades p ON pv.propiedad_id = p.id
            LEFT JOIN cat_estatus_prospeccion cep ON pv.estatus_proceso_id = cep.id
            WHERE pv.cliente_id = :cliente_id
            ORDER BY pv.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::findAllByClienteId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encuentra un proceso de venta a partir del ID de un pago asociado.
     * @param int $pagoId
     * @return array|null
     */
    public function findByPagoId(int $pagoId): ?array
    {
        $sql = "SELECT pv.* FROM procesos_venta pv
            JOIN pagos p ON pv.id = p.proceso_venta_id
            WHERE p.id = :pago_id";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':pago_id', $pagoId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::findByPagoId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el conteo de procesos de venta activos para un usuario.
     * @param int $userId
     * @return int
     */
    public function countActiveByUser(int $userId): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE usuario_responsable_id = :user_id AND is_active = 1";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::countActiveByUser: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene el conteo de todos los procesos de venta activos en el sistema.
     * @return int
     */
    public function countAllActive(): int
    {
        $sql = "SELECT COUNT(id) FROM {$this->tableName} WHERE is_active = 1";
     
        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ProcesoVentaModel::countAllActive: " . $e->getMessage());
            return 0;
        }
    }
}
