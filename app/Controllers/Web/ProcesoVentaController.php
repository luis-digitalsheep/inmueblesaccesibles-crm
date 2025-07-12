<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;

use App\Models\ProcesoVentaModel;

class ProcesoVentaController extends WebController
{
    private $procesoVentaModel;

    public function __construct()
    {
        parent::__construct();
        $this->procesoVentaModel = new ProcesoVentaModel();
    }

    /**
     * Muestra la vista de detalle/gestión de un proceso de venta.
     * Carga el "cascarón" que será llenado por JavaScript.
     * Ruta: GET /procesos-venta/ver/{id}
     *
     * @param int $id El ID del proceso de venta.
     * @param string $currentRoute (Opcional) El nombre de la ruta actual.
     */
    public function show(int $id, string $currentRoute = '')
    {
        // $this->checkPermission('procesos_venta.ver');

        $proceso = $this->procesoVentaModel->findById($id);

        if (!$proceso) {
            $this->renderErrorPage(404, 'Proceso de venta no encontrado.');
            return;
        }

        // Lógica de permisos para ver solo procesos de su sucursal/propios
        // if (!$this->permissionManager->hasPermission('procesos_venta.ver.todos') && ...) {
        //     $this->renderErrorPage(403, 'No tienes permiso para ver este proceso.');
        //     return;
        // }

        $data = [
            'pageTitle' => 'Gestionar Proceso de Venta',
            'pageDescription' => 'Propiedad: ' . htmlspecialchars($proceso['propiedad_direccion']),
            'procesoVentaId' => $id,
            'currentRoute' => $currentRoute,
            'permissions' => [
                'canUpdate' => $this->permissionManager->hasPermission('procesos_venta.update'),
                'canAddSeguimiento' => $this->permissionManager->hasPermission('procesos_venta.seguimiento.crear'),
                'canManageWorkflow' => $this->permissionManager->hasPermission('procesos_venta.workflow.gestionar'),
            ]
        ];

        $this->render('procesosVenta/show', $data, $currentRoute);
    }

    /*
    public function index(string $currentRoute = '')
    {
        // ... Lógica para mostrar la lista de todos los procesos ...
    }
    */
}
