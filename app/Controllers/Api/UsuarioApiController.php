<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\UsuarioModel;

class UsuarioApiController extends ApiController
{
    private $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new UsuarioModel();
    }

    public function apiGetAll()
    {
        $filters = [];

        if (!$this->permissionManager->hasPermission('usuarios.ver_todos')) {
            $filters['id'] = $this->permissionManager->getUserId();
        };

        try {
            $usuarios = $this->usuarioModel->getAll($filters);

            $this->jsonResponse([
                'status' => 'success',
                'data' => $usuarios,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::apiGetAll: " . $e->getMessage());

            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno del servidor al obtener usuarios.'], 500);
        }
    }

    /**
     * API: Devuelve una lista simple de usuarios (id, nombre) para usar en selectores.
     */
    public function apiGetSimpleList()
    {
        // $this->checkAuthAndPermissionApi('usuarios.view_list');

        try {
            // Llama al método del modelo que preparamos.
            $usuarios = $this->usuarioModel->getAllForSelect();

            // Envía la respuesta en nuestro formato JSON estándar.
            $this->jsonResponse(['status' => 'success', 'data' => $usuarios]);
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::apiGetSimpleList: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener la lista de usuarios.'], 500);
        }
    }
}
