<?php

namespace App\Models;

use App\Services\Database\Database;
use PDO;
use PDOException;

class UsuarioModel
{
  private $db;
  private $tableName = 'usuarios';

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  public function getAll($filters = []): array
  {
    $sql = "SELECT id, nombre, email, telefono, avatar_url FROM {$this->tableName}";

    $params = [];

    if (isset($filters['id']) && $filters['id'] !== '') {
      $sql .= " WHERE id = :id";

      $params[':id'] = (int) $filters['id'];
    } else {
      $sql .= " WHERE activo = 1";
    }

    $sql .= " ORDER BY nombre ASC";


    try {
      $stmt = $this->db->prepare($sql);

      foreach ($params as $key => $value) {
        $stmt->bindParam($key, $params[$key], PDO::PARAM_INT);
      }

      $stmt->execute();
      

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::getAll: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Busca un usuario en la base de datos por su email.
   *
   * @param string $email El email del usuario que se está intentando autenticar.
   * @return array|null Un array asociativo con los datos del usuario si se encuentra, o null si no existe.
   */
  public function findByEmail($email)
  {
    try {
      $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE email = :email LIMIT 1");
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al buscar usuario por email '{$email}': " . $e->getMessage());
      return null; // Devolvemos null para indicar que la operación falló o el usuario no fue encontrado debido a un error.
    }
  }

  /**
   * Verifica una contraseña en texto plano contra un hash de contraseña almacenado.
   * Es crucial que el hash almacenado haya sido generado previamente con password_hash().
   *
   * @param string $password La contraseña en texto plano (la que el usuario introduce).
   * @param string $hashedPassword El hash de la contraseña recuperado de la base de datos (columna 'password_hash').
   * @return bool True si la contraseña en texto plano coincide con el hash, false en caso contrario.
   */
  public function verifyPassword($password, $hashedPassword)
  {
    return password_verify($password, $hashedPassword);
  }

  /**
   * Obtiene una lista simple de todos los usuarios activos.
   * Ideal para poblar elementos <select> en los formularios.
   * @return array Lista de usuarios con id y nombre.
   */
  public function getAllForSelect(): array
  {
    $sql = "SELECT id, nombre FROM usuarios WHERE activo = 1 ORDER BY nombre ASC";

    try {
      $stmt = $this->db->query($sql);

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::getAllForSelect: " . $e->getMessage());
      return [];
    }
  }
}
