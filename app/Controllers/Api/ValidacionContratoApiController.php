<?php
namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\ValidacionContratoModel;
use App\Models\SolicitudContratoModel;
use App\Models\ProcesoVentaModel;

class ValidacionContratoApiController extends ApiController
{
    private $validacionModel, $solicitudModel, $procesoVentaModel;

    public function __construct()
    {
        parent::__construct();
        $this->validacionModel = new ValidacionContratoModel();
        $this->solicitudModel = new SolicitudContratoModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
    }

    public function index()
    {
        $this->checkAuthAndPermissionApi('contratos.validar');
        $userRoles = [$this->permissionManager->getRolId()];

        $validaciones = $this->validacionModel->getAllPendingForUser($this->permissionManager->getUserId(), $userRoles);

        $this->jsonResponse(['status' => 'success', 'data' => $validaciones]);
    }

    /**
     * Procesa la aprobación de una validación.
     */
    public function approve(int $validacionId)
    {
        $this->checkAuthAndPermissionApi('contratos.validar');
     
        $adminId = $this->permissionManager->getUserId();
        $input = json_decode(file_get_contents('php://input'), true);
        $comentarios = $input['comentarios'] ?? null;

        try {
            // Marcar esta validación específica como 'aprobado'
            if (!$this->validacionModel->approve($validacionId, $adminId, $comentarios)) {
                throw new \Exception('Esta validación ya fue procesada o no existe.');
            }

            // Obtener el ID de la solicitud de contrato principal
            $validacion = $this->validacionModel->findById($validacionId);
            $solicitudId = $validacion['solicitud_contrato_id'];

            // Verificar si TODAS las validaciones para esta solicitud ya están aprobadas
            if ($this->solicitudModel->areAllValidationsApproved($solicitudId)) {

                $solicitud = $this->solicitudModel->findById($solicitudId);

                // Marcar la solicitud de contrato como 'completado'
                $this->solicitudModel->complete($solicitudId, $solicitud['ruta_contrato_final']);

                // Avanzar el proceso de venta a 'Contrato Validado y Cargado'
                $siguienteEstatusId = 8;
                $this->procesoVentaModel->updateStatus($solicitud['proceso_venta_id'], $siguienteEstatusId);
            }

            $this->jsonResponse(['status' => 'success', 'message' => 'Validación registrada con éxito.']);
        } catch (\Exception $e) {
            error_log("Error al aprobar validación de contrato: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
