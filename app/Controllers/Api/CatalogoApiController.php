<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

use App\Models\CatalogoModel;

use App\Services\Auth\PermissionManager;

/**
 * @property-read PermissionManager $permissionManager
 */
class CatalogoApiController extends ApiController
{
    private $catalogoModel;

    public function __construct()
    {
        parent::__construct();
        $this->catalogoModel = new CatalogoModel();
    }

    /**
     * Endpoint API para obtener todos los estados.
     */
    public function apiGetEstados()
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        try {
            $estados = $this->catalogoModel->getAll('cat_estados');
            $this->jsonResponse([
                'status' => 'success',
                'data' => $estados,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetEstados(): " . $e->getMessage());

            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener estados.'
            ], 500);
        }
    }

    /**
     * Endpoint API para obtener municipios filtrados por un estado_id.
     */
    public function apiGetMunicipiosByEstado()
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        $estadoId = (int) ($_GET['estado_id'] ?? 0);

        if ($estadoId <= 0) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'ID de estado inválido.'
            ], 400);
        }

        try {
            $municipios = $this->catalogoModel->getMunicipiosByEstado($estadoId);
            $this->jsonResponse([
                'status' => 'success',
                'data' => $municipios,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en PropiedadController::apiGetMunicipiosByEstado(): " . $e->getMessage());

            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener municipios.'
            ], 500);
        }
    }

    /**
     * Endpoint API para obtener todas las sucursales.
     */
    public function apiGetSucursales()
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        try {
            $sucursales = $this->catalogoModel->getAll('cat_sucursales');
            $this->jsonResponse([
                'status' => 'success',
                'data' => $sucursales,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetSucursales(): " . $e->getMessage());

            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener sucursales.'
            ], 500);
        }
    }

    /**
     * Endpoint API para obtener todos los estatus de prospección.
     */
    public function apiGetEstatusProspeccion()
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        try {
            $estatus = $this->catalogoModel->getAll('cat_estatus_prospeccion');
            $this->jsonResponse([
                'status' => 'success',
                'data' => $estatus,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetEstatusProspeccion(): " . $e->getMessage());

            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener estatus de prospección.'
            ], 500);
        }
    }

    /**
     * API: Devuelve el catálogo de estatus globales de prospecto.
     */
    public function apiGetAllEstatusGlobalProspecto()
    {
        // Cualquier usuario autenticado que pueda ver prospectos debería poder ver este catálogo.
        $this->checkAuthAndPermissionApi('prospectos.ver');

        try {
            // Usamos el método genérico, pidiendo que ordene por la columna 'orden'.
            $estatus = $this->catalogoModel->getAll('cat_estatus_global_prospecto', 'orden');
            $this->jsonResponse(['status' => 'success', 'data' => $estatus]);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetAllEstatusGlobalProspecto: " . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Error al obtener el catálogo de estatus.'], 500);
        }
    }

    /**
     * Endpoint API para obtener todas las administradoras.
     * Si no tiene permiso para ver los nombres reales verán abreviaturas.
     */
    public function apiGetAdministradoras()
    {
        $ver_nombres = $this->permissionManager->hasPermission('administradoras.ver_nombres');

        try {
            $administradoras = $this->catalogoModel->getAll('cat_administradoras');

            $administradoras = array_map(function ($administradora) use ($ver_nombres) {

                if (!$ver_nombres) {
                    $administradora['nombre'] = $administradora['abreviatura'];
                }

                return $administradora;
            }, $administradoras);

            $this->jsonResponse([
                'status' => 'success',
                'data' => $administradoras,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetAdministradoras(): " . $e->getMessage());
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener administradores.'
            ], 500);
        }
    }

    /**
     * Endpoint API para obtener todos los roles.
     */
    public function apiGetRoles()
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        try {
            $roles = $this->catalogoModel->getAll('cat_roles');

            $this->jsonResponse([
                'status' => 'success',
                'data' => $roles,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetAdministradoras(): " . $e->getMessage());
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener administradores.'
            ], 500);
        }
    }

    public function apiGetResultadosSeguimiento() 
    {
        $this->checkAuthAndPermissionApi(
            'catalogos.ver',
            'Acceso denegado a la API de catálogos.'
        );

        try {
            $resultados = $this->catalogoModel->getAll('cat_resultados_seguimiento');

            $this->jsonResponse([
                'status' => 'success',
                'data' => $resultados,
            ], 200);
        } catch (\Exception $e) {
            error_log("Error en CatalogoController::apiGetResultadosSeguimiento(): " . $e->getMessage());
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Error interno del servidor al obtener resultados de seguimiento.'
            ], 500);
        }
    }
}
