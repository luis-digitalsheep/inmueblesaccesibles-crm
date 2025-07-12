<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

use App\Models\CarteraModel;
use App\Models\PropiedadRevisionModel;
use App\Models\CatalogoModel;

class PropiedadRevisionController extends WebController
{
  private $propiedadRevisionModel;
  private $catalogoModel;
  private $carteraModel;

  public function __construct()
  {
    parent::__construct();

    $this->propiedadRevisionModel = new PropiedadRevisionModel();
    $this->catalogoModel = new CatalogoModel();
    $this->carteraModel = new CarteraModel();
  }

  /**
   * Muestra el listado de propiedades en revisión, aplicando filtros y control de acceso por permisos.
   *
   * @param string $currentRoute La ruta actual de la solicitud (viene del router).
   */
  public function index(string $currentRoute = '')
  {
    $this->checkPermission('validaciones_cartera.ver');

    $filters = [];

    $filters['sucursal_id'] = $_GET['sucursal'] ?? '';
    $filters['administradora_id'] = $_GET['administradora'] ?? '';
    $filters['cartera_id'] = $_GET['cartera'] ?? '';
    $filters['estatus'] = $_GET['estatus'] ?? '';

    $sucursales = $this->catalogoModel->getAll('cat_sucursales');
    $administradoras = $this->catalogoModel->getAll('cat_administradoras');
    $carteras = $this->carteraModel->getAll();

    $data = [
      'pageTitle' => 'Validaciones de Cartera',
      'pageDescription' => 'Listado de propiedades pendientes de validación.',

      'userSucursalId' => $this->permissionManager->getSucursalId(),

      'canValidate' => $this->permissionManager->hasPermission('validaciones_cartera.validar'),

      'sucursales' => $sucursales,
      'administradoras' => $administradoras,
      'carteras' => $carteras,

      'currentFilters' => $filters
    ];

    $this->render('validaciones-cartera/list', $data, $currentRoute);
  }

  /**
   * Muestra el formulario para editar/validar una propiedad en revisión.
   */
  public function edit(int $id, string $currentRoute = '')
  {
    $this->checkPermission('validaciones_cartera.ver');

    $propiedadRevision = $this->propiedadRevisionModel->getById($id);

    if (!$propiedadRevision) {
      $this->renderErrorPage(404, 'Registro en revisión no encontrado.');
      return;
    }

    $propiedadRevisionId = $propiedadRevision['id'];

    $data = [
      'pageTitle' => 'Validar Propiedad de Cartera: ' . $propiedadRevision['numero_credito'],
      'pageDescription' => 'Revise, corrija y complete la información de la propiedad.',
      'propiedadRevisionID' => $propiedadRevisionId,

      'permissions' => [
        'validaciones_cartera.validar' => true
      ],
    ];

    $this->render('validaciones-cartera/show', $data, $currentRoute);
  }
}
