<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class SucursalController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('sucursales.ver');

        $data = [
            'pageTitle' => 'Gestión de Sucursales',
            'permissions' => [
                'canCreate' => $this->permissionManager->hasPermission('sucursales.crear'),
                'canUpdate' => $this->permissionManager->hasPermission('sucursales.editar'),
                'canDelete' => $this->permissionManager->hasPermission('sucursales.eliminar'),
            ]
        ];

        $this->render('sucursales/list', $data, $currentRoute);
    }
}
