<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\CarteraModel;
use App\Models\PropiedadRevisionModel;

use App\Services\Spreadsheet\SpreadsheetService;

class CarteraApiController extends ApiController
{
    private $carteraModel;
    private $propiedadRevisionModel;
    private $spreadsheetService;

    private function __construct()
    {
        parent::__construct();
        $this->carteraModel = new CarteraModel();
        $this->propiedadRevisionModel = new PropiedadRevisionModel();
        $this->spreadsheetService = new SpreadsheetService();
    }

    public function apiGetAll()
    {
        try {

            $filters = [];
            $filters['codigo'] = $_GET['codigo'] ?? null;
            $filters['nombre'] = $_GET['nombre'] ?? null;
            $filters['sucursal_id'] = $_GET['sucursal'] ?? null;
            $filters['administradora_id'] = $_GET['administradora'] ?? null;

            $limit = (int) ($_GET['limit'] ?? 10);
            $offset = (int) ($_GET['offset'] ?? 0);

            $carteras = $this->carteraModel->getAll($filters, $limit, $offset);

            $this->jsonResponse([
                'status' => 'success',
                'data' => $carteras
            ], 200);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function apiUploadCartera()
    {
        $this->checkAuthAndPermissionApi(
            'propiedades.cargar.cartera',
            'No tienes permiso para cargar carteras.'
        );

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error en la subida del archivo o archivo no enviado.'], 400);
        }

        $codigoCartera = $_POST['carga_codigo_cartera'] ?? null;
        $nombreCartera = $_POST['carga_nombre_cartera'] ?? null;
        $sucursalId = filter_var($_POST['carga_sucursal_id'] ?? null, FILTER_VALIDATE_INT);
        $administradoraId = filter_var($_POST['carga_administradora_id'] ?? null, FILTER_VALIDATE_INT);

        if (empty($codigoCartera) || empty($nombreCartera) || !$sucursalId || !$administradoraId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Código de cartera, nombre, sucursal y administradora son requeridos.'], 400);
        }

        $uploadedFile = $_FILES['file'];
        $fileName = $uploadedFile['name'];
        $fileTmpPath = $uploadedFile['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, ['xlsx', 'csv'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Formato de archivo no permitido. Solo .xlsx o .csv.'], 400);
        }

        $carteraId = $this->carteraModel->findOrCreateByCodigo($codigoCartera, $nombreCartera);

        if (!$carteraId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear o encontrar la cartera.'], 500);
        }

        try {
            $sheetData = $this->spreadsheetService->readSpreadsheet($fileTmpPath, 0, 2, true);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            error_log("Error al leer spreadsheet: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al procesar el archivo Excel/CSV: ' . $e->getMessage()], 500);
        }

        if (empty($sheetData)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'El archivo parece estar vacío o no tiene datos después de la cabecera.'], 400);
        }

        $filasInsertadas = 0;
        $erroresEnFilas = [];

        $columnMapping = [
            'B' => 'numero_credito',
            'C' => 'estado',
            'D' => 'municipio',
            'E' => 'fraccionamiento',
            'F' => 'direccion',
            'G' => 'direccion_extra',
            'H' => 'codigo_postal',
            'I' => 'etapa_judicial',
            'J' => 'etapa_judicial_segunda',
            'K' => 'fecha_etapa_judicial',
            'L' => 'tipo_vivienda',
            'M' => 'metros',
            'N' => 'tipo_inmueble',
            'O' => 'avaluo_administradora',
            'P' => 'precio_lista',
            'Q' => 'cofinavit'
        ];

        foreach ($sheetData as $rowIndex => $row) {
            $propiedadData = [
                'cartera_id' => $carteraId,
                'sucursal_id' => $sucursalId,
                'administradora_id' => $administradoraId,
                'estatus' => 'Pendiente'
            ];

            foreach ($columnMapping as $excelCol => $dbField) {
                $propiedadData[$dbField] = $row[$excelCol] ?? null;
            }

            if (empty($propiedadData['numero_credito']) || empty($propiedadData['direccion'])) {
                $erroresEnFilas[] = "Fila " . ($rowIndex + 2) . ": Número de crédito y dirección son requeridos.";
                continue;
            }

            // Limpiar y formatear valores, quitar símbolos de moneda, comas y convertir a float.
            foreach (['avaluo_administradora', 'precio_lista'] as $priceField) {
                if (isset($propiedadData[$priceField])) {
                    $cleanedValue = preg_replace('/[^\d.]/', '', $propiedadData[$priceField]);
                    $propiedadData[$priceField] = !empty($cleanedValue) ? (float)$cleanedValue : null;
                }
            }

            $revisionId = $this->propiedadRevisionModel->createRevision($propiedadData);

            if ($revisionId) {
                $filasInsertadas++;
            } else {
                $erroresEnFilas[] = "Fila " . ($rowIndex + 2) . ": Error al guardar en la base de datos.";
            }
        }

        if (!empty($erroresEnFilas)) {
            $this->jsonResponse([
                'status' => 'partial_success',
                'message' => "Carga completada con algunos errores. Filas insertadas: $filasInsertadas.",
                'errors_detail' => $erroresEnFilas
            ], 207);
        } else {
            $this->jsonResponse([
                'status' => 'success',
                'message' => "¡Cartera cargada con éxito! $filasInsertadas propiedades agregadas para revisión."
            ], 201);
        }
    }
}
