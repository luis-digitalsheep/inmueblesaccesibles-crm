<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\SeguimientoModel;

class SeguimientoApiController extends ApiController
{
    private $seguimientoModel;

    public function __construct()
    {
        parent::__construct();
        $this->seguimientoModel = new SeguimientoModel();
    }

    /**
     * API: Devuelve los seguimientos de un proceso de venta específico.
     * @param int $procesoVentaId
     */
    public function getByProcesoVenta(int $procesoVentaId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.ver_seguimiento');

        $seguimientos = $this->seguimientoModel->findByProcesoVentaId($procesoVentaId); // Nuevo método en el modelo

        $this->jsonResponse(['status' => 'success', 'data' => $seguimientos]);
    }

    /**
     * API: Devuelve todos los seguimientos de un cliente.
     * @param int $clienteId
     */
    public function indexByCliente(int $clienteId)
    {
        $this->checkAuthAndPermissionApi('clientes.ver_seguimiento');
        $seguimientos = $this->seguimientoModel->findAllByClienteId($clienteId);
        $this->jsonResponse(['status' => 'success', 'data' => $seguimientos]);
    }
}
