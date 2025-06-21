<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\CatalogoModel;

class AdministradoraApiController extends ApiController
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
      $administradoras = $this->catalogoModel->getAll('cat_administradoras');

      $this->jsonResponse([
        'status' => 'success',
        'data' => $administradoras
      ], 200);
    } catch (\Exception $e) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
