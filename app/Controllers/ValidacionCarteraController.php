<?php

namespace App\Controllers;

use App\Services\Auth\PermissionManager;

use App\Models\PropiedadRevision;
use App\Models\Catalogo;

class ValidacionCarteraController {
  private $propiedadRevisionModel;
  private $catalogoModel;
  private $permissionManager;

  public function __construct() {
    $this->propiedadRevisionModel = new PropiedadRevision();
    $this->catalogoModel = new Catalogo();

    $this->permissionManager = PermissionManager::getInstance();
  }

  /**
   * Muestra el listado de propiedades, aplicando filtros y control de acceso por permisos.
   *
   * @param string $currentRoute La ruta actual de la solicitud (viene del router).
   */
  public function index(string $currentRoute = '') {
    if (!$this->permissionManager->hasPermission('propiedades.ver')) {
      http_response_code(403);
      include BASE_PATH . '/app/Views/errors/403.php';
      exit();
    }

    $filters = [];

    if (isset($_GET['sucursal']) && $_GET['sucursal'] !== '') {
      $filters['sucursal_id'] = $_GET['sucursal'];
    }
    if (isset($_GET['administradora']) && $_GET['administradora'] !== '') {
      $filters['administradora_id'] = $_GET['administradora'];
    }

    $sucursales = $this->catalogoModel->getAll('cat_sucursales');
    $administradoras = $this->catalogoModel->getAll('cat_administradoras');

    $data = [
      'pageTitle' => 'Validaciones de Cartera',
      'pageDescription' => 'Listado de propiedades pendientes de validación.',
      'currentRoute' => $currentRoute,

      'userSucursalId' => $this->permissionManager->getSucursalId(),

      'sucursales' => $sucursales,
      'administradoras' => $administradoras,

      'currentFilters' => $filters
    ];

    extract($data);

    include BASE_PATH . '/app/Views/layouts/header.php';
    include BASE_PATH . '/app/Views/validaciones-cartera/list.php';
    include BASE_PATH . '/app/Views/layouts/footer.php';
  }

  /**
   * Endpoint API para obtener el listado de propiedades en formato JSON.
   * Aplica filtros, paginación y control de acceso por permisos.
   * Retorna un objeto JSON con 'data' (las propiedades) y 'total' (para paginación).
   */
  public function apiGetAll() {
    header('Content-Type: application/json');

    if (!$this->permissionManager->hasPermission('propiedades.ver')) {
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Acceso denegado a la API de propiedades.']);
      exit();
    }

    $filters = [];
    $filters['sucursal_id'] = $_GET['sucursal'] ?? '';
    $filters['administradora_id'] = $_GET['administradora'] ?? '';

    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    try {
      $propiedades = $this->propiedadRevisionModel->getAll($filters, $limit, $offset);
      $totalPropiedades = $this->propiedadRevisionModel->getTotalPropiedades($filters);

      http_response_code(200);

      echo json_encode([
        'status' => 'success',
        'data' => $propiedades,
        'total' => $totalPropiedades,
        'limit' => $limit,
        'offset' => $offset
      ]);
    } catch (\Exception $e) {
      error_log("Error en PropiedadController::apiGetAll(): " . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al obtener propiedades.']);
    }

    exit();
  }
}
