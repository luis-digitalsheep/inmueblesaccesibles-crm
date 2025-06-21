<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\ProspectoModel;
use App\Models\SeguimientoModel;

class ProspectoApiController extends ApiController
{
    private $prospectoModel;
    private $seguimientoModel;

    public function __construct()
    {
        parent::__construct();
        $this->prospectoModel = new ProspectoModel();
        $this->seguimientoModel = new SeguimientoModel();
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


    /**
     * API: Añade una nueva entrada de seguimiento a un prospecto.
     * POST /api/prospectos/{id}/seguimientos
     * @param int $id El ID del prospecto.
     */
    public function apiAddSeguimiento(int $id)
    {
        $this->checkAuthAndPermissionApi('prospectos.seguimiento.crear');

        $prospecto = $this->prospectoModel->findById($id);

        if (!$prospecto) {
            $this->jsonResponse(['status' => 'error', 'message' => 'El prospecto no existe.'], 404);
        }
        // TODO: Añadir lógica de permisos si solo el responsable puede añadir seguimientos


        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty(trim($input['comentarios'] ?? ''))) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Datos inválidos. El comentario es requerido.'], 400);
        }

        $data = [
            'prospecto_id' => $id,
            'usuario_registra_id' => $this->permissionManager->getUserId(),
            'tipo_interaccion' => $input['tipo_interaccion'] ?? 'nota',
            'comentarios' => trim($input['comentarios'])
        ];

        // 5. Llamar al modelo para crear el registro
        try {
            $nuevoSeguimientoId = $this->seguimientoModel->createForProspecto($data);
            if ($nuevoSeguimientoId) {
                // Opcional: podrías devolver el seguimiento recién creado si la UI lo necesita
                $this->jsonResponse(['status' => 'success', 'message' => 'Seguimiento añadido con éxito.'], 201);
            } else {
                $this->jsonResponse(['status' => 'error', 'message' => 'No se pudo guardar el seguimiento.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en ProspectoController::apiAddSeguimiento: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error interno del servidor.'], 500);
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

    // public function store()
    // {
    //     $this->checkPermission('prospectos.crear');
    //     // Validación de datos de $_POST
    //     $data = [
    //         'nombre' => trim($_POST['nombre'] ?? ''),
    //         'celular' => trim($_POST['celular'] ?? ''),
    //         'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
    //         'usuario_responsable_id' => filter_var($_POST['usuario_responsable_id'] ?? null, FILTER_VALIDATE_INT) ?: $this->permissionManager->getUserId(), // Asignar al usuario actual por defecto
    //         'sucursal_id' => filter_var($_POST['sucursal_id'] ?? null, FILTER_VALIDATE_INT) ?: $this->permissionManager->getSucursalId(), // Sucursal del usuario actual por defecto
    //         'dial_code' => trim($_POST['dial_code'] ?? '52'), // Default MX
    //         'pais_code' => trim($_POST['pais_code'] ?? 'MX'), // Default MX
    //         'estatus_prospeccion_id' => filter_var($_POST['estatus_prospeccion_id'] ?? 1, FILTER_VALIDATE_INT) ?: 1, // Default 'Contacto Inicial'
    //     ];

    //     if (empty($data['nombre']) || empty($data['celular']) || empty($data['usuario_responsable_id']) || empty($data['sucursal_id'])) {
    //         $_SESSION['flash_error_message'] = 'Nombre, celular, responsable y sucursal son obligatorios.';

    //         $this->redirect('/prospectos/crear');
    //         return;
    //     }

    //     $prospectoId = $this->prospectoModel->create($data);

    //     if ($prospectoId) {
    //         $_SESSION['flash_success_message'] = 'Prospecto creado con éxito. ID: ' . $prospectoId;
    //         $this->redirect('/prospectos'); // O a /prospectos/ver/{$prospectoId}
    //     } else {
    //         $_SESSION['flash_error_message'] = 'Error al crear el prospecto.';
    //         $this->redirect('/prospectos/crear');
    //     }
    // }

    // /**
    //  * Muestra el formulario para editar un prospecto existente.
    //  */
    // public function edit(int $id, string $currentRoute = '')
    // {
    //     $this->checkPermission('prospectos.editar');
    //     $prospecto = $this->prospectoModel->findById($id);
    //     if (!$prospecto) {
    //         $this->renderErrorPage(404, 'Prospecto no encontrado.');
    //         return;
    //     }
    //     // Podrías añadir un chequeo de permiso para editar solo prospectos de su sucursal si no es admin/gerente global
    //     // if (!$this->permissionManager->hasPermission('prospectos.editar.todos') && $prospecto['sucursal_id'] != $this->permissionManager->getSucursalId()){ ... }

    //     $this->_getFormDependencies($data);
    //     $data['pageTitle'] = 'Editar Prospecto: ' . htmlspecialchars($prospecto['nombre']);
    //     $data['pageDescription'] = 'Actualiza la información del prospecto.';
    //     $data['formAction'] = '/prospectos/actualizar/' . $id;
    //     $data['prospecto'] = $prospecto;
    //     $data['currentRoute'] = $currentRoute;

    //     $this->render('prospectos/edit', $data, $currentRoute);
    // }

    // /**
    //  * Actualiza un prospecto en la base de datos.
    //  */
    // public function update(int $id)
    // {
    //     $this->checkPermission('prospectos.editar');
    //     $prospecto = $this->prospectoModel->findById($id); // Para verificar que existe
    //     if (!$prospecto) {
    //         $_SESSION['flash_error_message'] = 'Prospecto no encontrado.';
    //         $this->redirect('/prospectos');
    //         return;
    //     }
    //     // TODO: Lógica de permisos de edición más granular si es necesario

    //     $data = [ /* Similar a store(), pero tomando datos de $_POST */
    //         'nombre' => trim($_POST['nombre'] ?? ''),
    //         // ... todos los campos ...
    //         'estatus_prospeccion_id' => filter_var($_POST['estatus_prospeccion_id'] ?? null, FILTER_VALIDATE_INT) ?: $prospecto['estatus_prospeccion_id'],
    //         'cliente_id' => filter_var($_POST['cliente_id'] ?? $prospecto['cliente_id'], FILTER_VALIDATE_INT) ?: null, // Si se convierte a cliente
    //     ];
    //     // TODO: Validación robusta

    //     if ($this->prospectoModel->update($id, $data)) {
    //         $_SESSION['flash_success_message'] = 'Prospecto actualizado con éxito.';
    //         $this->redirect('/prospectos'); // O a /prospectos/editar/{$id}
    //     } else {
    //         $_SESSION['flash_error_message'] = 'Error al actualizar el prospecto.';
    //         $this->redirect('/prospectos/editar/' . $id);
    //     }
    // }

    // /**
    //  * Elimina un prospecto.
    //  */
    // public function delete(int $id)
    // {
    //     $this->checkPermission('prospectos.eliminar');
    //     // TODO: Lógica de permisos de eliminación más granular si es necesario

    //     if ($this->prospectoModel->delete($id)) {
    //         $_SESSION['flash_success_message'] = 'Prospecto eliminado con éxito.';
    //     } else {
    //         $_SESSION['flash_error_message'] = 'Error al eliminar el prospecto.';
    //     }
    //     $this->redirect('/prospectos');
    // }

    // /**
    //  * Método privado para cargar dependencias comunes para los formularios create/edit.
    //  */
    // private function _getFormDependencies(array &$data)
    // {
    //     // TODO: Implementar $this->usuarioModel->getVendedoresActivos() o similar
    //     $data['usuariosResponsables'] = [];
    //     $data['sucursales'] = $this->catalogoModel->getAll('cat_sucursales');
    //     $data['estatusProspeccion'] = $this->catalogoModel->getAll('cat_estatus_prospeccion', 'orden');
    //     $data['paisesConCodigo'] = [['codigo' => 'MX', 'dial' => '52', 'nombre' => 'México']];
    // }
}
