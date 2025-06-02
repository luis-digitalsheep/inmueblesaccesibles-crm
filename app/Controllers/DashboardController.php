<?php

namespace App\Controllers;

class DashboardController {
  public function __construct() {
  }

  /**
   * Muestra la página principal del dashboard con datos resumidos.
   */
  public function index($currentRoute) {
    $data = [
      'pageTitle' => 'Panel de Control',
      'pageDescription' => 'Resumen general y métricas clave.',
      'currentRoute' => $currentRoute
    ];

    extract($data);

    include BASE_PATH . '/app/Views/layouts/header.php';
    include BASE_PATH . '/app/Views/dashboard/index.php';
    include BASE_PATH . '/app/Views/layouts/footer.php';
  }
}
