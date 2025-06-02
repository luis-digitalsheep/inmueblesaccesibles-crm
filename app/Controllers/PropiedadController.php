<?php

namespace App\Controllers;

use App\Services\Auth\PermissionManager;

use App\Models\Propiedad;
use App\Models\Catalogo;

class PropiedadController {
  private $propiedadModel;
  private $catalogoModel;

  private $permissionManager;

  public function __construct() {
    $this->propiedadModel = new Propiedad();
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
    if (isset($_GET['estado']) && $_GET['estado'] !== '') {
      $filters['estado_id'] = $_GET['estado'];
    }
    if (isset($_GET['municipio']) && $_GET['municipio'] !== '') {
      $filters['municipio_id'] = $_GET['municipio'];
    }
    if (isset($_GET['estatus_disponibilidad']) && $_GET['estatus_disponibilidad'] !== '') {
      $filters['estatus_disponibilidad'] = $_GET['estatus_disponibilidad'];
    }

    $sucursales = $this->catalogoModel->getAll('cat_sucursales');
    $administradoras = $this->catalogoModel->getAll('cat_administradoras');
    $estados = $this->catalogoModel->getAll('cat_estados');

    $clasificacionesLegales = $this->catalogoModel->getAll('cat_clasificacion_legal_propiedad');

    $data = [
      'pageTitle' => 'Listado de Propiedades',
      'pageDescription' => 'Gestiona las propiedades disponibles en el sistema.',
      'currentRoute' => $currentRoute,

      'canCreatePropiedad' => $this->permissionManager->hasPermission('propiedades.crear'),
      'canEditPropiedad' => $this->permissionManager->hasPermission('propiedades.editar'),
      'canDeletePropiedad' => $this->permissionManager->hasPermission('propiedades.eliminar'),
      'canLoadCartera' => $this->permissionManager->hasPermission('propiedades.cargar.cartera'),
      'userSucursalId' => $this->permissionManager->getSucursalId(),

      'sucursales' => $sucursales,
      'administradoras' => $administradoras,
      'estados' => $estados,
      'currentFilters' => $filters
    ];

    extract($data);

    include BASE_PATH . '/app/Views/layouts/header.php';
    include BASE_PATH . '/app/Views/propiedades/list.php';
    include BASE_PATH . '/app/Views/layouts/footer.php';
  }

  /**
   * Endpoint API para obtener municipios filtrados por un estado_id.
   * Llamado por JavaScript cuando se selecciona un estado en el filtro.
   */
  public function apiGetMunicipiosByEstado() {
    header('Content-Type: application/json');

    $permissionManager = PermissionManager::getInstance();

    if (!$permissionManager->getUserId() || !$permissionManager->hasPermission('propiedades.ver')) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'No autenticado o sin permiso para acceder a municipios.']);
      exit();
    }

    $estadoId = (int) ($_GET['estado_id'] ?? 0);

    if ($estadoId <= 0) {
      http_response_code(400);
      echo json_encode(['status' => 'error', 'message' => 'ID de estado inválido.']);
      exit();
    }

    try {
      $municipios = $this->catalogoModel->getMunicipiosByEstado($estadoId);
      http_response_code(200);
      echo json_encode(['status' => 'success', 'data' => $municipios]);
    } catch (\Exception $e) {
      error_log("Error en PropiedadController::apiGetMunicipiosByEstado(): " . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor al obtener municipios.']);
    }
    exit();
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
    $filters['estado_id'] = $_GET['estado'] ?? '';
    $filters['municipio_id'] = $_GET['municipio'] ?? '';
    $filters['clasificacion_legal_id'] = $_GET['clasificacion_legal'] ?? '';
    $filters['estatus_disponibilidad'] = $_GET['estatus_disponibilidad'] ?? '';

    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    try {
      $propiedades = $this->propiedadModel->getAll($filters, $limit, $offset);
      $totalPropiedades = $this->propiedadModel->getTotalPropiedades($filters);

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

  /**
   * Muestra el detalle completo de una propiedad específica por su ID.
   * Aplica verificaciones de permisos para asegurar que el usuario tenga acceso a ver la propiedad.
   *
   * @param int $id El ID de la propiedad a mostrar.
   * @param string $currentRoute La ruta actual de la solicitud (viene del router).
   */
  public function show(int $id, string $currentRoute = '') {
    if (!$this->permissionManager->hasPermission('propiedades.ver')) {
      http_response_code(403);
      include BASE_PATH . '/app/Views/errors/403.php';
      exit();
    }

    $id = (int) $id;

    $propiedad = $this->propiedadModel->getById($id);

    if (!$propiedad) {
      http_response_code(404);
      include BASE_PATH . '/app/Views/errors/404.php';
      exit();
    }

    if (!$this->permissionManager->hasPermission('propiedades.ver.todo')) {
      $userSucursalId = $permissionManager->getSucursalId();

      if ($propiedad['sucursal_id'] !== $userSucursalId) {
        http_response_code(403);
        include BASE_PATH . '/app/Views/errors/403.php';
        exit();
      }
    }

    $data = [
      'propiedad' => $propiedad,
      'pageTitle' => 'Detalle: ' . htmlspecialchars($propiedad['direccion']),
      'pageDescription' => 'Información completa de la propiedad.',
      'currentRoute' => $currentRoute,

      'canEditPropiedad' => $this->permissionManager->hasPermission('propiedades.editar'),
      'canDeletePropiedad' => $this->permissionManager->hasPermission('propiedades.eliminar'),
    ];

    extract($data);

    include BASE_PATH . '/app/Views/layouts/header.php';
    include BASE_PATH . '/app/Views/propiedades/detail.php';
    include BASE_PATH . '/app/Views/layouts/footer.php';
  }

  public function apiUploadCartera() {
    header('Content-Type: application/json');

    http_response_code(400);

    echo json_encode([
      'status' => 'error',
      'message' => 'No se pudo cargar la cartera.'
    ]);
  }
}
