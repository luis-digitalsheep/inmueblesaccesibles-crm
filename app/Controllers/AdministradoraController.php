<?php

namespace App\Controllers;

use App\Models\Catalogo;

class AdministradoraController {
  private $catalogoModel;

  public function __construct() {
    $this->catalogoModel = new Catalogo();
  }

  public function apiGetAll() {
    $administradoras = $this->catalogoModel->getAll('cat_administradoras');
    echo json_encode(['status' => 'success', 'data' => $administradoras]);
  }
}
