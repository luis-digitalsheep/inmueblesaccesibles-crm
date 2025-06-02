<?php

namespace App\Controllers;

use App\Services\Auth\PermissionManager;

use App\Models\Usuario;

class AuthController {
  private $usuarioModel;
  private $permissionManager;

  public function __construct() {
    $this->usuarioModel = new Usuario();
    $this->permissionManager = PermissionManager::getInstance();
  }

  /**
   * Muestra el formulario de login.
   */
  public function showLoginForm() {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
      header('Location: /');
      exit();
    }

    include BASE_PATH . '/app/Views/auth/login.php';
  }

  /**
   * Procesa el intento de login.
   * Maneja las peticiones POST del formulario de login y obtiene los permisos del usuario.
   */
  public function login() {
    header('Content-Type: application/json');

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
      echo json_encode(['status' => 'error', 'message' => 'Por favor, introduce tu email y contraseña.']);
      http_response_code(400);
    }

    $user = $this->usuarioModel->findByEmail($email);

    if ($user && $this->usuarioModel->verifyPassword($password, $user['password_hash'])) {
      $permissionsLoaded = $this->permissionManager->loadUserPermissions($user['id']);

      if (!$permissionsLoaded) {
        echo json_encode(['status' => 'error', 'message' => 'Error al cargar permisos.']);
        http_response_code(500);
        exit();
      }

      echo json_encode(['status' => 'success', 'message' => '¡Login exitoso!', 'redirect' => '/']);
      http_response_code(200);
      exit();
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Email o contraseña incorrectos.']);
      http_response_code(401);
      exit();
    }
  }

  /**
   * Cierra la sesión del usuario.
   */
  public function logout() {
    $_SESSION = array();

    // Esto es importante para invalidar completamente la sesión en el navegador del cliente.
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

    header('Location: /login');
    exit();
  }

  /**
   * Endpoint API para obtener los permisos del usuario actual.
   * Será llamado por JavaScript.
   */
  public function apiGetUserPermissions() {
    header('Content-Type: application/json');

    if (!$this->permissionManager->getUserId()) {
      http_response_code(401);
      echo json_encode(['status' => 'error', 'message' => 'No autenticado.']);
      exit();
    }

    echo json_encode([
      'status' => 'success',
      'permissions' => $this->permissionManager->getUserPermissions(),
      'user_id' => $this->permissionManager->getUserId(),
      'rol_id' => $this->permissionManager->getRolId(),
      'sucursal_id' => $this->permissionManager->getSucursalId()
    ]);
    http_response_code(200);
    exit();
  }
}
