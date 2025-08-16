<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class NotificacionModel
{
    private $db;
    private $tableName = 'notificaciones';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): ?int
    {
        $sql = "INSERT INTO {$this->tableName} (usuario_destinatario_id, mensaje, tipo_notificacion, url_destino, entidad_relacionada, entidad_id) 
                VALUES (:destinatario, :mensaje, :tipo, :url, :entidad, :entidad_id)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':destinatario', $data['usuario_destinatario_id']);
            $stmt->bindValue(':mensaje', $data['mensaje']);
            $stmt->bindValue(':tipo', $data['tipo_notificacion']);
            $stmt->bindValue(':url', $data['url_destino']);
            $stmt->bindValue(':entidad', $data['entidad_relacionada']);
            $stmt->bindValue(':entidad_id', $data['entidad_id']);

            if ($stmt->execute()) return (int)$this->db->lastInsertId();

            return null;
        } catch (PDOException $e) {
            error_log("Error en NotificacionModel::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todas las notificaciones no leídas de un usuario.
     * @param int $userId
     * @return array
     */
    public function findUnreadByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE usuario_destinatario_id = :user_id AND leida = 0 
                ORDER BY created_at DESC LIMIT 10"; // Limitamos a las 10 más recientes

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en NotificacionModel::findUnreadByUser: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marca una notificación como leída.
     * @param int $notificationId
     * @param int $userId - Para asegurar que un usuario solo marque sus propias notificaciones
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $sql = "UPDATE {$this->tableName} SET 
                    leida = 1, 
                    fecha_leida = :fecha 
                WHERE id = :id AND usuario_destinatario_id = :user_id AND leida = 0";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en NotificacionModel::markAsRead: " . $e->getMessage());
            return false;
        }
    }
}
