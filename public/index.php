<?php
require_once __DIR__ . '/../bootstrap.php';

use FastRoute\Dispatcher;
use App\Services\Auth\PermissionManager;

$dispatcher = FastRoute\simpleDispatcher(require BASE_PATH . '/config/routes.php');

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

$currentRoute = trim($uri, '/');

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
  case Dispatcher::NOT_FOUND:
    http_response_code(404);
    include BASE_PATH . '/app/Views/errors/404.php';
    exit();
    break;

  case Dispatcher::METHOD_NOT_ALLOWED:
    $allowedMethods = $routeInfo[1];

    http_response_code(405);
    header('Allow: ' . implode(', ', $allowedMethods));
    echo "<h1>405 - Método No Permitido</h1>";
    exit();
    break;

  case Dispatcher::FOUND:
    $handler = $routeInfo[1]; // El handler (ej. ['PropiedadController', 'index'])
    $vars = $routeInfo[2];   // Los parámetros de la URL (ej. ['id' => 123] de /propiedades/ver/123)

    // Extraer el nombre del controlador y el método
    list($controllerName, $methodName) = $handler;
    $controllerClass = "App\\Controllers\\" . $controllerName;

    if (!class_exists($controllerClass)) {
      http_response_code(500);
      include BASE_PATH . '/app/Views/errors/500.php';
      error_log("Error 500: Controlador {$controllerName} no encontrado.");
      exit();
    }
    if (!method_exists($controllerClass, $methodName)) {
      http_response_code(500);
      include BASE_PATH . '/app/Views/errors/500.php';
      error_log("Error 500: Método {$methodName} no existe en el controlador {$controllerName}.");
      exit();
    }

    $controller = new $controllerClass();

    // --- Lógica de Verificación de Autenticación y Permisos ---
    $publicRoutes = [
      'GET /login',
      'POST /login',
      'GET /logout'
    ];

    // Reconstruimos la clave para el check de rutas públicas
    $currentRouteKeyForPublicCheck = strtoupper($httpMethod) . ' ' . $uri;

    // Si la ruta NO es pública y el usuario no está logueado
    if (!in_array($currentRouteKeyForPublicCheck, $publicRoutes) && !PermissionManager::getInstance()->getUserId()) {
      header('Location: /login');
      exit();
    }
    // -----------------------------------------------------------

    // Llamar al método del controlador, pasando los parámetros de la URL y $currentRoute
    // Los parámetros de la URL (FastRoute $vars) vienen primero.
    // Luego, el $currentRoute para el resaltado del menú.
    call_user_func_array([$controller, $methodName], array_values($vars + ['currentRoute' => $currentRoute]));
    break;
}
