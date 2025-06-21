<?php

namespace App\Controllers\Web;

use App\Controllers\WebController;

class AuthController extends WebController
{
  public function __construct() {
    parent::__construct();
  }

  /**
   * Muestra el formulario de login.
   */
  public function showLoginForm()
  {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
      $this->redirect('/');
    }

    include BASE_PATH . '/app/Views/auth/login.php';
  }

  /**
   * Cierra la sesión del usuario.
   */
  public function logout()
  {
    $_SESSION = array();

    // Invalidar completamente la sesión en el navegador del cliente.
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }

    session_destroy();

    $this->redirect('/login');
  }
}
