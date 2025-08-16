<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class ValidacionPagoController extends WebController
{
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('pagos.validar');

        $data = [
            'pageTitle' => 'Validación de Pagos de Apartado',
            'pageDescription' => 'Aprueba o rechaza los pagos de apartado registrados.',
            'permissions' => ['canValidate' => $this->permissionManager->hasPermission('pagos.validar')],
            'currentRoute' => $currentRoute
        ];

        $this->render('validacionesPagos/list', $data, $currentRoute);
    }
}
