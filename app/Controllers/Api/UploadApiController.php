<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

class UploadApiController extends ApiController
{
    public function handleTempUpload()
    {
        if (!$this->permissionManager->getUserId()) {
            $this->jsonResponse(['status' => 'error', 'message' => 'No autorizado'], 401);
        }

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error en la subida del archivo.'], 400);
        }

        $file = $_FILES['file'];

        $tempDir = BASE_PATH . "/public/uploads/temp/";
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $fileName = uniqid('temp_') . '-' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($file['name']));
        $destinationPath = $tempDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
            $publicPath = "/uploads/temp/" . $fileName;

            $this->jsonResponse(['status' => 'success', 'data' => ['path' => $publicPath]], 201);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo guardar el archivo temporal en:', $destinationPath], 500);
        }
    }
}
