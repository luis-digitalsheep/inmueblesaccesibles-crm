<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\CatalogoModel;
use App\Models\ProcesoVentaModel;
use App\Models\SeguimientoModel;
use App\Services\Notification\NotificationManager;

class SeguimientoApiController extends ApiController
{
    private $seguimientoModel;
    private $notificacionManager;
    private $catalogoModel;
    private $procesoVentaModel;

    public function __construct()
    {
        parent::__construct();
        $this->seguimientoModel = new SeguimientoModel();
        $this->notificacionManager = new NotificationManager();
        $this->catalogoModel = new CatalogoModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
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
            'resultado' => $input['resultado'],
            'usuario_registra_id' => $userId
        ];

        try {
            $seguimientoId = $this->seguimientoModel->create($data);

            if ($seguimientoId) {
                $catalogoResultado = $this->catalogoModel->findByName('cat_resultados_seguimiento', $data['resultado']);

                if ($catalogoResultado && !empty($catalogoResultado['activa_escalamiento_a_rol'])) {
                    $rolIdANotificar = $catalogoResultado['activa_escalamiento_a_rol'];

                    $proceso = $this->procesoVentaModel->findById($procesoVentaId);

                    $mensaje = "Se requiere intervención gerencial en el proceso #{$procesoVentaId} para el prospecto {$proceso['prospecto_nombre']}. Resultado: {$data['resultado']}.";
                    $url = "/procesos-venta/ver/{$procesoVentaId}";

                    $this->notificacionManager->notificarNuevaTareaPorRol([$rolIdANotificar], $mensaje, $url);
                }

                $this->jsonResponse(['status' => 'success', 'message' => 'Seguimiento guardado con éxito.'], 201);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo guardar el seguimiento.'], 500);
            }
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
