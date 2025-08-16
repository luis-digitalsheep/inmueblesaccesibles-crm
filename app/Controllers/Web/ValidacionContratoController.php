<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;

class ValidacionContratoController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('contratos.validar');
        
        $data = [
            'pageTitle' => 'Validación de Contratos',
            'pageDescription' => 'Revisa y aprueba los borradores de contrato pendientes.',
            'currentRoute' => $currentRoute,
            'permissions' => [
                'canValidate' => $this->permissionManager->hasPermission('contratos.validar')
            ],
            'user' => [
                'userId' => $this->permissionManager->getUserId(),
                'userRoles' => [$this->permissionManager->getRolId()]
            ]
        ];

        $this->render('validacionesContratos/list', $data, $currentRoute);
    }
}
