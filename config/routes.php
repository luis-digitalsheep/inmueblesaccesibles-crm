<?php

return function (FastRoute\RouteCollector $r) {
  // Web
  $r->addRoute('GET', '/login', ['AuthController', 'showLoginForm']);
  $r->addRoute('POST', '/login', ['AuthController', 'login']);
  $r->addRoute('GET', '/logout', ['AuthController', 'logout']);

  $r->addRoute('GET', '/', ['DashboardController', 'index']);
  $r->addRoute('GET', '/index', ['DashboardController', 'index']);

  $r->addRoute('GET', '/agenda', ['TestController', 'index']);

  $r->addRoute('GET', '/notificaciones', ['TestController', 'index']);

  $r->addRoute('GET', '/propiedades', ['PropiedadController', 'index']);
  $r->addRoute('GET', '/propiedades/ver/{id:\d+}', ['PropiedadController', 'show']);

  $r->addRoute('GET', '/prospectos', ['TestController', 'index']);

  $r->addRoute('GET', '/clientes', ['TestController', 'index']);

  $r->addRoute('GET', '/validaciones-cartera', ['ValidacionCarteraController', 'index']);

  $r->addRoute('GET', '/validaciones-pagos', ['TestController', 'index']);

  $r->addRoute('GET', '/solicitudes-contratos', ['TestController', 'index']);

  $r->addRoute('GET', '/validaciones-contratos', ['TestController', 'index']);

  $r->addRoute('GET', '/folios', ['TestController', 'index']);

  $r->addRoute('GET', '/reporte-ejemplo', ['TestController', 'index']);

  $r->addRoute('GET', '/usuarios', ['TestController', 'index']);

  $r->addRoute('GET', '/roles', ['TestController', 'index']);

  $r->addRoute('GET', '/administradoras', ['TestController', 'index']);

  $r->addRoute('GET', '/sucursales', ['TestController', 'index']);

  $r->addRoute('GET', '/permisos', ['TestController', 'index']);

  // API
  $r->addRoute('GET', '/api/auth/permissions', ['AuthController', 'apiGetUserPermissions']);
  $r->addRoute('GET', '/api/sucursales', ['SucursalController', 'apiGetAll']);
  $r->addRoute('GET', '/api/propiedades', ['PropiedadController', 'apiGetAll']);
  $r->addRoute('GET', '/api/validaciones-cartera', ['ValidacionCarteraController', 'apiGetAll']);
  $r->addRoute('GET', '/api/catalogos/municipios', ['PropiedadController', 'apiGetMunicipiosByEstado']);
  $r->addRoute('POST', '/api/propiedades/upload-cartera', ['PropiedadController', 'apiUploadCartera']);
  $r->addRoute('GET', '/api/administradoras', ['AdministradoraController', 'apiGetAll']);
};
