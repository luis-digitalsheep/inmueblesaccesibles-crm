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

    // Aquí iría un método para crear/guardar el registro del documento
    public function createForProspecto(array $data)
    {
        // Lógica para el INSERT en la tabla `documentos_clientes`
    }
}
