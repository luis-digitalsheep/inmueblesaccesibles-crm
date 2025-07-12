<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\PropiedadFotoModel;
use App\Models\PropiedadModel;
use App\Models\PropiedadRevisionModel;

class PropiedadRevisionApiController extends ApiController
{
  private $propiedadRevisionModel;
  private $propiedadModel;
  private $propiedadFotoModel;

  public function __construct()
  {
    parent::__construct();
    $this->propiedadRevisionModel = new PropiedadRevisionModel();
    $this->propiedadModel = new PropiedadModel();
    $this->propiedadFotoModel = new PropiedadFotoModel();
  }

  /**
   * Endpoint API para obtener el listado de propiedades en revisión en formato JSON.
   * Aplica filtros, paginación y control de acceso por permisos.
   * Retorna un objeto JSON con 'data' (las propiedades) y 'total' (para paginación).
   */
  public function apiGetAll()
  {
    $this->checkAuthAndPermissionApi('validaciones_cartera.ver');

    $filters = [];
    $filters['sucursal_id'] = $_GET['sucursal'] ?? '';
    $filters['administradora_id'] = $_GET['administradora'] ?? '';
    $filters['cartera_id'] = $_GET['cartera'] ?? '';
    $filters['estatus'] = $_GET['estatus'] ?? '';

    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    try {
      $propiedades = $this->propiedadRevisionModel->getAll($filters, $limit, $offset);
      $totalPropiedades = $this->propiedadRevisionModel->getTotalPropiedades($filters);

      $this->jsonResponse([
        'status' => 'success',
        'data' => $propiedades,
        'total' => $totalPropiedades,
        'limit' => $limit,
        'offset' => $offset
      ], 200);
    } catch (\Exception $e) {
      error_log("Error en PropiedadController::apiGetAll(): " . $e->getMessage());
      $this->jsonResponse([
        'status' => 'error',
        'message' => 'Error interno del servidor al obtener propiedades en revisión.'
      ], 500);
    }
  }

  /**
   * Endpoint API para obtener una propiedad por su ID en formato JSON 
   * 
   * @param int $id El ID de la propiedad a obtener.
   */
  public function apiGetById(int $id)
  {
    $this->checkAuthAndPermissionApi('validaciones_cartera.validar');
    $revision = $this->propiedadRevisionModel->getById($id);

    if (!$revision) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Registro no encontrado'], 404);
    }

    $this->jsonResponse(['status' => 'success', 'data' => $revision]);
  }

  /**
   * Endpoint API para validar una propiedad en revisión.
   * 
   * @param int $id El ID de la propiedad a validar.
   */
  public function apiUpdate(int $id)
  {
    $this->checkAuthAndPermissionApi('validaciones_cartera.validar');

    $usuario_id = $this->permissionManager->getUserId();

    $input = json_decode(file_get_contents('php://input'), true);
    $fotosTemporales = $input['fotos_temporales'] ?? [];

    unset($input['fotos_temporales']);

    $camposNumericos = ['metros', 'avaluo_administradora', 'precio_lista', 'precio_venta', 'cofinavit'];

    foreach ($camposNumericos as $campo) {
      if (isset($input[$campo]) && $input[$campo] === '') {
        $input[$campo] = null;
      }
    }

    if (empty($input['direccion'])) {
      $this->jsonResponse(['status' => 'error', 'message' => 'La dirección es un campo requerido.'], 422);
      return;
    }

    try {
      // Crear la propiedad en la tabla `propiedades`
      $propiedadId = $this->propiedadModel->createFromRevision($usuario_id, $input);

      if (!$propiedadId) throw new \Exception("No se pudo crear el registro final de la propiedad.");

      // Manejo de fotos
      foreach ($fotosTemporales as $tempPath) {
        if (strpos($tempPath, '/uploads/temp/') !== 0) continue;

        $tempFileName = basename($tempPath);
        $sourceFilePath = BASE_PATH . '/public' . $tempPath;

        $finalDir = BASE_PATH . "/public/uploads/propiedades/{$propiedadId}/";

        if (!is_dir($finalDir)) mkdir($finalDir, 0755, true);

        $finalFileName = str_replace('temp_', '', $tempFileName);
        $finalFilePath = $finalDir . $finalFileName;
        $finalPublicPath = "/uploads/propiedades/{$propiedadId}/{$finalFileName}";

        if (file_exists($sourceFilePath) && rename($sourceFilePath, $finalFilePath)) {
          // Guardar la ruta de las fotos en las base de datos
          $this->propiedadFotoModel->create($usuario_id, [
            'propiedad_id' => $propiedadId,
            'ruta_archivo' => $finalPublicPath,
            'nombre_archivo' => $finalFileName
          ]);
        }
      }

      // Actualizar el estatus de la revisión a "Validado" y asociarla con la propiedad final
      $this->propiedadRevisionModel->updateStatus($id, 'Validado', $propiedadId);

      $this->jsonResponse([
        'status' => 'success',
        'message' => "Propiedad validada con éxito. Nuevo ID de Propiedad: {$propiedadId}",
        'data' => ['propiedad_id' => $propiedadId]
      ], 200);
    } catch (\Exception $e) {
      error_log("Error en validación de cartera: " . $e->getMessage());

      $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
