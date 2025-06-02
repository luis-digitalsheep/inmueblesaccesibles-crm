<?php

namespace App\Controllers;

use App\Models\Catalogo;

class SucursalController {
  private $catalogoModel;

  public function __construct() {
    $this->catalogoModel = new Catalogo();
  }

  public function apiGetAll() {
    $sucursales = $this->catalogoModel->getAll('cat_sucursales');
    echo json_encode(['status' => 'success', 'data' => $sucursales]);
  }
}
