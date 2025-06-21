<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\PropiedadRevisionModel;

class PropiedadRevisionApiController extends ApiController
{
  private $propiedadRevisionModel;

  public function __construct()
  {
    $this->propiedadRevisionModel = new PropiedadRevisionModel();

    parent::__construct();
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
}
