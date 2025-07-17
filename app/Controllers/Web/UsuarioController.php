<?php
namespace App\Controllers\Web;

use App\Controllers\WebController;

class UsuarioController extends WebController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Muestra la vista de listado de usuarios (el "cascarón" dinámico).
     */
    public function index(string $currentRoute = '')
    {
        $this->checkPermission('usuarios.ver');

        $data = [
            'pageTitle' => 'Gestión de Usuarios',
            'pageDescription' => 'Administra los usuarios del sistema, sus roles y permisos.',
            'currentRoute' => $currentRoute,
 
            'permissions' => [
                'canCreate' => $this->permissionManager->hasPermission('usuarios.crear'),
                'canUpdate' => $this->permissionManager->hasPermission('usuarios.editar'),
                'canDelete' => $this->permissionManager->hasPermission('usuarios.eliminar'),
            ]
        ];

        $this->render('usuarios/list', $data, $currentRoute);
    }
}