<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;
use App\Models\FolioApartadoModel;

class FolioApartadoController extends WebController
{
    private $folioApartadoModel;

    public function __construct() {
        parent::__construct();
        $this->folioApartadoModel = new FolioApartadoModel();
    }

    /**
     * Entrega un recibo de apartado PDF al navegador después de verificar permisos.
     * @param int $id El ID del folio en la base de datos.
     */
    public function descargar(int $id) {
        $this->checkPermission('folios_apartado.ver');

        $folio = $this->folioApartadoModel->findById($id);

        if (!$folio || empty($folio['ruta_pdf'])) {
            $this->renderErrorPage(404, 'Recibo de apartado no encontrado o sin archivo asociado.');
            return;
        }

        $filePath = BASE_PATH . '/storage/app/' . $folio['ruta_pdf'];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->renderErrorPage(404, 'El archivo físico no existe o no se puede leer.');
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($folio['folio']) . '.pdf"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        ob_clean();
        flush();
        
        readfile($filePath);
        exit;
    }
}