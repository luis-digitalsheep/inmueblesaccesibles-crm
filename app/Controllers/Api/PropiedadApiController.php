<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\PropiedadModel;

class PropiedadApiController extends ApiController
{
  private $propiedadModel;

  public function __construct()
  {
    parent::__construct();

    $this->propiedadModel = new PropiedadModel();
  }

  /**
   * Endpoint API para obtener el listado de propiedades en formato JSON.
   * Aplica filtros, paginación y control de acceso por permisos.
   * Retorna un objeto JSON con 'data' (las propiedades) y 'total' (para paginación).
   */
  public function apiGetAll()
  {
    $this->checkAuthAndPermissionApi(
      'propiedades.ver',
      'Acceso denegado a la API de propiedades.'
    );

    $filters = [];
    $filters['sucursal_id'] = $_GET['sucursal'] ?? '';
    $filters['administradora_id'] = $_GET['administradora'] ?? '';
    $filters['estado_id'] = $_GET['estado'] ?? '';
    $filters['municipio_id'] = $_GET['municipio'] ?? '';
    $filters['estatus_disponibilidad'] = $_GET['estatus_disponibilidad'] ?? '';

    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = (int) ($_GET['offset'] ?? 0);

    if (!$this->permissionManager->hasPermission('propiedades.ver.todo')) {
      $userSucursalId = $this->permissionManager->getSucursalId();
      $filters['sucursal_id'] = $userSucursalId;
    }

    try {
      $propiedades = $this->propiedadModel->getAll($filters, $limit, $offset);
      $totalPropiedades = $this->propiedadModel->getTotalPropiedades($filters);

      $this->jsonResponse([
        'status' => 'success',
        'data' => $propiedades,
        'total' => $totalPropiedades,
        'limit' => $limit,
        'offset' => $offset
      ], 200);
    } catch (\Exception $e) {
      error_log("Error en PropiedadController::apiGetAll(): " . $e->getMessage());

      $this->jsonResponse([
        'status' => 'error',
        'message' => 'Error interno del servidor al obtener propiedades.'
      ], 500);
    }
  }

  /**
   * Endpoint API para obtener una propiedad por su ID en formato JSON.
   * Aplica control de acceso por permisos.
   * Retorna un objeto JSON con 'data' (la propiedad) o 'message' (en caso de error).
   * 
   * @param int $id El ID de la propiedad a obtener.
   */
  public function apiGetById(int $id)
  {
    $this->checkAuthAndPermissionApi(
      'propiedades.ver',
      'Acceso denegado a la API de propiedades.'
    );

    try {
      $propiedad = $this->propiedadModel->getById($id);

      if ($propiedad) {
        $this->jsonResponse(['status' => 'success', 'data' => $propiedad], 200);
      } else {
        $this->jsonResponse(['status' => 'error', 'message' => 'Propiedad no encontrada.'], 404);
      }
    } catch (\Exception $e) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Error interno del servidor al obtener propiedad.'], 500);
    }
  }

  /**
   * Endpoint API para obtener un listado de propiedades disponibles para selección.
   * Este endpoint es utilizado en el proceso de creación de procesos de venta.
   */
  public function getAvailableProperties()
  {
    $this->checkAuthAndPermissionApi('procesos_venta.crear');

    try {
      $propiedades = $this->propiedadModel->findAllAvailableForSelect();

      $this->jsonResponse(['status' => 'success', 'data' => $propiedades]);
    } catch (\Exception $e) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener propiedades.'], 500);
    }
  }
}
