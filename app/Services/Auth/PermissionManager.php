<?php

namespace App\Services\Auth;

use App\Services\Database\Database;
use PDO;
use PDOException;

class PermissionManager
{
  private $db;
  private static $instance = null;

  private function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Obtiene la única instancia de PermissionManager (Singleton).
   * @return PermissionManager
   */
  public static function getInstance(): PermissionManager
  {
    if (self::$instance === null) {
      self::$instance = new PermissionManager();
    }
    return self::$instance;
  }

  /**
   * Carga los permisos de un usuario en la sesión.
   * Debe llamarse al iniciar sesión el usuario.
   *
   * @param int $userId El ID del usuario.
   * @param int $rolId El ID del rol del usuario.
   * @return bool True si los permisos se cargaron con éxito, false en caso contrario.
   */
  public function loadUserPermissions(int $userId): bool
  {
    try {
      $userStmt = $this->db->prepare("SELECT id, nombre, email, rol_id, sucursal_id FROM usuarios WHERE id = :user_id LIMIT 1");
      $userStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
      $userStmt->execute();
      $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);

      if (!$userResult) {
        error_log("Error: Usuario con ID {$userId} no encontrado para cargar permisos.");
        $_SESSION['user_permissions'] = [];

        unset($_SESSION['user_id']);
        unset($_SESSION['rol_id']);
        unset($_SESSION['sucursal_id']);
        unset($_SESSION['nombre_usuario']);

        return false;
      }

      $rolId = $userResult['rol_id'];

      $sql = "SELECT p.nombre
        FROM cat_permisos p
        JOIN rol_permiso rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = :rol_id";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
      $stmt->execute();

      $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

      $_SESSION['user_permissions'] = $permissions;
      $_SESSION['user_id'] = $userResult['id'];
      $_SESSION['rol_id'] = $userResult['rol_id'];
      $_SESSION['sucursal_id'] = $userResult['sucursal_id'];
      $_SESSION['nombre_usuario'] = $userResult['nombre'];

      return true;
    } catch (PDOException $e) {
      error_log("Error al cargar permisos para el usuario {$userId} (Rol: {$rolId}): " . $e->getMessage());

      $_SESSION['user_permissions'] = [];
      unset($_SESSION['user_id']);
      unset($_SESSION['rol_id']);
      unset($_SESSION['sucursal_id']);
      unset($_SESSION['nombre_usuario']);

      return false;
    }
  }

  /**
   * Verifica si el usuario actual (en sesión) tiene un permiso específico.
   *
   * @param string $permissionName El nombre del permiso a verificar (ej. 'propiedades.crear').
   * @return bool True si el usuario tiene el permiso, false en caso contrario.
   */
  public function hasPermission(string $permissionName): bool
  {
    if (!isset($_SESSION['user_permissions']) || !isset($_SESSION['user_id'])) {
      return false;
    }

    return in_array($permissionName, $_SESSION['user_permissions']);
  }

  /**
   * Devuelve todos los permisos del usuario actual en un array de strings.
   * @return array Un array de strings con los nombres de los permisos.
   */
  public function getUserPermissions(): array
  {
    return $_SESSION['user_permissions'] ?? [];
  }

  /**
   * Devuelve el ID del usuario actual en sesión.
   * @return int|null El ID del usuario o null si no hay sesión.
   */
  public function getUserId(): ?int
  {
    return $_SESSION['user_id'] ?? null;
  }

  /**
   * Devuelve el ID del rol del usuario actual en sesión.
   * @return int|null El ID del rol o null si no hay sesión.
   */
  public function getRolId(): ?int
  {
    return $_SESSION['rol_id'] ?? null;
  }

  /**
   * Devuelve el ID de la sucursal del usuario actual en sesión.
   * @return int|null El ID de la sucursal o null si no hay sesión.
   */
  public function getSucursalId(): ?int
  {
    return $_SESSION['sucursal_id'] ?? null;
  }

  /**
   * Devuelve el nombre del usuario actual en sesión.
   * @return string|null El nombre del usuario o null si no hay sesión.
   */
  public function getUserName(): ?string
  {
    return $_SESSION['nombre_usuario'] ?? null;
  }  
}
