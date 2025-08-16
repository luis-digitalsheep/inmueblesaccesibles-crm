<?php
namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\ProspectoModel;
use App\Models\ProcesoVentaModel;
use App\Models\PagoModel;
use App\Models\SolicitudContratoModel;
use App\Models\ValidacionContratoModel;

class DashboardApiController extends ApiController
{
    private $prospectoModel;
    private $procesoVentaModel;
    private $pagoModel;
    private $solicitudContratoModel;
    private $validacionContratoModel;

    public function __construct()
    {
        parent::__construct();
     
        $this->prospectoModel = new ProspectoModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->pagoModel = new PagoModel();
        $this->solicitudContratoModel = new SolicitudContratoModel();
        $this->validacionContratoModel = new ValidacionContratoModel();
    }

    /**
     * API: Obtiene los datos para el dashboard según el rol del usuario.
     */
    public function getData()
    {
        // $this->checkAuthAndPermissionApi('dashboard.ver');
        
        $userId = $this->permissionManager->getUserId();
        $userRol = $this->permissionManager->getRolId();

        $dashboardData = [];

        error_log("UserID: $userId, UserRol: $userRol");

        // Lógica para construir el dashboard según el rol
        
        if ($userRol == 1 ) { // 1 = Dirección
            $dashboardData = $this->getDirectorDashboard();
        } else if ($userRol == 5) { // 5 = Asesor Comercial
            $dashboardData = $this->getVendedorDashboard($userId);
        } else {
            // Dashboard por defecto para otros roles administrativos
            $dashboardData = $this->getAdminDashboard($userRol);
        }

        $this->jsonResponse(['status' => 'success', 'data' => $dashboardData]);
    }

    /**
     * Construye el set de datos para el dashboard de Dirección.
     */
    private function getDirectorDashboard(): array
    {
        return [
            'type' => 'director',
            'kpis' => [
                ['label' => 'Prospectos Activos', 'value' => $this->prospectoModel->countAllActive()],
                ['label' => 'Procesos de Venta Activos', 'value' => $this->procesoVentaModel->countAllActive()],
            ],
            'work_queues' => [
                ['label' => 'Pagos Pendientes de Validación', 'value' => $this->pagoModel->countAllPending(), 'url' => '/validaciones-pagos'],
                ['label' => 'Solicitudes de Contrato Pendientes', 'value' => $this->solicitudContratoModel->countAllPending(), 'url' => '/solicitudes-contrato'],
            ]
        ];
    }

    /**
     * Construye el set de datos para el dashboard de un Vendedor.
     */
    private function getVendedorDashboard(int $userId): array
    {
        return [
            'type' => 'vendedor',
            'kpis' => [
                ['label' => 'Mis Prospectos Activos', 'value' => $this->prospectoModel->countByUser($userId)],
                ['label' => 'Mis Procesos de Venta Activos', 'value' => $this->procesoVentaModel->countActiveByUser($userId)],
            ],
            'recent_activity' => [] // TODO: añadir una lista de sus últimos seguimientos
        ];
    }

    /**
     * Construye el set de datos para dashboards administrativos.
     */
    private function getAdminDashboard(int $userRol): array
    {
        // Lógica para obtener las "colas de trabajo" relevantes para los roles del usuario
        return [
            'type' => 'admin',
            'work_queues' => [
                ['label' => 'Validaciones de Contrato Pendientes', 'value' => $this->validacionContratoModel->countPendingForUser([$userRol]), 'url' => '/validaciones-contrato'],
            ]
        ];
    }
}
