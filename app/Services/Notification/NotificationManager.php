<?php

namespace App\Services\Notification;

use App\Models\NotificacionModel;
use App\Models\UsuarioModel;

class NotificationManager
{
    private $notificacionModel;
    private $usuarioModel;

    public function __construct()
    {
        $this->notificacionModel = new NotificacionModel();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Notifica a todos los usuarios con un rol específico sobre una nueva tarea.
     */
    public function notificarNuevaTareaPorRol(array $rolIds, string $mensaje, string $urlDestino)
    {
        // Buscar todos los usuarios que tengan uno de los roles en $rolIds
        $usuariosANotificar = $this->usuarioModel->findByRoles($rolIds);

        // Crear una notificación para cada uno de esos usuarios
        foreach ($usuariosANotificar as $usuario) {
            $this->notificacionModel->create([
                'usuario_destinatario_id' => $usuario['id'],
                'mensaje' => $mensaje,
                'url_destino' => $urlDestino,
                'tipo_notificacion' => 'nueva_tarea'
            ]);
        }
    }

    /**
     * Notifica a un usuario específico (ej. un vendedor) sobre un avance.
     */
    public function notificarAvanceProceso(int $vendedorId, string $mensaje, string $urlDestino)
    {
        $this->notificacionModel->create([
            'usuario_destinatario_id' => $vendedorId,
            'mensaje' => $mensaje,
            'url_destino' => $urlDestino,
            'tipo_notificacion' => 'avance_proceso'
        ]);
    }
}
