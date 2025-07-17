<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\AdministradoraModel;
use App\Models\CatalogoModel;

class AdministradoraApiController extends ApiController
{
  private $catalogoModel;
  private $administradorasModel;

  public function __construct()
  {
    parent::__construct();
    $this->catalogoModel = new CatalogoModel();
    $this->administradorasModel = new AdministradoraModel();
  }

  public function apiGetAll()
  {
    try {
      $administradoras = $this->catalogoModel->getAll('cat_administradoras');

      $this->jsonResponse([
        'status' => 'success',
        'data' => $administradoras
      ], 200);
    } catch (\Exception $e) {
      $this->jsonResponse([
        'status' => 'error',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function index()
  {
    $this->checkAuthAndPermissionApi('administradoras.ver');

    $filtros = [];

    $limit = (int)($_GET['limit'] ?? 15);
    $offset = (int)($_GET['offset'] ?? 0);

    $items = $this->administradorasModel->getAll($filtros, $limit, $offset);
    $total = $this->administradorasModel->getTotal($filtros);

    $this->jsonResponse(['status' => 'success', 'data' => $items, 'total' => $total]);
  }

  public function show(int $id)
  {
    $this->checkAuthAndPermissionApi('administradoras.ver');

    $administradora = $this->administradorasModel->findById($id);

    if (!$administradora) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Administradora no encontrada.'], 404);
      return;
    }

    $this->jsonResponse(['status' => 'success', 'data' => $administradora]);
  }

  public function store()
  {
    $this->checkAuthAndPermissionApi('administradoras.crear');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['nombre']) && !empty($input['abreviatura'])) {
      $administradoraId = $this->administradorasModel->create($input);

      if (!$administradoraId) {
        $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear la administradora.'], 500);
      }

      $administradora = $this->administradorasModel->findById($administradoraId);
      $this->jsonResponse(['status' => 'success', 'message' => 'Administradora creada con éxito.', 'data' => $administradora], 201);
      return;
    }

    $this->jsonResponse(['status' => 'error', 'message' => 'Nombre y abreviatura son requeridos.'], 422);
  }

  public function update(int $id)
  {
    $this->checkAuthAndPermissionApi('administradoras.editar');

    $input = json_decode(file_get_contents('php://input'), true);

    if ($this->administradorasModel->update($id, $input)) {
      $administradora = $this->administradorasModel->findById($id);
      $this->jsonResponse(['status' => 'success', 'message' => 'Administradora actualizada con éxito.', 'data' => $administradora]);
    } else {
      $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar la administradora.'], 500);
    }
  }

  public function destroy(int $id)
  {
    $this->checkAuthAndPermissionApi('administradoras.eliminar');

    if ($this->administradorasModel->delete($id)) {
      $this->jsonResponse(['status' => 'success', 'message' => 'Administradora eliminada con éxito.']);
    } else {
      $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo eliminar la administradora.'], 500);
    }
  }
}
