<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\UsuarioModel;

class AuthApiController extends ApiController
{
  private $usuarioModel;

  public function __construct()
  {
    $this->usuarioModel = new UsuarioModel();
    parent::__construct();
  }

  /**
   * Procesa el intento de login.
   * Maneja las peticiones POST del formulario de login y obtiene los permisos del usuario.
   */
  public function login()
  {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => 'Por favor, introduce tu email y contraseña.'
      ], 400);
    }

    try {
      $user = $this->usuarioModel->findByEmail($email);

      if (!$user || !$this->usuarioModel->verifyPassword($password, $user['password_hash'])) {
        $this->jsonResponse([
          'status' => 'error',
          'message' => 'Email o contraseña incorrectos.'
        ], 401);
      }

      $permissionsLoaded = $this->permissionManager->loadUserPermissions($user['id']);

      if (!$permissionsLoaded) {
        $this->jsonResponse([
          'status' => 'error',
          'message' => 'Error al cargar permisos del usuario.'
        ], 500);
      }

      $this->jsonResponse([
        'status' => 'success',
        'message' => 'Login exitoso',
        'redirect' => '/'
      ], 200);
    } catch (\Exception $e) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => 'Error al procesar el login: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Endpoint API para obtener los permisos del usuario actual.
   */
  public function apiGetUserPermissions()
  {
    if (!$this->permissionManager->getUserId()) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => 'No autenticado.'
      ], 401);
    }

    $this->jsonResponse([
      'status' => 'success',
      'permissions' => $this->permissionManager->getUserPermissions(),
      'user_id' => $this->permissionManager->getUserId(),
      'rol_id' => $this->permissionManager->getRolId(),
      'sucursal_id' => $this->permissionManager->getSucursalId()
    ], 200);
  }
}
