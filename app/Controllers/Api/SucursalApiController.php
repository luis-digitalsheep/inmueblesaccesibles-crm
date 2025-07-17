<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\CatalogoModel;
use App\Models\SucursalModel;

class SucursalApiController extends ApiController
{
  private $catalogoModel;
  private $sucursalModel;

  public function __construct()
  {
    parent::__construct();
    $this->sucursalModel = new SucursalModel();
    $this->catalogoModel = new CatalogoModel();
  }

  public function apiGetAll()
  {
    try {
      $sucursales = $this->catalogoModel->getAll('cat_sucursales');

      $this->jsonResponse([
        'status' => 'success',
        'data' => $sucursales
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
    $this->checkAuthAndPermissionApi('sucursales.ver');

    $items = $this->sucursalModel->getAll([], (int)($_GET['limit'] ?? 15), (int)($_GET['offset'] ?? 0));
    $total = $this->sucursalModel->getTotal();
    
    $this->jsonResponse(['status' => 'success', 'data' => $items, 'total' => $total]);
  }

  public function show(int $id)
  {
    $this->checkAuthAndPermissionApi('sucursales.editar');

    $sucursal = $this->sucursalModel->findById($id);
    
    if (!$sucursal) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Sucursal no encontrada.'], 404);
      return;
    }
    
    $this->jsonResponse(['status' => 'success', 'data' => $sucursal]);
  }

  public function store()
  {
    $this->checkAuthAndPermissionApi('sucursales.crear');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['nombre']) || empty($input['abreviatura'])) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Nombre y abreviatura son requeridos.'], 422);
      return;
    }
    
    $id = $this->sucursalModel->create($input);
    
    if ($id) {
      $this->jsonResponse(['status' => 'success', 'message' => 'Sucursal creada con éxito.', 'data' => $this->sucursalModel->findById($id)], 201);
    } else {
      $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo crear la sucursal.'], 500);
    }
  }

  public function update(int $id)
  {
    $this->checkAuthAndPermissionApi('sucursales.editar');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($this->sucursalModel->update($id, $input)) {
      $this->jsonResponse(['status' => 'success', 'message' => 'Sucursal actualizada con éxito.', 'data' => $this->sucursalModel->findById($id)]);
    } else {
      $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar la sucursal.'], 500);
    }
  }

  public function destroy(int $id)
  {
    $this->checkAuthAndPermissionApi('sucursales.eliminar');
    
    if ($this->sucursalModel->delete($id)) {
      $this->jsonResponse(['status' => 'success', 'message' => 'Sucursal eliminada con éxito.']);
    } else {
      $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo eliminar la sucursal. Es posible que esté en uso.'], 500);
    }
  }
}
