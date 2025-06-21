<?php
use FastRoute\RouteCollector;

return function (RouteCollector $r) {
  $webRoutes = require_once __DIR__ . '/../routes/web.php';
  $webRoutes($r);

  $r->addGroup('/api', function (RouteCollector $api) {
    $apiRoutes = require_once __DIR__ . '/../routes/api.php';
    $apiRoutes($api);
  });
};
