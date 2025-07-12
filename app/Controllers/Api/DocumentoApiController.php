<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\DocumentoModel;
use App\Models\ProspectoModel;

class DocumentoApiController extends ApiController
{
    private $documentoModel;
    private $prospectoModel;

    public function __construct()
    {
        parent::__construct();
        $this->documentoModel = new DocumentoModel();
        $this->prospectoModel = new ProspectoModel();
    }

    /**
     * API: Devuelve los documentos de un prospecto específico.
     * @param int $prospectoId
     */
    public function apiGetByProspecto(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('prospectos.ver');

        try {
            $documentos = $this->documentoModel->getByProspectoId($prospectoId);

            $this->jsonResponse(['status' => 'success', 'data' => $documentos]);
        } catch (\Exception $e) {
            error_log("Error en DocumentoController::apiGetByProspecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener los documentos.'], 500);
        }
    }

    public function getByProcesoVenta(int $procesoVentaId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.ver_documentos');

        $documentos = $this->documentoModel->findByProcesoVentaId($procesoVentaId);

        $this->jsonResponse(['status' => 'success', 'data' => $documentos]);
    }



    /**
     * API: Sube un nuevo documento para un prospecto.
     * @param int $prospectoId
     */
    public function apiStoreForProspecto(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('prospectos.workflow.gestionar');

        $id_usuario = $this->permissionManager->getUserId();

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error en la subida del archivo.'], 400);
            return;
        }

        $tipoDocumentoId = filter_var($_POST['tipo_documento_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$tipoDocumentoId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Tipo de documento no especificado.'], 400);
            return;
        }

        $file = $_FILES['file'];
        $uploadDir = BASE_PATH . "/storage/app/documentos/prospectos/{$prospectoId}/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '-' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $data = [
                'prospecto_id' => $prospectoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'nombre_archivo' => $fileName,
                'ruta_archivo' => "documentos/prospectos/{$prospectoId}/" . $fileName,
            ];

            try {
                $docId = $this->documentoModel->createForProspecto($id_usuario, $data);

                if (!$docId) {
                    throw new \Exception("Error al guardar el registro del documento.");
                }

                $tipoDocumentoId = (int)$tipoDocumentoId;

                if ($tipoDocumentoId === 1) {
                    $siguienteEstatusGlobalId = 2;

                    $this->prospectoModel->updateGlobalStatus($prospectoId, $siguienteEstatusGlobalId);
                }

                $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Documento subido y estatus actualizado con éxito.',
                    'data' => [
                        'docId' => $docId,
                        'new_global_status_id' => $siguienteEstatusGlobalId ?? null
                    ]
                ], 201);
            } catch (\Exception $e) {
                error_log("Error en subida de documento: " . $e->getMessage());
                $this->jsonResponse(['status' => 'error', 'message' => 'Ocurrió un error al procesar el documento.'], 500);
            }
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo mover el archivo subido.'], 500);
        }
    }

    /**
     * API: Devuelve todos los documentos de un cliente.
     * @param int $clienteId
     */
    public function indexByCliente(int $clienteId)
    {
        $this->checkAuthAndPermissionApi('clientes.ver_documentos');
        $documentos = $this->documentoModel->findAllByClienteId($clienteId);
        $this->jsonResponse(['status' => 'success', 'data' => $documentos]);
    }
}
