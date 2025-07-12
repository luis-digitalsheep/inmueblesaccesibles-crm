<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;
use App\Models\DocumentoModel;

class DocumentoController extends WebController
{
    private $documentoModel;

    public function __construct()
    {
        parent::__construct();
        $this->documentoModel = new DocumentoModel();
    }

    /**
     * Entrega un archivo privado al navegador después de verificar permisos.
     * @param int $id El ID del documento en la base de datos.
     */
    public function descargar(int $id)
    {
        $this->checkPermission('prospectos.documentos.ver');

        $documento = $this->documentoModel->findById($id);
        
        if (!$documento) {
            $this->renderErrorPage(404, 'Documento no encontrado.');
            return;
        }

        $filePath = BASE_PATH . '/storage/app/' . $documento['ruta_archivo'];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->renderErrorPage(404, 'El archivo físico no existe o no se puede leer.');
            return;
        }

        header('Content-Type: ' . mime_content_type($filePath));
        header('Content-Disposition: inline; filename="' . basename($documento['nombre_archivo']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        ob_clean();
        flush();

        readfile($filePath);
        exit;
    }
}
