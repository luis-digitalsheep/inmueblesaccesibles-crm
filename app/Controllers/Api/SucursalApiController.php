<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\CatalogoModel;

class SucursalApiController extends ApiController
{
  private $catalogoModel;

  public function __construct()
  {
    parent::__construct();
    $this->catalogoModel = new CatalogoModel();
  }

  public function apiGetAll()
  {
    try {
      $sucursales = $this->catalogoModel->getAll('cat_sucursales');

      $this->jsonResponse([
        'status' => 'success',
        'data' => $sucursales
      ], 200);
    } catch (\Exception $e) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
