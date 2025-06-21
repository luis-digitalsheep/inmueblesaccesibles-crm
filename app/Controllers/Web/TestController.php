<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class TestController extends WebController {

  public function __construct() {
    parent::__construct();
  }

  public function index(string $currentRoute = '') {
    $data = [
      'pageTitle' => 'En desarrollo',
      'pageDescription' => 'Pagina en desarrollo',
    ];

    $this->render('test/index', $data, $currentRoute);
  }
}
