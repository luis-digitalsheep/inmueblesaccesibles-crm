<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\DocumentoModel;
use App\Models\ProcesoVentaModel;
use App\Models\SolicitudContratoModel;
use App\Models\ValidacionContratoModel;

class SolicitudContratoApiController extends ApiController
{
    private $solicitudContratoModel;
    private $documentoModel;
    private $procesoVentaModel;
    private $validacionContratoModel;

    public function __construct()
    {
        parent::__construct();
        $this->solicitudContratoModel = new SolicitudContratoModel();
        $this->documentoModel = new DocumentoModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->validacionContratoModel = new ValidacionContratoModel();
    }

    public function index()
    {
        $this->checkAuthAndPermissionApi('contratos.gestionar');

        $limit = (int)($_GET['limit'] ?? 15);
        $offset = (int)($_GET['offset'] ?? 0);

        $solicitudes = $this->solicitudContratoModel->getAllPending([], $limit, $offset);
        $total = $this->solicitudContratoModel->getTotalPending();

        $this->jsonResponse(['status' => 'success', 'data' => $solicitudes, 'total' => $total]);
    }

    public function asignar(int $solicitudId)
    {
        $this->checkAuthAndPermissionApi('contratos.gestionar');
        $adminUserId = $this->permissionManager->getUserId();

        if ($this->solicitudContratoModel->assign($solicitudId, $adminUserId)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Tarea asignada con éxito.']);
        }

        $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo asignar la tarea.'], 500);
    }

    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('contratos.gestionar');
        $solicitud = $this->solicitudContratoModel->findById($id);

        if (!$solicitud) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Solicitud no encontrada.'], 404);
            return;
        }

        $this->jsonResponse(['status' => 'success', 'data' => $solicitud]);
    }

    /**
     * Sube el contrato generado, completa la solicitud y avanza el proceso de venta.
     */
    public function uploadContract(int $solicitudId)
    {
        $this->checkAuthAndPermissionApi('contratos.gestionar');

        // Validar la subida del archivo
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error en la subida del archivo.'], 400);
            return;
        }

        try {
            $solicitud = $this->solicitudContratoModel->findById($solicitudId);

            if (!$solicitud) throw new \Exception('Solicitud no encontrada.');

            // Guardar el archivo físicamente en 'storage'
            $file = $_FILES['file'];
            $storagePath = "contratos_finales/{$solicitud['proceso_venta_id']}/" . uniqid('contrato_') . '-' . basename($file['name']);
            $fullPath = BASE_PATH . '/storage/app/' . $storagePath;
            if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new \Exception('No se pudo guardar el archivo físico.');
            }

            // Crear el registro del documento en la tabla `documentos_clientes`
            $docData = [
                'proceso_venta_id' => $solicitud['proceso_venta_id'],
                'tipo_documento_id' => 8, // "Contrato de apartado"
                'nombre_archivo' => basename($file['name']),
                'ruta_archivo' => $storagePath,
                'subido_por_usuario_id' => $this->permissionManager->getUserId()
            ];

            $this->documentoModel->createForProceso($docData);

            $this->solicitudContratoModel->moveToValidation($solicitudId, $storagePath);

            $rolesQueValidan = [1, 2, 3]; // 2=Administrador, 3=Director de Ventas

            $this->validacionContratoModel->createValidationRequests($solicitudId, $rolesQueValidan);

            $this->jsonResponse(['status' => 'success', 'message' => 'Contrato subido y enviado a validación.']);

            // // Marcar la solicitud de contrato como "completada"
            // $this->solicitudContratoModel->complete($solicitudId, $storagePath);

            // // Actualizar el estado del proceso de venta
            // $siguienteEstatusId = 8; // 8 = 'Contrato Validado y Cargado'
            // $this->procesoVentaModel->updateStatus($solicitud['proceso_venta_id'], $siguienteEstatusId);

            // $this->jsonResponse(['status' => 'success', 'message' => 'Contrato subido y solicitud completada con éxito.']);
        } catch (\Exception $e) {
            error_log("Error al subir contrato: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al procesar el contrato.'], 500);
        }
    }
}
