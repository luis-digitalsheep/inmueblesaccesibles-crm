    <!DOCTYPE html>

    <html lang="es">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" type="image/png" href="/assets/img/favicon.ico">
      <title><?php echo htmlspecialchars($pageTitle); ?></title>

      <!-- Librerias JS -->
      <script src="https://kit.fontawesome.com/dbec4d7656.js" crossorigin="anonymous"></script>
      <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

      <!-- Fonts -->
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

      <!-- Librerias CSS -->
      <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

      <!-- Estilos CSS -->
      <!-- <link href="/assets/css/main.css" rel="stylesheet">
      <link href="/assets/css/sidebar.css" rel="stylesheet">
      <link href="/assets/css/components/modal.css" rel="stylesheet">
      <link href="/assets/css/components/dropzone.css" rel="stylesheet"> -->
      <!-- <style src="/assets/css/propiedades-detail.css" type="text/css"></style> -->
      <link rel="stylesheet" href="/assets/css/main.css">

      <script type="module" src="/assets/js/utils/modal.js"></script>
      <script type="module" src="/assets/js/utils/alerts.js"></script>

      <script type="module" src="/assets/js/permissions.js"></script>

      <script type="module" src="/assets/js/main.js"></script>

    </head>

    <body data-current-route="<?php echo htmlspecialchars($currentRoute ?? ''); ?>">
      <div class="page-wrapper">
        <aside class="sidebar">
          <div class="sidebar-header">
            <a href="/" class="sidebar-brand">
              <img src="/assets/img/logo.png" alt="Logo Panel" class="brand-logo">
            </a>
          </div>
          <nav class="sidebar-nav">
            <ul>
              <li class="nav-item <?php echo ($currentRoute === '' || $currentRoute === 'index') ? 'active' : ''; ?>">
                <a href="/">
                  <i class="icon fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'agenda') ? 'active' : ''; ?>">
                <a href="/agenda">
                  <i class="icon fa-solid fa-calendar"></i><span>Mi agenda</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'notificaciones') ? 'active' : ''; ?>">
                <a href="/notificaciones">
                  <i class="icon fa-solid fa-bell"></i><span>Notificaciones</span>
                </a>
              </li>

              <li class="nav-divider"></li>

              <li class="sidebar-heading">Gestión General</li>
              <li class="nav-item <?php echo ($currentRoute === 'propiedades') ? 'active' : ''; ?>">
                <a href="/propiedades">
                  <i class="icon fa-solid fa-house"></i>
                  <span>Propiedades</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'prospectos') ? 'active' : ''; ?>">
                <a href="/prospectos">
                  <i class="icon fa-regular fa-user"></i>
                  <span>Prospectos</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'clientes') ? 'active' : ''; ?>">
                <a href="/clientes">
                  <i class="icon fa-solid fa-user"></i>
                  <span>Clientes</span>
                </a>
              </li>

              <li class="nav-item has-submenu <?php echo strpos($currentRoute, 'tareas') === 0 ? 'active' : ''; ?>">
                <a href="#" class="submenu-toggle"> <i class="icon fa-solid fa-list-check"></i></i> <span>Tareas</span>
                  <i class="icon-arrow-down submenu-arrow"></i> </a>
                <ul class="submenu-content">
                  <li class="nav-item <?php echo ($currentRoute === 'validaciones-cartera') ? 'active' : ''; ?>">
                    <a href="/validaciones-cartera"><i class="icon fa-solid fa-house-circle-check"></i>Validaciones cartera</a>
                  </li>
                  <li class="nav-item <?php echo ($currentRoute === 'validaciones-pagos') ? 'active' : ''; ?>">
                    <a href="/validaciones-pagos"><i class="icon fa-solid fa-money-bill"></i>Validaciones pagos</a>
                  </li>
                  <li class="nav-item <?php echo ($currentRoute === 'solicitudes-contratos') ? 'active' : ''; ?>">
                    <a href="/solicitudes-contratos"><i class="icon fa-solid fa-file-contract"></i>Solicitudes contratos</a>
                  </li>
                  <li class="nav-item <?php echo ($currentRoute === 'validaciones-contratos') ? 'active' : ''; ?>">
                    <a href="/validaciones-contratos"><i class="icon fa-solid fa-file-signature"></i>Validaciones contratos</a>
                  </li>
                </ul>
              </li>

              <li class="nav-divider"></li>

              <li class="sidebar-heading">Reportes</li>
              <li class="nav-item <?php echo ($currentRoute === 'folios') ? 'active' : ''; ?>">
                <a href="/folios">
                  <i class="icon fa-solid fa-stamp"></i>
                  <span>Folios de apartado</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'reporte-ejemplo') ? 'active' : ''; ?>">
                <a href="/reporte-ejemplo">
                  <i class="icon fa-solid fa-chart-simple"></i>
                  <span>Reporte ejemplo</span>
                </a>
              </li>

              <li class="nav-divider"></li>

              <li class="sidebar-heading">Configuración</li>
              <li class="nav-item <?php echo ($currentRoute === 'usuarios') ? 'active' : ''; ?>">
                <a href="/usuarios">
                  <i class="icon fa-solid fa-user-gear"></i>
                  <span>Usuarios</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'administradoras') ? 'active' : ''; ?>">
                <a href="/administradoras">
                  <i class="icon fa-solid fa-building-columns"></i>
                  <span>Administradoras</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'sucursales') ? 'active' : ''; ?>">
                <a href="/sucursales">
                  <i class="icon fa-solid fa-building"></i>
                  <span>Sucursales</span>
                </a>
              </li>
              <li class="nav-item <?php echo ($currentRoute === 'permisos') ? 'active' : ''; ?>">
                <a href="/permisos">
                  <i class="icon fa-solid fa-users-gear"></i>
                  <span>Roles y permisos</span>
                </a>
              </li>
            </ul>
          </nav>
        </aside>

        <main class="content-wrapper">
          <header class="topbar">
            <nav class="topbar-nav">
              <div class="topbar-left">
                <button class="sidebar-toggle-btn" aria-label="Toggle Sidebar">
                  <i class="fa-solid fa-bars"></i>
                </button>
              </div>
              <div class="topbar-right">
                <div class="user-dropdown">
                  <a href="#" class="user-info" role="button" aria-expanded="false">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?></span>
                    <img class="user-avatar" src="/assets/img/default-avatar.png" alt="Avatar">
                  </a>
                  <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a href="/logout" class="dropdown-item">Cerrar Sesión</a>
                  </div>
                </div>
              </div>
            </nav>
          </header>

          <section class="page-content">
            <div class="container-fluid">