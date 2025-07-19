<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;
use App\Models\RolModel;

class RolController extends WebController
{
    private $rolModel;

    public function __construct()
    {
        parent::__construct();
        $this->rolModel = new RolModel();
    }

    public function index(string $currentRoute = '')
    {
        $this->checkPermission('roles.ver');

        $data = [
            'pageTitle' => 'Gestión de Roles',
            'permissions' => [
                'canCreate' => $this->permissionManager->hasPermission('roles.crear'),
                'canUpdate' => $this->permissionManager->hasPermission('roles.editar'),
                'canDelete' => $this->permissionManager->hasPermission('roles.eliminar'),
            ]
        ];

        $this->render('roles/list', $data, $currentRoute);
    }

    /**
     * Muestra la vista de edición de un rol.
     */
    public function edit(int $id, string $currentRoute = '')
    {
        $this->checkPermission('roles.editar');

        $rol = $this->rolModel->findById($id);
        
        if (!$rol) {
            $this->renderErrorPage(404, 'Rol no encontrado.');
            return;
        }

        $data = [
            'pageTitle' => 'Editar Rol: ' . htmlspecialchars($rol['nombre']),
            'rolId' => $id,
            'currentRoute' => $currentRoute
        ];

        $this->render('roles/edit', $data, $currentRoute);
    }
}
