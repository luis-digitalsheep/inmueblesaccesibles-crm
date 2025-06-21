<?php

use FastRoute\RouteCollector;

use App\Controllers\Api\AdministradoraApiController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\CarteraApiController;
use App\Controllers\Api\CatalogoApiController;
use App\Controllers\Api\DocumentoApiController;
use App\Controllers\Api\ProcesoVentaApiController;
use App\Controllers\Api\PropiedadApiController;
use App\Controllers\Api\PropiedadRevisionApiController;
use App\Controllers\Api\ProspectoApiController;
use App\Controllers\Api\SucursalApiController;
use App\Controllers\Api\UsuarioApiController;

/**
 * Define las rutas de la API de la aplicación.
 * @param RouteCollector $api
 */
return function (RouteCollector $api) {
    // --- Rutas de Autenticación ---
    $api->addRoute('POST', '/auth/login', [AuthApiController::class, 'login']);
    $api->addRoute('GET', '/auth/permissions', [AuthApiController::class, 'apiGetUserPermissions']);

    // --- API de Usuarios ---
    $api->addRoute('GET', '/usuarios', [UsuarioApiController::class, 'apiGetAll']);
    $api->addRoute('GET', '/usuarios/simple-list', [UsuarioApiController::class, 'apiGetSimpleList']);

    // --- API de Sucursales ---
    $api->addRoute('GET', '/sucursales', [SucursalApiController::class, 'apiGetAll']);

    // --- API de Propiedades ---
    $api->addRoute('GET', '/propiedades', [PropiedadApiController::class, 'apiGetAll']);
    $api->addRoute('GET', '/propiedades/{id:\d+}', [PropiedadApiController::class, 'apiGetById']);
    $api->addRoute('GET', '/propiedades/disponibles', [PropiedadApiController::class, 'getAvailableProperties']);

    // --- API de Validaciones de Cartera ---
    $api->addRoute('GET', '/validaciones-cartera', [PropiedadRevisionApiController::class, 'apiGetAll']);

    // --- API de Carteras ---
    $api->addRoute('POST', '/carteras/upload', [CarteraApiController::class, 'apiUploadCartera']);

    // --- API de Administradoras ---
    $api->addRoute('GET', '/administradoras', [AdministradoraApiController::class, 'apiGetAll']);

    // --- API de Prospectos ---
    $api->addRoute('GET', '/prospectos', [ProspectoApiController::class, 'apiGetAll']);
    $api->addRoute('GET', '/prospectos/{id:\d+}', [ProspectoApiController::class, 'apiGetById']);
    $api->addRoute('GET', '/prospectos/{id:\d+}/procesos-venta', [ProcesoVentaApiController::class, 'apiGetByProspecto']);
    $api->addRoute('POST', '/prospectos/{id:\d+}/procesos-venta', [ProcesoVentaApiController::class, 'apiCreateForProspecto']);

    $api->addRoute('PUT', '/prospectos/{id:\d+}/update-global-status', [ProspectoApiController::class, 'apiUpdateGlobalStatus']);

    $api->addRoute('POST', '/prospectos/{id:\d+}/seguimientos', [ProspectoApiController::class, 'apiAddSeguimiento']);
    $api->addRoute('GET', '/prospectos/{id:\d+}/seguimientos', [ProspectoApiController::class, 'apiGetByProspecto']);

    $api->addRoute('GET', '/prospectos/{id:\d+}/documentos', [DocumentoApiController::class, 'apiGetByProspecto']);
    $api->addRoute('POST', '/prospectos/{id:\d+}/documentos', [DocumentoApiController::class, 'apiStoreForProspecto']);

    // --- API de Catálogos ---
    $api->addRoute('GET', '/catalogos/municipios', [CatalogoApiController::class, 'apiGetMunicipiosByEstado']);
    $api->addRoute('GET', '/catalogos/sucursales', [CatalogoApiController::class, 'apiGetSucursales']);
    $api->addRoute('GET', '/catalogos/estatus-prospeccion', [CatalogoApiController::class, 'apiGetEstatusProspeccion']);
    $api->addRoute('GET', '/catalogos/estatus-global-prospecto', [CatalogoApiController::class, 'apiGetAllEstatusGlobalProspecto']);
};
