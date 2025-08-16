<?php
namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\NotificacionModel;

class NotificacionApiController extends ApiController
{
    private $notificacionModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificacionModel = new NotificacionModel();
    }

    /**
     * API: Devuelve las notificaciones no leídas para el usuario actual.
     */
    public function index()
    {
        // $this->checkAuthAndPermissionApi();
        $userId = $this->permissionManager->getUserId();

        $notificaciones = $this->notificacionModel->findUnreadByUser($userId);

        $this->jsonResponse(['status' => 'success', 'data' => $notificaciones]);
    }

    /**
     * API: Marca una notificación como leída.
     */
    public function markAsRead(int $id)
    {
        // $this->checkAuthAndPermissionApi();
        $userId = $this->permissionManager->getUserId();

        if ($this->notificacionModel->markAsRead($id, $userId)) {
            $this->jsonResponse(['status' => 'success']);
        } else {
            $this->jsonResponse(['status' => 'no_action']);
        }
    }
}
