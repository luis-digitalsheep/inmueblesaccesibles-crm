<?php
namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\RolModel;
use App\Models\PermisoModel;

class RolApiController extends ApiController
{
    private $rolModel;
    private $permisoModel;

    public function __construct()
    {
        parent::__construct();
        $this->rolModel = new RolModel();
        $this->permisoModel = new PermisoModel();
    }

    /**
     * API: Devuelve una lista de roles.
     */
    public function index()
    {
        $this->checkAuthAndPermissionApi('roles.ver');
     
        $roles = $this->rolModel->getAll();
     
        $this->jsonResponse(['status' => 'success', 'data' => $roles]);
    }

    /**
     * API: Devuelve los datos de un rol específico y los permisos que tiene asignados.
     */
    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('roles.editar');

        $rol = $this->rolModel->findById($id);
        if (!$rol) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Rol no encontrado.'], 404);
            return;
        }

        $rol['permisos'] = $this->rolModel->getPermissionIdsByRoleId($id);

        $this->jsonResponse(['status' => 'success', 'data' => $rol]);
    }

    /**
     * API: Crea un nuevo rol.
     */
    public function store()
    {
        $this->checkAuthAndPermissionApi('roles.crear');
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nombre'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'El nombre del rol es requerido.'], 422);
            return;
        }

        $rolId = $this->rolModel->create($input);

        if ($rolId) {
            $nuevoRol = $this->rolModel->findById($rolId);
            $this->jsonResponse(['status' => 'success', 'message' => 'Rol creado con éxito.', 'data' => $nuevoRol], 201);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear el rol.'], 500);
        }
    }

    /**
     * API: Actualiza un rol existente y sincroniza sus permisos.
     */
    public function update(int $id)
    {
        $this->checkAuthAndPermissionApi('roles.editar');
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['nombre'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'El nombre del rol es requerido.'], 422);
            return;
        }

        try {
            $this->rolModel->update($id, ['nombre' => $input['nombre']]);

            $permissionIds = $input['permisos'] ?? [];
            $this->rolModel->syncPermissions($id, $permissionIds);

            $rolActualizado = $this->rolModel->findById($id);
            $this->jsonResponse(['status' => 'success', 'message' => 'Rol y permisos actualizados con éxito.', 'data' => $rolActualizado]);
        } catch (\Exception $e) {
            error_log("Error en RolApiController::update: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al actualizar el rol.'], 500);
        }
    }

    /**
     * API: Elimina un rol.
     */
    public function destroy(int $id)
    {
        $this->checkAuthAndPermissionApi('roles.eliminar');

        if ($this->rolModel->delete($id)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Rol eliminado con éxito.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo eliminar el rol. Es posible que esté en uso.'], 500);
        }
    }
}
