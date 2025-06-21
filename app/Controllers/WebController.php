<?php

namespace App\Controllers;

abstract class WebController extends Controller
{
    protected $permissionManager;
    protected $currentUser;

    public function __construct()
    {
        parent::__construct();
    }

    protected function render(string $viewName, array $data = [], string $currentRoute = '')
    {
        $data['pageTitle'] = $data['pageTitle'] ?? 'CRM Inmuebles';
        $data['pageDescription'] = $data['pageDescription'] ?? '';
        $data['currentRoute'] = $currentRoute;

        extract($data);

        include BASE_PATH . '/app/Views/layouts/header.php';
        include BASE_PATH . '/app/Views/' . $viewName . '.php';
        include BASE_PATH . '/app/Views/layouts/footer.php';
    }

    protected function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    protected function checkPermission(string $permissionName, ?string $redirectTo = null, string $errorMessage = 'Acceso denegado.')
    {
        if (!$this->permissionManager->hasPermission($permissionName)) {
            if ($redirectTo) {
                $this->redirect($redirectTo);
            } else {
                $this->renderErrorPage(403, $errorMessage);
            }
        }
    }

    protected function renderErrorPage(int $statusCode, string $message = '')
    {
        http_response_code($statusCode);

        if ($statusCode === 403) {
            include BASE_PATH . '/app/Views/errors/403.php';
        } elseif ($statusCode === 404) {
            include BASE_PATH . '/app/Views/errors/404.php';
        } else {
            echo "<h1>Error {$statusCode}</h1><p>{$message}</p>";
        }
        exit;
    }
}
