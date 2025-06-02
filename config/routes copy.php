<?php

return [
  'GET /login' => ['AuthController', 'showLoginForm'],
  'POST /login' => ['AuthController', 'login'],
  'GET /logout' => ['AuthController', 'logout'],

  'GET /api/auth/permissions' => ['AuthController', 'apiGetUserPermissions'],

  'GET /' => ['DashboardController', 'index'],
  'GET /index' => ['DashboardController', 'index'],

  'GET /agenda' => ['TestController', 'index'],

  'GET /notificaciones' => ['TestController', 'index'],

  'GET /propiedades' => ['PropiedadController', 'index'],
  'GET /propiedades/ver/:id' => ['PropiedadController', 'show'],
  'GET /api/propiedades' => ['PropiedadController', 'apiGetAll'],
  'GET /api/catalogos/municipios' => ['PropiedadController', 'apiGetMunicipiosByEstado'],

  'GET /prospectos' => ['TestController', 'index'],

  'GET /clientes' => ['TestController', 'index'],

  'GET /validaciones-cartera' => ['TestController', 'index'],

  'GET /validaciones-pagos' => ['TestController', 'index'],

  'GET /solicitudes-contratos' => ['TestController', 'index'],

  'GET /validaciones-contratos' => ['TestController', 'index'],

  'GET /folios' => ['TestController', 'index'],

  'GET /reporte-ejemplo' => ['TestController', 'index'],

  'GET /usuarios' => ['TestController', 'index'],

  'GET /administradoras' => ['TestController', 'index'],

  'GET /sucursales' => ['TestController', 'index'],

  'GET /permisos' => ['TestController', 'index'],
];
