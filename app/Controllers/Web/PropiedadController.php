<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

use App\Models\PropiedadModel;
use App\Models\CatalogoModel;

class PropiedadController extends WebController
{
  private $propiedadModel;
  private $catalogoModel;

  public function __construct()
  {
    parent::__construct();

    $this->propiedadModel = new PropiedadModel();
    $this->catalogoModel = new CatalogoModel();
  }

  /**
   * Muestra el listado de propiedades, aplicando filtros y control de acceso por permisos.
   *
   * @param string $currentRoute La ruta actual de la solicitud (viene del router).
   */
  public function index(string $currentRoute = '')
  {
    $this->checkPermission('propiedades.ver');

    $filters = [];


    $filters['sucursal'] = $_GET['sucursal'] ?? '';
    $filters['administradora_id'] = $_GET['administradora'] ?? '';
    $filters['estado_id'] = $_GET['estado'] ?? '';
    $filters['municipio_id'] = $_GET['municipio'] ?? '';
    $filters['estatus_disponibilidad'] = $_GET['estatus_disponibilidad'] ?? '';

    $sucursales = $this->catalogoModel->getAll('cat_sucursales');
    $administradoras = $this->catalogoModel->getAll('cat_administradoras');
    $estados = $this->catalogoModel->getAll('cat_estados');

    $data = [
      'pageTitle' => 'Listado de Propiedades',
      'pageDescription' => 'Gestiona las propiedades disponibles en el sistema.',

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

    $this->render('propiedades/list', $data, $currentRoute);
  }

  /**
   * Muestra el detalle completo de una propiedad específica por su ID.
   * Aplica verificaciones de permisos para asegurar que el usuario tenga acceso a ver la propiedad.
   *
   * @param int $id El ID de la propiedad a mostrar.
   * @param string $currentRoute La ruta actual de la solicitud (viene del router).
   */
  public function show(int $id, string $currentRoute = '')
  {
    $this->checkPermission('propiedades.ver');

    $id = (int) $id;

    $propiedad = $this->propiedadModel->getById($id);

    if (!$propiedad) {
      $this->renderErrorPage(404);
    }

    if (!$this->permissionManager->hasPermission('propiedades.ver.todo')) {
      $userSucursalId = $this->permissionManager->getSucursalId();

      if ($propiedad['sucursal_id'] !== $userSucursalId) {
        $this->renderErrorPage(403);
      }
    }

    $data = [
      'pageTitle' => 'Detalle: ' . htmlspecialchars($propiedad['direccion']),
      'pageDescription' => 'Información completa de la propiedad.',
      'propiedadId' => $id,
      'propiedadDireccion' => $propiedad['direccion'],

      'permissions' => [
        'canUpdate' => $this->permissionManager->hasPermission('propiedades.editar'),
        'canDeletePropiedad' => $this->permissionManager->hasPermission('propiedades.eliminar'),
      ],
    ];

    $this->render('propiedades/show', $data, $currentRoute);
  }
}
