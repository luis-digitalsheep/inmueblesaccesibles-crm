<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

use App\Models\ProspectoModel;

class ProspectoController extends WebController
{ 
    private $prospectoModel;

    public function __construct()
    {
        parent::__construct();
        $this->prospectoModel = new ProspectoModel();
    }

    /**
     * Muestra la lista de prospectos.
     * 
     * @param string $currentRoute Ruta actual para mantener el contexto de navegación.
     * @return void
     */
    public function index(string $currentRoute = '')
    {
        // $this->checkPermission('prospectos.ver');

        $filters = [];
        $filters['nombre'] = $_GET['nombre'] ?? '';
        $filters['usuario_responsable_id'] = $_GET['usuario_responsable_id'] ?? '';
        $filters['sucursal_id'] = $_GET['sucursal_id'] ?? '';
        $filters['estatus_prospeccion_id'] = $_GET['estatus_prospeccion_id'] ?? '';

        $data = [
            'pageTitle' => 'Listado de Prospectos',
            'pageDescription' => 'Gestiona tus prospectos y su seguimiento.',
            'currentFilters' => $_GET,

            // 'canCreateProspecto' => $this->permissionManager->hasPermission('prospectos.crear'),
            'canCreateProspecto' => true,
        ];

        $this->render('prospectos/list', $data, $currentRoute);
    }

    /**
     * Muestra la vista de detalle/gestión de un prospecto.
     */
    public function show(int $id, string $currentRoute = '')
    {
        // $this->checkPermission('prospectos.ver');

        $prospecto = $this->prospectoModel->findById($id);

        if (!$prospecto) {
            $this->renderErrorPage(404, 'Prospecto no encontrado.');
            return;
        }

        $data = [
            'pageTitle' => 'Gestionar Prospecto',
            'pageDescription' => 'Perfil, seguimiento y estatus del prospecto.',
            'prospectoId' => $id,
            'prospectoNombre' => $prospecto['nombre'],
            'currentRoute' => $currentRoute,

            'permissions' => [
                'canUpdate'           => true, // $this->permissionManager->hasPermission('prospectos.update'),
                'canAddSeguimiento'   => true, // $this->permissionManager->hasPermission('prospectos.seguimiento.crear'),
                'canManageWorkflow'   => true, // $this->permissionManager->hasPermission('prospectos.workflow.gestionar'),
                'canCreateProceso'    => true // $this->permissionManager->hasPermission('procesos_venta.crear'),
            ]
        ];

        $this->render('prospectos/show', $data, $currentRoute);
    }
}
