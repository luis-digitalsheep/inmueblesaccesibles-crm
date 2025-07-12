<?php
// app/Models/FolioApartadoModel.php
namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class FolioApartadoModel
{
    private $db;
    private $tableName = 'folios_apartados';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuevo folio de apartado en la base de datos.
     * @param array $data Debe contener: sucursal_id, folio, usuario_propietario_id, estatus_folio_id, etc.
     * @return int|false El ID del nuevo folio creado o false en caso de error.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->tableName} (sucursal_id, folio, usuario_propietario_id, estatus_folio_id, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES (:sucursal_id, :folio, :usuario_propietario_id, :estatus_folio_id, :creado_por, :actualizado_por)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':sucursal_id', $data['sucursal_id'], PDO::PARAM_INT);
            $stmt->bindParam(':folio', $data['folio']);
            $stmt->bindParam(':usuario_propietario_id', $data['usuario_propietario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':estatus_folio_id', $data['estatus_folio_id'], PDO::PARAM_INT); // ej. 1 = "Generado"
            $stmt->bindParam(':creado_por', $data['creado_por_usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':actualizado_por', $data['actualizado_por_usuario_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en FolioApartadoModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la ruta del archivo PDF para un folio específico.
     * @param int $folioId El ID del registro en la tabla folios_apartados.
     * @param string $pdfPath La ruta al archivo PDF guardado en 'storage'.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function updatePdfPath(int $folioId, string $pdfPath): bool
    {
        $sql = "UPDATE {$this->tableName} SET ruta_pdf = :ruta_pdf WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ruta_pdf', $pdfPath);
            $stmt->bindParam(':id', $folioId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en FolioApartadoModel::updatePdfPath: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera el siguiente número de folio para un prefijo específico.
     * @param string $prefix El prefijo (ej. 'GDL-ZEND').
     * @return int El siguiente número de folio.
     */
    public function getNextFolioNumber(string $prefix): int
    {
        // Busca el máximo folio que empiece con el mismo prefijo
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) as max_folio 
                FROM {$this->tableName} 
                WHERE folio LIKE :prefix";
        try {
            $stmt = $this->db->prepare($sql);
            $likePrefix = $prefix . '-%';
            $stmt->bindParam(':prefix', $likePrefix);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result && $result['max_folio']) ? (int)$result['max_folio'] + 1 : 1;
        } catch (PDOException $e) {
            error_log("Error en FolioApartadoModel::getNextFolioNumber: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Encuentra un folio de apartado por su ID.
     * @param int $id El ID del folio.
     * @return array|null Los datos del folio o null si no se encuentra.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, folio, ruta_pdf FROM {$this->tableName} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en FolioApartadoModel::findById: " . $e->getMessage());
            return null;
        }
    }
}
