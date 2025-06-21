<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\DocumentoModel;

class DocumentoApiController extends ApiController
{
    private $documentoModel;

    public function __construct()
    {
        parent::__construct();
        $this->documentoModel = new DocumentoModel();
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

    /**
     * API: Sube un nuevo documento para un prospecto.
     * @param int $prospectoId
     */
    public function apiStoreForProspecto(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('prospectos.workflow.gestionar');

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
                'subido_por_usuario_id' => $this->permissionManager->getUserId()
            ];

            $docId = $this->documentoModel->createForProspecto($data);
            if ($docId) {
                $this->jsonResponse(['status' => 'success', 'message' => 'Documento subido con éxito.', 'data' => ['docId' => $docId]], 201);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'Error al guardar el registro del documento.'], 500);
            }
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo mover el archivo subido.'], 500);
        }
    }
}
