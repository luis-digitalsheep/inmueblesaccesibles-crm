<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class PermisoController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('permisos.ver');

        $data = [
            'pageTitle' => 'Gestión de Permisos',
            'permissions' => [
                'canCreate' => $this->permissionManager->hasPermission('permisos.crear'),
                'canUpdate' => $this->permissionManager->hasPermission('permisos.editar'),
                'canDelete' => $this->permissionManager->hasPermission('permisos.eliminar'),
            ]
        ];
        
        $this->render('permisos/list', $data, $currentRoute);
    }
}
