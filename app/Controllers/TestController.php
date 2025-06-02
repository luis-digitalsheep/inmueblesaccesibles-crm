<?php

namespace App\Controllers;

class TestController {

  public function index(string $currentRoute = '') {
    $data = [
      'pageTitle' => 'En desarrollo',
      'pageDescription' => 'Pagina en desarrollo',
      'currentRoute' => $currentRoute,
    ];

    extract($data);

    include BASE_PATH . '/app/Views/layouts/header.php';
    include BASE_PATH . '/app/Views/test/index.php';
    include BASE_PATH . '/app/Views/layouts/footer.php';
  }
}
