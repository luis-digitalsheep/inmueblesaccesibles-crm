<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class PropiedadFotoModel
{
    private $db;
    private $tableName = 'fotos_propiedades';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuevo registro para una foto de propiedad.
     * @param int $usuario_id El ID del usuario que sube la foto.
     * @param array $data Datos de la foto. Debe contener: propiedad_id, ruta_archivo, nombre_archivo.
     * @return int|false El ID del nuevo registro o false en caso de error.
     */
    public function create(int $usuario_id, array $data)
    {
        $sql = "INSERT INTO {$this->tableName} 
                (propiedad_id, ruta_archivo, nombre_archivo, orden, creado_por_usuario_id) 
            VALUES 
                (:propiedad_id, :ruta_archivo, :nombre_archivo, :orden, :creado_por_usuario_id)
        ";

        try {
            $stmt = $this->db->prepare($sql);

            $orden = $data['orden'] ?? 0;
            // El usuario que sube la foto (si tienes esa lógica implementada).

            $stmt->bindParam(':propiedad_id', $data['propiedad_id'], PDO::PARAM_INT);
            $stmt->bindParam(':ruta_archivo', $data['ruta_archivo']);
            $stmt->bindParam(':nombre_archivo', $data['nombre_archivo']);
            $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
            $stmt->bindParam(':creado_por_usuario_id', $usuario_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en PropiedadFotoModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las fotos asociadas a una propiedad.
     * @param int $propiedadId
     * @return array
     */
    public function findByPropiedadId(int $propiedadId): array
    {
        $sql = "SELECT id, ruta_archivo, nombre_archivo, orden 
            FROM {$this->tableName} 
            WHERE propiedad_id = :propiedad_id 
            ORDER BY orden ASC
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':propiedad_id', $propiedadId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PropiedadFotoModel::findByPropiedadId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina una foto por su ID.
     * @param int $fotoId
     * @return bool
     */
    public function delete(int $fotoId): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $fotoId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PropiedadFotoModel::delete: " . $e->getMessage());
            return false;
        }
    }
}
