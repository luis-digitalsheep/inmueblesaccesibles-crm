<?php

use FastRoute\RouteCollector;

use App\Controllers\Web\AuthController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\PropiedadController;
use App\Controllers\Web\PropiedadRevisionController;
use App\Controllers\Web\ProspectoController;
use App\Controllers\Web\TestController;

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

    $r->addRoute('GET', '/clientes', [TestController::class, 'index']);

    $r->addRoute('GET', '/validaciones-cartera', [PropiedadRevisionController::class, 'index']);
    $r->addRoute('GET', '/validaciones-cartera/validar/{id:\d+}', [PropiedadRevisionController::class, 'edit']);

    $r->addRoute('GET', '/validaciones-pagos', [TestController::class, 'index']);

    $r->addRoute('GET', '/solicitudes-contratos', [TestController::class, 'index']);

    $r->addRoute('GET', '/validaciones-contratos', [TestController::class, 'index']);

    $r->addRoute('GET', '/folios', [TestController::class, 'index']);

    $r->addRoute('GET', '/reporte-ejemplo', [TestController::class, 'index']);

    $r->addRoute('GET', '/usuarios', [TestController::class, 'index']);

    $r->addRoute('GET', '/roles', [TestController::class, 'index']);

    $r->addRoute('GET', '/administradoras', [TestController::class, 'index']);

    $r->addRoute('GET', '/sucursales', [TestController::class, 'index']);

    $r->addRoute('GET', '/permisos', [TestController::class, 'index']);
};
