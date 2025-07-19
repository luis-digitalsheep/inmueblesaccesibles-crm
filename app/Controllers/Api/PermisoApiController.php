<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\PermisoModel;

class PermisoApiController extends ApiController
{
    private $permisoModel;

    public function __construct()
    {
        parent::__construct();
        $this->permisoModel = new PermisoModel();
    }

    public function index()
    {
        $this->checkAuthAndPermissionApi('permisos.ver');

        $items = $this->permisoModel->getAll([], (int)($_GET['limit'] ?? 15), (int)($_GET['offset'] ?? 0));
        $total = $this->permisoModel->getTotal();

        $this->jsonResponse(['status' => 'success', 'data' => $items, 'total' => $total]);
    }

    /**
     * API: Obtiene los datos de un permiso específico.
     * @param int $id
     */
    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('permisos.editar');

        $permiso = $this->permisoModel->findById($id);

        if (!$permiso) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Permiso no encontrado.'], 404);
            return;
        }

        $this->jsonResponse(['status' => 'success', 'data' => $permiso]);
    }

    /**
     * API: Crea un nuevo permiso.
     * @param array $input Datos del permiso.
     * @return JSON
     */
    public function store()
    {
        $this->checkAuthAndPermissionApi('permisos.crear');
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse(['status' => 'error', 'message' => 'JSON inválido.'], 400);
            return;
        }

        if (empty($input['modulo']) || empty($input['accion'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Los campos Módulo y Acción son requeridos.'], 422);
            return;
        }

        $input['nombre'] = trim($input['modulo']) . '.' . trim($input['accion']);

        $permisoId = $this->permisoModel->create($input);

        if ($permisoId) {
            $nuevoPermiso = $this->permisoModel->findById($permisoId);
            $this->jsonResponse(['status' => 'success', 'message' => 'Permiso creado con éxito.', 'data' => $nuevoPermiso], 201);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear el permiso. Es posible que el nombre clave ya exista.'], 500);
        }
    }

    /**
     * API: Actualiza un permiso existente.
     */
    public function update(int $id)
    {
        $this->checkAuthAndPermissionApi('permisos.editar');
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['modulo']) || empty($input['accion'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Los campos Módulo y Acción son requeridos.'], 422);
            return;
        }

        $input['nombre'] = trim($input['modulo']) . '.' . trim($input['accion']);

        if ($this->permisoModel->update($id, $input)) {
            $permisoActualizado = $this->permisoModel->findById($id);
            $this->jsonResponse(['status' => 'success', 'message' => 'Permiso actualizado con éxito.', 'data' => $permisoActualizado]);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el permiso.'], 500);
        }
    }

    /**
     * API: Elimina un permiso.
     */
    public function destroy(int $id)
    {
        $this->checkAuthAndPermissionApi('permisos.eliminar');

        if ($this->permisoModel->delete($id)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Permiso eliminado con éxito.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo eliminar el permiso. Es posible que esté en uso por algún rol.'], 500);
        }
    }

    /**
     * API: Devuelve todos los permisos, agrupados por módulo.
     */
    public function getAllGrouped()
    {
        $this->checkAuthAndPermissionApi('roles.editar');
     
        $permisos = $this->permisoModel->getAllGroupedByModule();
     
        $this->jsonResponse(['status' => 'success', 'data' => $permisos]);
    }
}
