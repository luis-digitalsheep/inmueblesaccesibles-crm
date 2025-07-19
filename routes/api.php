<?php

use FastRoute\RouteCollector;

use App\Controllers\Api\AdministradoraApiController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\CarteraApiController;
use App\Controllers\Api\CatalogoApiController;
use App\Controllers\Api\ClienteApiController;
use App\Controllers\Api\DocumentoApiController;
use App\Controllers\Api\PermisoApiController;
use App\Controllers\Api\ProcesoVentaApiController;
use App\Controllers\Api\PropiedadApiController;
use App\Controllers\Api\PropiedadRevisionApiController;
use App\Controllers\Api\ProspectoApiController;
use App\Controllers\Api\RolApiController;
use App\Controllers\Api\SeguimientoApiController;
use App\Controllers\Api\SucursalApiController;
use App\Controllers\Api\UsuarioApiController;
use App\Controllers\Api\UploadApiController;

/**
 * Define las rutas de la API de la aplicación.
 * @param RouteCollector $api
 */
return function (RouteCollector $api) {
    // --- Rutas de Autenticación ---
    $api->addRoute('POST', '/auth/login', [AuthApiController::class, 'login']);
    $api->addRoute('GET', '/auth/permissions', [AuthApiController::class, 'apiGetUserPermissions']);

    // --- API de Usuarios ---
    $api->addRoute('GET', '/usuarios', [UsuarioApiController::class, 'index']);
    $api->addRoute('POST', '/usuarios', [UsuarioApiController::class, 'store']);
    $api->addRoute('GET', '/usuarios/{id:\d+}', [UsuarioApiController::class, 'show']);
    $api->addRoute('PUT', '/usuarios/{id:\d+}', [UsuarioApiController::class, 'update']);
    $api->addRoute('DELETE', '/usuarios/{id:\d+}', [UsuarioApiController::class, 'destroy']);
    $api->addRoute('GET', '/usuarios/simple-list', [UsuarioApiController::class, 'apiGetSimpleList']);

    // --- API de Sucursales ---
    $api->addRoute('GET', '/sucursales', [SucursalApiController::class, 'index']);
    $api->addRoute('POST', '/sucursales', [SucursalApiController::class, 'store']);
    $api->addRoute('GET', '/sucursales/{id:\d+}', [SucursalApiController::class, 'show']);
    $api->addRoute('PUT', '/sucursales/{id:\d+}', [SucursalApiController::class, 'update']);
    $api->addRoute('DELETE', '/sucursales/{id:\d+}', [SucursalApiController::class, 'destroy']);

    // --- API de Propiedades ---
    $api->addRoute('GET', '/propiedades', [PropiedadApiController::class, 'apiGetAll']);
    $api->addRoute('GET', '/propiedades/{id:\d+}', [PropiedadApiController::class, 'apiGetById']);
    $api->addRoute('GET', '/propiedades/disponibles', [PropiedadApiController::class, 'getAvailableProperties']);

    // --- API de Validaciones de Cartera ---
    $api->addRoute('GET', '/validacion-cartera', [PropiedadRevisionApiController::class, 'apiGetAll']);
    $api->addRoute('GET', '/validacion-cartera/{id:\d+}', [PropiedadRevisionApiController::class, 'apiGetById']);
    $api->addRoute('PUT', '/validacion-cartera/{id:\d+}', [PropiedadRevisionApiController::class, 'apiUpdate']);
    $api->addRoute('POST', '/validacion-cartera/{id:\d+}/fotos', [PropiedadRevisionApiController::class, 'uploadFotos']);

    // --- API de Carteras ---
    $api->addRoute('POST', '/carteras/upload', [CarteraApiController::class, 'apiUploadCartera']);

    // --- API de Administradoras ---
    $api->addRoute('GET', '/administradoras', [AdministradoraApiController::class, 'index']);
    $api->addRoute('POST', '/administradoras', [AdministradoraApiController::class, 'store']);
    $api->addRoute('GET', '/administradoras/{id:\d+}', [AdministradoraApiController::class, 'show']);
    $api->addRoute('PUT', '/administradoras/{id:\d+}', [AdministradoraApiController::class, 'update']);
    $api->addRoute('DELETE', '/administradoras/{id:\d+}', [AdministradoraApiController::class, 'destroy']);
    // $api->addRoute('GET', '/administradoras', [AdministradoraApiController::class, 'apiGetAll']);

    // --- API de Prospectos ---
    $api->addRoute('GET', '/prospectos', [ProspectoApiController::class, 'apiGetAll']);
    $api->addRoute('POST', '/prospectos', [ProspectoApiController::class, 'apiCreate']);
    $api->addRoute('GET', '/prospectos/{id:\d+}', [ProspectoApiController::class, 'apiGetById']);
    $api->addRoute('PUT', '/prospectos/{id:\d+}', [ProspectoApiController::class, 'apiUpdate']);

    $api->addRoute('GET', '/prospectos/{id:\d+}/procesos-venta', [ProcesoVentaApiController::class, 'apiGetByProspecto']);
    $api->addRoute('POST', '/prospectos/{id:\d+}/procesos-venta', [ProcesoVentaApiController::class, 'apiCreateForProspecto']);

    $api->addRoute('PUT', '/prospectos/{id:\d+}/update-global-status', [ProspectoApiController::class, 'apiUpdateGlobalStatus']);

    $api->addRoute('POST', '/prospectos/{id:\d+}/seguimientos', [ProspectoApiController::class, 'apiAddSeguimiento']);
    $api->addRoute('GET', '/prospectos/{id:\d+}/seguimientos', [ProspectoApiController::class, 'apiGetByProspecto']);

    $api->addRoute('GET', '/prospectos/{id:\d+}/documentos', [DocumentoApiController::class, 'apiGetByProspecto']);
    $api->addRoute('POST', '/prospectos/{id:\d+}/documentos', [DocumentoApiController::class, 'apiStoreForProspecto']);
    $api->addRoute('POST', '/prospectos/{prospectoId:\d+}/convertir-a-cliente', [ProspectoApiController::class, 'convertToCliente']);

    // --- API de Procesos de Venta ---
    $api->addRoute('GET', '/procesos-venta/{id:\d+}', [ProcesoVentaApiController::class, 'show']);
    $api->addRoute('GET', '/procesos-venta/{id:\d+}/seguimientos', [SeguimientoApiController::class, 'getByProcesoVenta']);
    $api->addRoute('GET', '/procesos-venta/{id:\d+}/documentos', [DocumentoApiController::class, 'getByProcesoVenta']);
    $api->addRoute('PUT', '/procesos-venta/{id}/update-status', [ProcesoVentaApiController::class, 'updateStatus']);

    // --- API de Folios ---
    $api->addRoute('POST', '/procesos-venta/{id:\d+}/generar-folio', [ProcesoVentaApiController::class, 'generarFolio']);

    // --- API de Comprobante de apartado ---
    $api->addRoute('POST', '/procesos-venta/{id}/subir-comprobante', [ProcesoVentaApiController::class, 'subirComprobante']);

    // --- API de Clientes ---
    $api->addRoute('GET', '/clientes', [ClienteApiController::class, 'index']);
    $api->addRoute('GET', '/clientes/{id:\d+}', [ClienteApiController::class, 'show']);
    $api->addRoute('GET', '/clientes/{id:\d+}/procesos-venta', [ProcesoVentaApiController::class, 'indexByCliente']);
    $api->addRoute('GET', '/clientes/{id:\d+}/seguimientos', [SeguimientoApiController::class, 'indexByCliente']);
    $api->addRoute('GET', '/clientes/{id:\d+}/documentos', [DocumentoApiController::class, 'indexByCliente']);
    $api->addRoute('PUT', '/clientes/{id:\d+}', [ClienteApiController::class, 'update']);

    // --- API de Permisos ---
    $api->addRoute('GET', '/permisos', [PermisoApiController::class, 'index']);
    $api->addRoute('POST', '/permisos', [PermisoApiController::class, 'store']);
    $api->addRoute('GET', '/permisos/{id:\d+}', [PermisoApiController::class, 'show']);
    $api->addRoute('PUT', '/permisos/{id:\d+}', [PermisoApiController::class, 'update']);
    $api->addRoute('DELETE', '/permisos/{id:\d+}', [PermisoApiController::class, 'destroy']);

    $api->addRoute('GET', '/permisos/all-grouped', [PermisoApiController::class, 'getAllGrouped']);

    // --- API de Roles ---
    $api->addRoute('GET', '/roles', [RolApiController::class, 'index']);
    $api->addRoute('POST', '/roles', [RolApiController::class, 'store']);
    $api->addRoute('GET', '/roles/{id:\d+}', [RolApiController::class, 'show']);
    $api->addRoute('PUT', '/roles/{id:\d+}', [RolApiController::class, 'update']);
    $api->addRoute('DELETE', '/roles/{id:\d+}', [RolApiController::class, 'destroy']);

    // --- API de Catálogos ---
    $api->addRoute('GET', '/catalogos/estados', [CatalogoApiController::class, 'apiGetEstados']);
    $api->addRoute('GET', '/catalogos/municipios', [CatalogoApiController::class, 'apiGetMunicipiosByEstado']);
    $api->addRoute('GET', '/catalogos/sucursales', [CatalogoApiController::class, 'apiGetSucursales']);
    $api->addRoute('GET', '/catalogos/estatus-prospeccion', [CatalogoApiController::class, 'apiGetEstatusProspeccion']);
    $api->addRoute('GET', '/catalogos/estatus-global-prospecto', [CatalogoApiController::class, 'apiGetAllEstatusGlobalProspecto']);
    $api->addRoute('GET', '/catalogos/administradoras', [CatalogoApiController::class, 'apiGetAdministradoras']);
    $api->addRoute('GET', '/catalogos/roles', [CatalogoApiController::class, 'apiGetRoles']);

    // --- API para Subidas de Archivos ---
    $api->addRoute('POST', '/uploads/temp-photo', [UploadApiController::class, 'handleTempUpload']);
};
