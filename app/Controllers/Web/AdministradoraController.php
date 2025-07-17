<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;

class AdministradoraController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('administradoras.ver');

        $data = [
            'pageTitle' => 'Gestión de Administradoras',
            'permissions' => [
                'canCreate' => $this->permissionManager->hasPermission('administradoras.crear'),
                'canUpdate' => $this->permissionManager->hasPermission('administradoras.editar'),
                'canDelete' => $this->permissionManager->hasPermission('administradoras.eliminar'),
            ]
        ];

        $this->render('administradoras/list', $data, $currentRoute);
    }
}