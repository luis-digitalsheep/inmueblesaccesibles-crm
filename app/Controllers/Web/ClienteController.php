<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;
use App\Models\ClienteModel;

class ClienteController extends WebController {
    private $clienteModel;

    public function __construct() {
        parent::__construct();

        $this->clienteModel = new ClienteModel();
    }

    public function index(string $currentRoute = '') {
        $this->checkPermission('clientes.ver');
        
        $data = [
            'pageTitle' => 'Gestión de Clientes',
            'pageDescription' => 'Consulta la información y procesos de tus clientes.',
            'currentRoute' => $currentRoute,
        ];
        
        $this->render('clientes/list', $data, $currentRoute);
    }
        /**
     * Muestra la vista de detalle/gestión de un cliente.
     * Carga el "cascarón" que será llenado por JavaScript.
     */
    public function show(int $id, string $currentRoute = '') {
        $this->checkPermission('clientes.ver');

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            $this->renderErrorPage(404, 'Cliente no encontrado.');
            return;
        }

        $data = [
            'pageTitle' => 'Expediente del Cliente',
            'pageDescription' => 'Cliente: ' . htmlspecialchars($cliente['nombre_completo']),
            'clienteId' => $id,
            'currentRoute' => $currentRoute,
            'permissions' => [
                'canUpdate' => $this->permissionManager->hasPermission('clientes.update'),
                'canCreateProceso' => $this->permissionManager->hasPermission('procesos_venta.crear'),
            ]
        ];
        
        $this->render('clientes/show', $data, $currentRoute);
    }
}