<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\ClienteModel;

class ClienteApiController extends ApiController
{
    private $clienteModel;

    public function __construct()
    {
        parent::__construct();
        $this->clienteModel = new ClienteModel();
    }

    /**
     * API: Devuelve una lista de clientes.
     */
    public function index()
    {
        $this->checkAuthAndPermissionApi('clientes.ver');

        $filters = $_GET; // Simplificado, idealmente sanitizar
        // TODO: Aplicar filtro de sucursal por permisos

        $limit = (int)($_GET['limit'] ?? 15);
        $offset = (int)($_GET['offset'] ?? 0);

        $clientes = $this->clienteModel->getAll($filters, $limit, $offset);
        $totalClientes = $this->clienteModel->getTotalClientes($filters);

        $this->jsonResponse([
            'status' => 'success',
            'data' => $clientes,
            'total' => $totalClientes,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * API: Devuelve los datos de un cliente específico.
     * Ruta: GET /api/clientes/{id}
     * @param int $id El ID del cliente.
     */
    public function show(int $id)
    {
        $this->checkAuthAndPermissionApi('clientes.ver');

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Cliente no encontrado.'], 404);
            return;
        }

        // TODO: Añadir lógica de permisos
        
        $this->jsonResponse(['status' => 'success', 'data' => $cliente]);
    }
}
