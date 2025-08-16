<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use App\Models\ClienteModel;
use App\Models\ProcesoVentaModel;
use App\Models\ProspectoModel;
use App\Models\SeguimientoModel;

class ProspectoApiController extends ApiController
{
    private $prospectoModel;
    private $seguimientoModel;
    private $procesoVentaModel;
    private $clienteModel;


    public function __construct()
    {
        parent::__construct();
        $this->prospectoModel = new ProspectoModel();
        $this->seguimientoModel = new SeguimientoModel();
        $this->procesoVentaModel = new ProcesoVentaModel();
        $this->clienteModel = new ClienteModel();
    }

    /**
     * Obtiene todos los prospectos con paginación y filtros.
     * 
     * @return void
     */
    public function apiGetAll()
    {
        $this->checkAuthAndPermissionApi(
            'prospectos.ver',
            'Acceso denegado a la API de prospectos.'
        );

        $filters = [];
        $filters['nombre'] = $_GET['nombre'] ?? '';
        $filters['usuario_responsable_id'] = $_GET['usuario_responsable_id'] ?? '';
        $filters['sucursal_id'] = $_GET['sucursal_id'] ?? '';
        $filters['estatus_prospeccion_id'] = $_GET['estatus_prospeccion_id'] ?? '';

        $limit = (int) ($_GET['limit'] ?? 10);
        $offset = (int) ($_GET['offset'] ?? 0);

        if (!$this->permissionManager->hasPermission('prospectos.ver.todo')) {
            $userSucursalId = $this->permissionManager->getSucursalId();
            $filters['sucursal_id'] = $userSucursalId;
        }

        $prospectos = $this->prospectoModel->getAll($filters, $limit, $offset);
        $totalProspectos = $this->prospectoModel->getTotalProspectos($filters);

        $this->jsonResponse([
            'status' => 'success',
            'data' => $prospectos,
            'total' => $totalProspectos,
            'limit' => $limit,
            'offset' => $offset,
        ], 200);
    }

    /**
     * API: Devuelve un prospecto específico por ID.
     * @param int $id El ID del prospecto.
     */
    public function apiGetById(int $id)
    {
        $this->checkAuthAndPermissionApi('prospectos.ver');

        $prospecto = $this->prospectoModel->findById($id);

        if (!$prospecto) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Prospecto no encontrado.'], 404);
        }

        $this->jsonResponse([
            'status' => 'success',
            'data' => [
                'prospecto' => $prospecto,
            ]
        ]);
    }

    /**
     * API: Devuelve los seguimientos de un prospecto específico.
     * @param int $prospectoId
     */
    public function apiGetByProspecto(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('prospectos.ver');

        try {
            $seguimientos = $this->seguimientoModel->getByProspectoId($prospectoId);
            $this->jsonResponse(['status' => 'success', 'data' => $seguimientos]);
        } catch (\Exception $e) {
            error_log("Error en SeguimientoController::apiGetByProspecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener los seguimientos.'], 500);
        }
    }

    public function apiUpdateGlobalStatus(int $id)
    {
        $this->checkAuthAndPermissionApi('prospectos.workflow.gestionar');

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatusId = filter_var($input['estatus_global_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$newStatusId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'ID de estatus inválido.'], 400);
        }

        // TODO: Añadir lógica para verificar que el avance de estatus es válido (ej. no se puede retroceder)

        if ($this->prospectoModel->updateGlobalStatus($id, $newStatusId)) {
            $this->jsonResponse(['status' => 'success', 'message' => 'Estatus del prospecto actualizado.']);
        } else {
            $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo actualizar el estatus.'], 500);
        }
    }

    public function apiCreate()
    {
        $this->checkAuthAndPermissionApi('prospectos.crear');

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        $data = [
            'nombre' => trim($input['nombre'] ?? ''),
            'celular' => trim($input['celular'] ?? ''),
            'email' => filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'usuario_responsable_id' => $input['usuario_responsable_id'] ?? $this->permissionManager->getUserId(),
            'sucursal_id' => $input['sucursal_id'] ?? $this->permissionManager->getSucursalId(),
            'dial_code' => trim($input['dial_code'] ?? '52'),
            'pais_code' => trim($input['pais_code'] ?? 'MX'),
            'estatus_prospeccion_id' => filter_var($input['estatus_prospeccion_id'] ?? 1, FILTER_VALIDATE_INT) ?: 1
        ];

        error_log('$data: ' . json_encode($data));

        if (empty($data['nombre']) || empty($data['celular']) || empty($data['usuario_responsable_id']) || empty($data['sucursal_id'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Todos los campos son obligatorios.'], 400);
            return;
        }

        try {
            $prospectoId = $this->prospectoModel->create($data);

            $this->jsonResponse(['status' => 'success', 'data' => ['prospecto_id' => $prospectoId]], 201);
        } catch (\Exception $e) {
            error_log("Error en ProspectoController::apiCreate: " . $e->getMessage());

            $this->jsonResponse(['status' => 'error', 'message' => 'Error al crear el prospecto.'], 500);
            return;
        }
    }

    public function apiUpdate(int $id)
    {
        $this->checkAuthAndPermissionApi('prospectos.editar');

        $jsonInput = file_get_contents('php://input');
        $input = json_decode($jsonInput, true);

        error_log("ID: " . $id);

        try {
            $data = [
                'nombre' => trim($input['nombre'] ?? ''),
                'celular' => trim($input['celular'] ?? ''),
                'email' => filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL),
                'usuario_responsable_id' => $input['usuario_responsable_id'] ?? $this->permissionManager->getUserId(),
                'sucursal_id' => $input['sucursal_id'] ?? $this->permissionManager->getSucursalId(),
            ];

            $this->prospectoModel->update($id, $data);

            $this->jsonResponse(['status' => 'success', 'message' => 'Prospecto actualizado con éxito.'], 200);
        } catch (\Exception $e) {
            error_log("Error en ProspectoController::apiUpdate: " . $e->getMessage());

            $this->jsonResponse(['status' => 'error', 'message' => 'Error al actualizar el prospecto.'], 500);
        }
    }

    public function apiDestroy(int $id)
    {
        $this->checkAuthAndPermissionApi('prospectos.eliminar');

        try {
            $this->prospectoModel->delete($id);
            $this->jsonResponse(['status' => 'success', 'message' => 'Prospecto eliminado con éxito.'], 200);
        } catch (\Exception $e) {
            error_log("Error en ProspectoController::apiDestroy: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al eliminar el prospecto.'], 500);
        }
    }

    /**
     * API: Convierte un prospecto en cliente.
     * @param int $prospectoId
     */
    public function convertToCliente(int $prospectoId)
    {
        $this->checkAuthAndPermissionApi('clientes.convertir');
        $userId = $this->permissionManager->getUserId();

        try {
            $prospecto = $this->prospectoModel->findById($prospectoId);

            if (!$prospecto) throw new \Exception('Prospecto no encontrado.');
            if ($prospecto['cliente_id']) throw new \Exception('Este prospecto ya ha sido convertido a cliente.');

            // Crear el nuevo registro de cliente
            $prospecto['user_id'] = $userId;
            $clienteId = $this->clienteModel->createFromProspecto($prospecto);
            
            if (!$clienteId) throw new \Exception('No se pudo crear el registro del cliente.');

            // Actualizar el prospecto para enlazarlo al nuevo cliente
            $this->prospectoModel->linkToCliente($prospectoId, $clienteId);

            // Reasignar todos los procesos de venta del prospecto al nuevo cliente
            $this->procesoVentaModel->reassignProcesosToCliente($prospectoId, $clienteId);

            // Actualizar el estado global del prospecto a "Convertido"
            $this->prospectoModel->updateGlobalStatus($prospectoId, 4);

            $this->jsonResponse([
                'status' => 'success',
                'message' => 'Prospecto convertido a Cliente con éxito.',
                'data' => ['cliente_id' => $clienteId]
            ]);
        } catch (\Exception $e) {
            error_log("Error al convertir prospecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno al convertir el prospecto.'], 500);
        }
    }
}
