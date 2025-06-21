<?php

namespace App\Controllers;

use App\Services\Auth\PermissionManager;

abstract class Controller
{
    protected $permissionManager;

    public function __construct()
    {
        $this->permissionManager = PermissionManager::getInstance();
    }
}
