<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class DashboardController  extends WebController
{
  public function __construct() {
    parent::__construct();
  }

  /**
   * Muestra la página principal del dashboard con datos resumidos.
   */
  public function index($currentRoute)
  {
    $data = [
      'pageTitle' => 'Panel de Control',
      'pageDescription' => 'Resumen general y métricas clave.',
      'currentRoute' => $currentRoute
    ];

    $this->render('dashboard/index', $data, $currentRoute);
  }
}
