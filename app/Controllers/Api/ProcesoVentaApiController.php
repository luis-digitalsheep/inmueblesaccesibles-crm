<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\ProcesoVentaModel;
use App\Models\ProspectoModel;

class ProcesoVentaApiController extends ApiController
{
    private $procesoVentaModel;
    private $prospectoModel;

    public function __construct()
    {
        parent::__construct();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->prospectoModel = new ProspectoModel();
    }

    /**
     * API: Devuelve los procesos de venta de un prospecto específico.
     * @param int $prospectoId
     */
    public function apiGetByProspecto(int $prospectoId)
    {
        // Si el usuario puede ver el prospecto, puede ver su lista de procesos.
        $this->checkAuthAndPermissionApi('prospectos.ver');

        try {
            $procesos = $this->procesoVentaModel->findAllByProspectoId($prospectoId);
            $this->jsonResponse(['status' => 'success', 'data' => $procesos]);
        } catch (\Exception $e) {
            error_log("Error en ProcesoVentaApiController::apiGetByProspecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener los procesos de venta.'], 500);
        }
    }

    /**
     * API: Crea un nuevo proceso de venta para un prospecto y una propiedad.
     * @param int $prospectoId
     */
    public function apiCreateForProspecto(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.crear');

        $input = json_decode(file_get_contents('php://input'), true);
        $propiedadId = filter_var($input['propiedad_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$propiedadId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Debe seleccionar una propiedad.'], 400);
            return;
        }

        // TODO: Validar que no exista ya un proceso activo para este prospecto y esta propiedad.

        $data = [
            'prospecto_id' => $prospectoId,
            'propiedad_id' => $propiedadId,
            'estatus_proceso_id' => 2,
            'usuario_responsable_id' => $this->prospectoModel->findById($prospectoId)['usuario_responsable_id'] ?? $this->permissionManager->getUserId()
        ];

        try {
            $procesoId = $this->procesoVentaModel->create($data);

            if ($procesoId) {
                $nuevoProceso = $this->procesoVentaModel->findById($procesoId);
                
                $this->jsonResponse(['status' => 'success', 'message' => 'Proceso de venta creado con éxito.', 'data' => $nuevoProceso], 201);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear el proceso de venta.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en ProcesoVentaApiController::apiCreateForProspecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al crear el proceso.'], 500);
        }
    }
}
