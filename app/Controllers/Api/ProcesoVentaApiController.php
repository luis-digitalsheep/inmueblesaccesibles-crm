<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\DocumentoModel;
use App\Models\FolioApartadoModel;
use App\Models\PagoModel;
use App\Models\ProcesoVentaModel;
use App\Models\PropiedadModel;
use App\Models\ProspectoModel;
use App\Models\SolicitudContratoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProcesoVentaApiController extends ApiController
{
    private $procesoVentaModel;
    private $prospectoModel;
    private $folioApartadoModel;
    private $documentoModel;
    private $pagoModel;
    private $propiedadModel;
    private $solicitudContratoModel;

    public function __construct()
    {
        parent::__construct();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->prospectoModel = new ProspectoModel();
        $this->folioApartadoModel = new FolioApartadoModel();
        $this->documentoModel = new DocumentoModel();
        $this->pagoModel = new PagoModel();
        $this->propiedadModel = new PropiedadModel();
        $this->solicitudContratoModel = new SolicitudContratoModel();
    }

    /**
     * API: Devuelve los datos de un proceso de venta específico.
     * Ruta: GET /api/procesos-venta/{id}
     * @param int $id El ID del proceso de venta.
     */
    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.ver');

        $proceso = $this->procesoVentaModel->findById($id);

        if (!$proceso) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Proceso de venta no encontrado.'], 404);
            return;
        }

        $this->jsonResponse(['status' => 'success', 'data' => $proceso]);
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
            'estatus_proceso_id' => 1,
            'usuario_responsable_id' => $this->prospectoModel->findById($prospectoId)['usuario_responsable_id'] ?? $this->permissionManager->getUserId()
        ];

        try {
            $procesoId = $this->procesoVentaModel->create($data);

            if (!$this->propiedadModel->updateStatus($propiedadId, 'En Proceso')) {
                throw new \Exception('No se pudo actualizar el estado de la propiedad.');
            }

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

    public function updateStatus(int $id)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.update');

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatusId = filter_var($input['estatus_proceso_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$newStatusId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Nuevo estatus no especificado.'], 400);
        }
        // TODO: Añadir validación para asegurar que el avance de estatus es válido (ej. no se puede ir de 1 a 5 directamente).

        if ($this->procesoVentaModel->updateStatus($id, $newStatusId)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Proceso actualizado con éxito.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el proceso.'], 500);
        }
    }

    public function generarFolio(int $procesoId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.generar_folio');
        $userId = $this->permissionManager->getUserId();

        try {
            $procesoData = $this->procesoVentaModel->findForFolioGeneration($procesoId);

            if (!$procesoData) throw new \Exception('Datos del proceso no encontrados.');

            $prefix = $procesoData['sucursal_abreviatura'] . '-' . $procesoData['administradora_abreviatura'];

            $nextNumber = $this->folioApartadoModel->getNextFolioNumber($prefix);
            $folioCompleto = $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $folioData = [
                'sucursal_id' => $procesoData['sucursal_id'],
                'folio' => $folioCompleto,
                'usuario_propietario_id' => $procesoData['usuario_responsable_id'],
                'estatus_folio_id' => 1,
                'creado_por_usuario_id' => $userId,
                'actualizado_por_usuario_id' => $userId,
            ];

            $folioId = $this->folioApartadoModel->create($folioData);
            if (!$folioId) throw new \Exception('No se pudo crear el registro del folio.');

            $pdfPath = $this->createApartadoPdf($folioCompleto, $procesoData);

            if (!$pdfPath) {
                throw new \Exception('No se pudo generar o guardar el archivo PDF.');
            }

            if (!$this->folioApartadoModel->updatePdfPath($folioId, $pdfPath)) {
                throw new \Exception('No se pudo actualizar la ruta del PDF en la base de datos.');
            }

            $downloadUrl = "/folios-apartado/descargar/{$folioId}";

            $siguienteEstatusId = 3;
            $this->procesoVentaModel->assignFolioAndUpdateStatus($procesoId, $folioCompleto, $siguienteEstatusId);

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Folio y PDF generados con éxito.',
                'data' => [
                    'folio' => $folioCompleto,
                    'pdf_url' => $downloadUrl
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error en ProcesoVentaApiController::generarFolio: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al generar el folio.'], 500);
        }
    }

    /**
     * API: Recibe el comprobante de pago, lo guarda, actualiza el estado del proceso
     * y crea una solicitud de validación de pago.
     * @param int $procesoId
     */
    public function subirComprobante(int $procesoId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.subir_comprobante');
        $userId = $this->permissionManager->getUserId();

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Error en la subida del archivo.'], 400);
            return;
        }

        $tipoDocumentoId = filter_var($_POST['tipo_documento_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$tipoDocumentoId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Tipo de documento no especificado.'], 400);
            return;
        }

        try {
            $file = $_FILES['file'];
            $storagePath = "comprobantes_pago/{$procesoId}/" . uniqid('pago_') . '-' . basename($file['name']);
            $fullPath = BASE_PATH . '/storage/app/' . $storagePath;

            if (!is_dir(dirname($fullPath))) mkdir(dirname($fullPath), 0755, true);
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new \Exception('No se pudo guardar el archivo físico.');
            }

            $docData = [
                'proceso_venta_id' => $procesoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'nombre_archivo' => basename($file['name']),
                'ruta_archivo' => $storagePath,
                'subido_por_usuario_id' => $userId
            ];

            $documentoId = $this->documentoModel->createForProceso($docData);

            if (!$documentoId) throw new \Exception('Error al registrar el documento en la base de datos.');

            $siguienteEstatusId = 4;
            $this->procesoVentaModel->updateStatus($procesoId, $siguienteEstatusId);

            $pagoData = [
                'proceso_venta_id'          => $procesoId,
                'tipo_pago_id'              => 1, // 1 = 'Apartado'
                'monto'                     => 10000.00,
                'documento_comprobante_id'  => $documentoId,
                'user_id'                   => $userId
            ];

            $this->pagoModel->create($pagoData);

            $this->jsonResponse(['status' => 'success', 'message' => 'Comprobante subido. Se ha enviado a validación.']);
        } catch (\Exception $e) {
            error_log("Error al subir comprobante: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al procesar el comprobante.'], 500);
        }
    }

    /**
     * Función privada para crear el PDF usando Dompdf.
     * @param string $folio
     * @param array $data
     * @return string La ruta pública al PDF guardado.
     */
    private function createApartadoPdf(string $folio, array $data): string
    {
        // --- Cargar el contenido de la plantilla HTML ---
        ob_start();

        // Pasamos los datos a la plantilla
        extract($data);

        include(BASE_PATH . '/app/Views/pdf_templates/recibo_apartado.php');
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfDir = BASE_PATH . "/storage/app/recibos_apartado/";
        if (!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);

        $fileName = "recibo-apartado-{$folio}.pdf";
        $filePath = $pdfDir . $fileName;
        file_put_contents($filePath, $dompdf->output());

        return "recibos_apartado/" . $fileName;
    }

    /**
     * API: Devuelve los procesos de venta de un cliente específico.
     * @param int $clienteId
     */
    public function indexByCliente(int $clienteId)
    {
        $this->checkAuthAndPermissionApi('clientes.ver');
        $procesos = $this->procesoVentaModel->findAllByClienteId($clienteId);
        $this->jsonResponse(['status' => 'success', 'data' => $procesos]);
    }

    /**
     * API: Crea una solicitud de contrato y avanza el estado del proceso de venta.
     * @param int $procesoId
     */
    public function solicitarContrato(int $procesoId)
    {
        $this->checkAuthAndPermissionApi('contratos.solicitar');
        $userId = $this->permissionManager->getUserId();

        try {
            // Actualizar el estado del proceso de venta al siguiente paso
            $siguienteEstatusId = 6; // 6 = 'Solicitud de Contrato Generada'

            if (!$this->procesoVentaModel->updateStatus($procesoId, $siguienteEstatusId)) {
                throw new \Exception('No se pudo actualizar el estado del proceso de venta.');
            }

            // Crear la nueva solicitud de contrato
            $solicitudData = [
                'proceso_venta_id' => $procesoId,
                'solicitado_por_usuario_id' => $userId
            ];

            $solicitudId = $this->solicitudContratoModel->create($solicitudData);

            if (!$solicitudId) {
                throw new \Exception('No se pudo crear la solicitud de contrato.');
            }

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Solicitud de contrato enviada con éxito. El área correspondiente ha sido notificada.'
            ]);
        } catch (\Exception $e) {
            error_log("Error al solicitar contrato: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al procesar la solicitud.'], 500);
        }
    }

    /**
     * Sube un documento a un proceso y avanza su estado.
     * @param int $procesoId
     */
    public function subirDocumentoYActualizarEstatus(int $procesoId)
    {
        $this->checkAuthAndPermissionApi('procesos_venta.update');
        $userId = $this->permissionManager->getUserId();

        // Validar datos
        if (empty($_FILES['file'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se ha subido ningún archivo.'], 400);
            return;
        }

        $tipoDocumentoId = filter_var($_POST['tipo_documento_id'] ?? null, FILTER_VALIDATE_INT);
        $siguienteEstatusId = filter_var($_POST['siguiente_estatus_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$tipoDocumentoId || !$siguienteEstatusId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Faltan parámetros.'], 400);
            return;
        }

        try {
            // Guardar el archivo y el registro del documento
            $file = $_FILES['file'];

            $storageBasePath = "contratos_firmados/{$procesoId}/";
            $uploadDir = BASE_PATH . '/storage/app/' . $storageBasePath;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid('contrato_firmado_') . '-' . basename($file['name']);
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \Exception('No se pudo mover el archivo subido.');
            }

            $storagePath = $storageBasePath . $fileName;

            $docData = [
                'proceso_venta_id' => $procesoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'ruta_archivo' => $storagePath,
                'nombre_archivo' => basename($file['name']),
                'subido_por_usuario_id' => $userId,
                'creado_por_usuario_id' => $userId
            ];
            $this->documentoModel->createForProceso($docData);

            // Actualizar el estado del proceso de venta
            $this->procesoVentaModel->updateStatus($procesoId, $siguienteEstatusId);
            $this->jsonResponse(['status' => 'success', 'message' => 'Documento subido y proceso actualizado con éxito.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
