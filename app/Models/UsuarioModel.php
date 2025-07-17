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

  public function getAll($filters = [], int $limit = 15, int $offset = 0): array
  {
    $sql = "SELECT 
        u.id, u.nombre, u.email, u.telefono, u.avatar_url, u.activo,
        r.nombre as rol_nombre,
        s.nombre as sucursal_nombre
      FROM {$this->tableName} u
      LEFT JOIN cat_roles r ON u.rol_id = r.id
      LEFT JOIN cat_sucursales s ON u.sucursal_id = s.id
    ";

    $whereClauses = [];
    $params = [];

    if (!empty($filters['nombre'])) {
      $whereClauses[] = "(u.nombre LIKE :nombre OR u.email LIKE :nombre)";
      $params[':nombre'] = '%' . $filters['nombre'] . '%';
    }

    if (!empty($filters['id'])) {
      $whereClauses[] = "u.id = :id";
      $params[':id'] = (int) $filters['id'];
    }

    if (!empty($whereClauses)) {
      $sql .= " WHERE " . implode(' AND ', $whereClauses);
    }

    $sql .= " ORDER BY u.nombre ASC LIMIT :limit OFFSET :offset";

    try {
      $stmt = $this->db->prepare($sql);

      foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value);
      }

      $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::getAll: " . $e->getMessage());

      return [];
    }
  }

  /**
   * Obtiene el conteo total de usuarios para la paginación.
   */
  public function getTotal(array $filters = []): int
  {
    $sql = "SELECT COUNT(u.id) as total FROM {$this->tableName} u";

    $whereClauses = [];
    $params = [];

    if (!empty($filters['nombre'])) {
      $whereClauses[] = "(u.nombre LIKE :nombre OR u.email LIKE :nombre)";
      $params[':nombre'] = '%' . $filters['nombre'] . '%';
    }

    if (!empty($filters['id'])) {
      $whereClauses[] = "u.id = :id";
      $params[':id'] = (int) $filters['id'];
    }

    if (!empty($whereClauses)) {
      $sql .= " WHERE " . implode(' AND ', $whereClauses);
    }

    try {
      $stmt = $this->db->prepare($sql);

      foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value);
      }

      $stmt->execute();
      return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::getTotal: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Busca un usuario por su ID.
   */
  public function findById(int $id): ?array
  {
    $sql = "SELECT * FROM {$this->tableName} WHERE id = :id";
    try {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::findById: " . $e->getMessage());
      return null;
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
  public function getAllForSelect($filters = []): array
  {
    $sql = "SELECT id, nombre FROM usuarios WHERE activo = 1 ORDER BY nombre ASC";

    $whereClauses = [];
    $params = [];

    if (!empty($filters['id'])) {
      $whereClauses[] = "u.id = :id";
      $params[':id'] = (int) $filters['id'];
    }

    try {
      $stmt = $this->db->prepare($sql);

      foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value);
      }

      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::getAllForSelect: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Crea un nuevo usuario en la base de datos.
   */
  public function create(array $data): ?int
  {
    $sql = "INSERT INTO {$this->tableName} (nombre, email, telefono, password_hash, rol_id, sucursal_id, activo, creado_por_usuario_id, actualizado_por_usuario_id) 
                VALUES (:nombre, :email, :telefono, :password_hash, :rol_id, :sucursal_id, :activo, :user_id, :user_id)";
    try {
      $stmt = $this->db->prepare($sql);
      $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

      $stmt->bindParam(':nombre', $data['nombre']);
      $stmt->bindParam(':email', $data['email']);
      $stmt->bindParam(':telefono', $data['telefono']);
      $stmt->bindParam(':password_hash', $passwordHash);
      $stmt->bindParam(':rol_id', $data['rol_id'], PDO::PARAM_INT);
      $stmt->bindParam(':sucursal_id', $data['sucursal_id'], PDO::PARAM_INT);
      $stmt->bindValue(':activo', $data['activo'] ?? 1, PDO::PARAM_INT);
      $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);

      if ($stmt->execute()) {
        return (int)$this->db->lastInsertId();
      }

      return null;
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::create: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Actualiza un usuario existente.
   */
  public function update(int $id, array $data): bool
  {
    $fields = [
      'nombre' => $data['nombre'],
      'email' => $data['email'],
      'telefono' => $data['telefono'],
      'rol_id' => $data['rol_id'],
      'sucursal_id' => $data['sucursal_id'],
      'activo' => $data['activo'],
      'actualizado_por_usuario_id' => $data['user_id']
    ];

    if (!empty($data['password'])) {
      $fields['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $setClauses = [];
    foreach ($fields as $key => $value) {
      $setClauses[] = "{$key} = :{$key}";
    }

    $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses) . " WHERE id = :id";

    try {
      $stmt = $this->db->prepare($sql);
      $fields['id'] = $id;
      return $stmt->execute($fields);
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::update: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Desactiva un usuario (borrado lógico).
   */
  public function delete(int $id): bool
  {
    $sql = "UPDATE {$this->tableName} SET activo = 0 WHERE id = :id";
    try {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Error en UsuarioModel::delete: " . $e->getMessage());
      return false;
    }
  }
}
