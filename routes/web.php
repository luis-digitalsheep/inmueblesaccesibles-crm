<?php


use FastRoute\RouteCollector;

use App\Controllers\Web\AdministradoraController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\PropiedadController;
use App\Controllers\Web\PropiedadRevisionController;
use App\Controllers\Web\ProspectoController;
use App\Controllers\Web\TestController;
use App\Controllers\Web\DocumentoController;
use App\Controllers\Web\ProcesoVentaController;
use App\Controllers\Web\FolioApartadoController;
use App\Controllers\Web\ClienteController;
use App\Controllers\Web\PermisoController;
use App\Controllers\Web\RolController;
use App\Controllers\Web\SucursalController;
use App\Controllers\Web\UsuarioController;
use App\Controllers\Web\ValidacionPagoController;
use App\Controllers\Web\SolicitudContratoController;
use App\Controllers\Web\ValidacionContratoController;

/**
 * Define las rutas web (de navegación) de la aplicación.
 * @param RouteCollector $r
 */
return function (RouteCollector $r) {
    $r->addRoute('GET', '/login', [AuthController::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [AuthController::class, 'login']);
    $r->addRoute('GET', '/logout', [AuthController::class, 'logout']);

    $r->addRoute('GET', '/', [DashboardController::class, 'index']);
    $r->addRoute('GET', '/index', [DashboardController::class, 'index']);

    $r->addRoute('GET', '/agenda', [TestController::class, 'index']);

    $r->addRoute('GET', '/notificaciones', [TestController::class, 'index']);

    $r->addRoute('GET', '/propiedades', [PropiedadController::class, 'index']);
    $r->addRoute('GET', '/propiedades/ver/{id:\d+}', [PropiedadController::class, 'show']);

    $r->addRoute('GET', '/prospectos', [ProspectoController::class, 'index']);
    $r->addRoute('GET', '/prospectos/ver/{id:\d+}', [ProspectoController::class, 'show']);

    $r->addRoute('GET', '/procesos-venta/ver/{id:\d+}', [ProcesoVentaController::class, 'show']);

    $r->addRoute('GET', '/clientes', [ClienteController::class, 'index']);
    $r->addRoute('GET', '/clientes/ver/{id:\d+}', [ClienteController::class, 'show']);

    $r->addRoute('GET', '/validaciones-cartera', [PropiedadRevisionController::class, 'index']);
    $r->addRoute('GET', '/validacion-cartera/validar/{id:\d+}', [PropiedadRevisionController::class, 'edit']);

    $r->addRoute('GET', '/validaciones-pagos', [ValidacionPagoController::class, 'index']);

    $r->addRoute('GET', '/solicitudes-contratos', [SolicitudContratoController::class, 'index']);

    $r->addRoute('GET', '/validaciones-contratos', [ValidacionContratoController::class, 'index']);

    $r->addRoute('GET', '/folios', [TestController::class, 'index']);
    $r->addRoute('GET', '/folios-apartado/descargar/{id:\d+}', [FolioApartadoController::class, 'descargar']);

    $r->addRoute('GET', '/reporte-ejemplo', [TestController::class, 'index']);

    $r->addRoute('GET', '/usuarios', [UsuarioController::class, 'index']);

    $r->addRoute('GET', '/administradoras', [AdministradoraController::class, 'index']);

    $r->addRoute('GET', '/sucursales', [SucursalController::class, 'index']);

    $r->addRoute('GET', '/permisos', [PermisoController::class, 'index']);

    $r->addRoute('GET', '/roles', [RolController::class, 'index']);
    $r->addRoute('GET', '/roles/editar/{id:\d+}', [RolController::class, 'edit']);

    $r->addRoute('GET', '/documentos/descargar/{id:\d+}', [DocumentoController::class, 'descargar']);
};
