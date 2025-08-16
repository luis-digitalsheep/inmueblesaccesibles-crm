<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;

class SolicitudContratoController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('contratos.gestionar');
        $data = [
            'pageTitle' => 'Solicitudes de Contrato',
            'pageDescription' => 'Gestiona las solicitudes pendientes para la generación de contratos.',
            'currentRoute' => $currentRoute,
            'currentUserId' => $this->permissionManager->getUserId(),
            'permissions' => [
                'canManage' => $this->permissionManager->hasPermission('contratos.gestionar')
            ]
        ];
        $this->render('solicitudesContrato/list', $data, $currentRoute);
    }
}