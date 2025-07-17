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

    public function index()
    {
        $filters = $_GET;

        $limit = (int)($_GET['limit'] ?? 15);
        $offset = (int)($_GET['offset'] ?? 0);

        if (!$this->permissionManager->hasPermission('usuarios.ver_todos')) {
            $filters['id'] = $this->permissionManager->getUserId();
        };

        try {
            $usuarios = $this->usuarioModel->getAll($filters, $limit, $offset);
            $total = $this->usuarioModel->getTotal($filters);

            $this->jsonResponse([
                'status' => 'success',
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
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
        $filters = [];

        if (!$this->permissionManager->hasPermission('usuarios.ver_todos')) {
            $filters['id'] = $this->permissionManager->getUserId();
        };

        try {
            $usuarios = $this->usuarioModel->getAllForSelect($filters);

            $this->jsonResponse(['status' => 'success', 'data' => $usuarios]);
        } catch (\Exception $e) {
            error_log("Error en UsuarioController::apiGetSimpleList: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener la lista de usuarios.'], 500);
        }
    }

    /**
     * API: Obtiene los datos de un usuario específico para edición.
     */
    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('usuarios.editar');
        $usuario = $this->usuarioModel->findById($id);

        if (!$usuario) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado.'], 404);
            return;
        }

        $this->jsonResponse(['status' => 'success', 'data' => $usuario]);
    }

    /**
     * API: Crea un nuevo usuario.
     */
    public function store()
    {
        $this->checkAuthAndPermissionApi('usuarios.crear');
        $input = json_decode(file_get_contents('php://input'), true);

        // TODO: Validación robusta de datos (ej. email único, contraseña segura)

        if (empty($input['nombre']) || empty($input['email']) || empty($input['password'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Nombre, email y contraseña son requeridos.'], 422);
            return;
        }

        $input['user_id'] = $this->permissionManager->getUserId();

        $usuarioId = $this->usuarioModel->create($input);

        if ($usuarioId) {
            $nuevoUsuario = $this->usuarioModel->findById($usuarioId);
            $this->jsonResponse(['status' => 'success', 'message' => 'Usuario creado con éxito.', 'data' => $nuevoUsuario], 201);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear el usuario.'], 500);
        }
    }

    /**
     * API: Actualiza un usuario existente.
     */
    public function update(int $id)
    {
        $this->checkAuthAndPermissionApi('usuarios.editar');
        $input = json_decode(file_get_contents('php://input'), true);

        $input['user_id'] = $this->permissionManager->getUserId();

        if ($this->usuarioModel->update($id, $input)) {
            $usuarioActualizado = $this->usuarioModel->findById($id);
            $this->jsonResponse(['status' => 'success', 'message' => 'Usuario actualizado con éxito.', 'data' => $usuarioActualizado]);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el usuario.'], 500);
        }
    }

    /**
     * API: Elimina (desactiva) un usuario.
     */
    public function destroy(int $id)
    {
        $this->checkAuthAndPermissionApi('usuarios.eliminar');

        if ($this->usuarioModel->delete($id)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Usuario desactivado con éxito.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo desactivar el usuario.'], 500);
        }
    }
}
