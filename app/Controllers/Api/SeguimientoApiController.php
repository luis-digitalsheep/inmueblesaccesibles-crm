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

    public function addSeguimiento(int $procesoVentaId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.seguimiento.crear');

        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $this->permissionManager->getUserId();
        $data = [
            'proceso_venta_id' => $procesoVentaId,
            'tipo_interaccion' => $input['tipo_interaccion'] ?? 'nota',
            'comentarios' => $input['comentarios'] ?? '',
            'usuario_registra_id' => $userId
        ];

        try {
            $seguimientoId = $this->seguimientoModel->create($data);
            if (!$seguimientoId) throw new \Exception('No se pudo crear el seguimiento.');

            $this->jsonResponse(['status' => 'success', 'message' => 'Seguimiento creado con éxito.'], 201);
        } catch (\Exception $e) {
            error_log("Error en SeguimientoApiController::addSeguimiento: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al crear el seguimiento.'], 500);
        }
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
