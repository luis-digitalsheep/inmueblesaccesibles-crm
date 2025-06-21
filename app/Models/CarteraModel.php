<?php

namespace App\Models;

use App\Services\Database\Database;

use PDO;
use PDOException;

/**
 * Clase Cartera
 *
 * Esta clase representa el modelo de la entidad Cartera.
 * Proporciona métodos para obtener información sobre las carteras y cargar carteras mediante archivos.
 */
class CarteraModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las carteras.
     *
     * @param array $filters Filtros opcionales para la consulta.
     * @param int $limit Límite de resultados a retornar (0 para sin límite).
     * @param int $offset Desplazamiento para la paginación (0 por defecto).
     * @return array Lista de carteras.
     * @throws \Exception Si ocurre un error al obtener las carteras.
     */
    public function getAll(array $filters = [], int $limit = 0, int $offset = 0)
    {
        try {
            $query = "SELECT 
                    c.id,
                    c.codigo,
                    c.nombre,
                    sucursal_id,
                    administradora_id,
                    suc.nombre AS sucursal_nombre,
                    adm.nombre AS administradora_nombre  
                FROM carteras c
                LEFT JOIN cat_sucursales suc ON c.sucursal_id = suc.id
                LEFT JOIN cat_administradoras adm ON c.administradora_id = adm.id
            ";

            $whereClauses = [];
            $params = [];
            $types = [];

            // Aplicar filtros si existen
            if (isset($filters['codigo']) && $filters['codigo'] !== '') {
                $whereClauses[] = "codigo LIKE :filter_codigo";
                $params[':filter_codigo'] = '%' . $filters['codigo'] . '%';
                $types[':filter_codigo'] = PDO::PARAM_STR;
            }

            if (isset($filters['nombre']) && $filters['nombre'] !== '') {
                $whereClauses[] = "nombre LIKE :filter_nombre";
                $params[':filter_nombre'] = '%' . $filters['nombre'] . '%';
                $types[':filter_nombre'] = PDO::PARAM_STR;
            }

            if (isset($filters['sucursal_id']) && $filters['sucursal_id'] !== '') {
                $whereClauses[] = "c.sucursal_id = :filter_sucursal_id";
                $params[':filter_sucursal_id'] = (int) $filters['sucursal_id'];
                $types[':filter_sucursal_id'] = PDO::PARAM_INT;
            }

            if (isset($filters['administradora_id']) && $filters['administradora_id'] !== '') {
                $whereClauses[] = "c.administradora_id = :filter_administradora_id";
                $params[':filter_administradora_id'] = (int) $filters['administradora_id'];
                $types[':filter_administradora_id'] = PDO::PARAM_INT;
            }

            // Construir la cláusula WHERE si hay filtros
            if (count($whereClauses) > 0) {
                $query .= " WHERE " . implode(" AND ", $whereClauses);
            }

            // Agregar paginación si se especifica un límite
            if ($limit > 0) {
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
                $types[':limit'] = PDO::PARAM_INT;
                $types[':offset'] = PDO::PARAM_INT;
            }

            // Preparar y ejecutar la consulta
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error en obtener las carteras: " . $e->getMessage());
        }
    }

    public function fileLoad() {}

    public function findOrCreateByCodigo($codigo, $nombre)
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM carteras WHERE codigo = :codigo");
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->execute();
            $cartera = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cartera) {
                return (int)$cartera['id'];
            }

            $stmtInsert = $this->db->prepare("INSERT INTO carteras (codigo, nombre) VALUES (:codigo, :nombre)");
            $stmtInsert->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmtInsert->bindParam(':nombre', $nombre, PDO::PARAM_STR);

            if ($stmtInsert->execute()) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en CarteraDefinitionModel::findOrCreateByCodigo: " . $e->getMessage());
            return false;
        }
    }
}
