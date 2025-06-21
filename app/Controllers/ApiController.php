<?php

namespace App\Controllers;

abstract class ApiController  extends Controller
{
    protected $permissionManager;

    public function __construct()
    {
        parent::__construct();
    }

    protected function jsonResponse(array $responseData, int $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($responseData);
        exit;
    }

    /**
     * Verifica la autenticación y un permiso específico para un endpoint API.
     * Si falla la autenticación, envía un error JSON 401.
     * Si falla el permiso, envía un error JSON 403.
     * Si todo está bien, la ejecución continúa.
     *
     * @param string $permissionName El nombre del permiso a verificar.
     * @param string $permErrorMessage Mensaje para el error de permiso denegado (403).
     * @param string $authErrorMessage Mensaje para el error de no autenticado (401).
     */
    protected function checkAuthAndPermissionApi(
        string $permissionName,
        string $permErrorMessage = 'No tienes los permisos necesarios para realizar esta acción.',
        string $authErrorMessage = 'Acceso no autorizado. Debes iniciar sesión.'
    ) {
        if (!$this->permissionManager->getUserId()) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $authErrorMessage
            ], 401);
        }

        if (!$this->permissionManager->hasPermission($permissionName)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $permErrorMessage
            ], 403);
        }
    }
}
