<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\PagoModel;
use App\Models\ProspectoModel;
use App\Models\ClienteModel;
use App\Models\ProcesoVentaModel;
use App\Services\Notification\NotificationManager;

class ValidacionPagoApiController extends ApiController
{
    private $pagoModel, $prospectoModel, $clienteModel, $procesoVentaModel, $notificacionManager;

    public function __construct()
    {
        parent::__construct();
        $this->pagoModel = new PagoModel();
        $this->prospectoModel = new ProspectoModel();
        $this->clienteModel = new ClienteModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->notificacionManager = new NotificationManager();
    }

    public function index()
    {
        $this->checkAuthAndPermissionApi('pagos.validar');

        $limit = (int)($_GET['limit'] ?? 15);
        $offset = (int)($_GET['offset'] ?? 0);

        $pagos = $this->pagoModel->getAllPending([], $limit, $offset);
        $total = $this->pagoModel->getTotalPending();

        $this->jsonResponse(['status' => 'success', 'data' => $pagos, 'total' => $total]);
    }

    /**
     * Aprueba un pago y convierte el prospecto a cliente.
     */
    public function approve(int $pagoId)
    {
        $this->checkAuthAndPermissionApi('pagos.validar');

        $adminId = $this->permissionManager->getUserId();

        try {
            // Marcar el pago como aprobado
            $this->pagoModel->updateValidationStatus($pagoId, 'aprobado', $adminId);

            // Obtener el prospecto asociado a este pago
            $pago = $this->pagoModel->findById($pagoId);
            $proceso = $this->procesoVentaModel->findById($pago['proceso_venta_id']);
            $prospecto = $this->prospectoModel->findById($proceso['prospecto_id']);

            if (!$prospecto) throw new \Exception('Prospecto asociado no encontrado.');
            if ($prospecto['cliente_id']) throw new \Exception('Este prospecto ya ha sido convertido.');

            // Crear el cliente
            $prospecto['user_id'] = $adminId;
            $clienteId = $this->clienteModel->createFromProspecto($prospecto);

            if (!$clienteId) throw new \Exception('No se pudo crear el registro del cliente.');

            // Enlazar prospecto a cliente, reasignar procesos y actualizar estados
            $this->prospectoModel->linkToCliente($prospecto['id'], $clienteId);
            $this->procesoVentaModel->reassignProcesosToCliente($prospecto['id'], $clienteId);
            $this->prospectoModel->updateGlobalStatus($prospecto['id'], 4); // 4 = Convertido a Cliente
            $this->procesoVentaModel->updateStatus($pago['proceso_venta_id'], 5); // 5 = Pago Validado / Convertido a Cliente

            $this->jsonResponse(['status' => 'success', 'message' => 'Pago aprobado y prospecto convertido a cliente con éxito.']);
        } catch (\Exception $e) {
            error_log("Error al aprobar pago: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Rechaza una solicitud de validación de pago.
     */
    public function reject(int $pagoId)
    {
        $this->checkAuthAndPermissionApi('pagos.validar');

        $adminId = $this->permissionManager->getUserId();
        $input = json_decode(file_get_contents('php://input'), true);
        $motivo = trim($input['motivo'] ?? '');

        if (empty($motivo)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'El motivo del rechazo es requerido.'], 422);
            return;
        }

        if ($this->pagoModel->reject($pagoId, $adminId, $motivo)) {
            $proceso = $this->procesoVentaModel->findByPagoId($pagoId);

            // Notificar rechazo
            if ($proceso) {
                $vendedorId = $proceso['usuario_responsable_id'];
                $mensaje = "El pago del apartado para el proceso #{$proceso['id']} fue rechazado. Motivo: \"{$motivo}\"";
                $urlDestino = "/procesos-venta/ver/{$proceso['id']}";

                $this->notificacionManager->notificarAvanceProceso($vendedorId, $mensaje, $urlDestino);
            }

            $this->jsonResponse(['status' => 'success', 'message' => 'El pago ha sido rechazado y el vendedor ha sido notificado.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo procesar el rechazo.'], 500);
        }
    }
}
