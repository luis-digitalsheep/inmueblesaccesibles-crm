<?php
$propiedad = $propiedad ?? [];
$pageTitle = $pageTitle ?? 'Detalle de Propiedad';
$pageDescription = $pageDescription ?? 'Información completa del inmueble.';

// Puedes obtener el nombre del usuario logueado para mostrarlo en los botones de acción si aplica
$currentUserName = \App\Services\Auth\PermissionManager::getInstance()->getUserName();

// Mapeo de estatus_disponibilidad a un color (para consistencia con listado)
$estatus_display = htmlspecialchars($propiedad['estatus_disponibilidad'] ?? 'Disponible');

$clase_estatus = '';
switch ($estatus_display) {
  case 'Apartada':
    $clase_estatus = 'status-apartada';
    break;
  case 'Vendida':
    $clase_estatus = 'status-vendida';
    break;
  case 'En Proceso de Cambio':
    $clase_estatus = 'status-en-proceso-cambio';
    break;
  case 'Retirada':
    $clase_estatus = 'status-retirada';
    break;
  default:
    $clase_estatus = 'status-disponible';
    break;
}
?>

<link rel="stylesheet" href="/assets/css/propiedades-detail.css">

<div class="page-header-area">
  <div class="page-title-group">
    <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
  </div>
  <div class="page-actions">
    <a href="/propiedades" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Volver al Listado
    </a>
  </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
  <div class="card-header card-header-tabs">
    <ul class="nav nav-tabs-custom" id="propiedadDetailTabs">
      <li class="nav-item"><button class="nav-link active" data-bs-target="#info-general">Info. General</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-target="#info-financiera">Info. Financiera</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-target="#info-legal">Info. Legal</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-target="#fotos-comentarios">Fotos y Comentarios</button></li>
      <?php if (!empty($propiedad['cliente_id'])): ?>
        <li class="nav-item"><button class="nav-link" data-bs-target="#cliente-asociado">Cliente Asociado</button></li>
      <?php endif; ?>
    </ul>
  </div>
  <div class="card-body">

    <div class="tab-content" id="propiedadDetailTabsContent">

      <div class="tab-pane active" id="info-general" role="tabpanel">
        <div id="info-general-container">
          <p class="text-muted text-center p-5"><i class="fas fa-spinner fa-spin"></i> Cargando información general...</p>
        </div>
      </div>

      <div class="tab-pane" id="info-financiera" role="tabpanel">
        <div id="info-financiera-container">
          <p class="text-muted text-center p-5"><i class="fas fa-spinner fa-spin"></i> Cargando información financiera...</p>
        </div>
      </div>

      <div class="tab-pane" id="fotos-comentarios" role="tabpanel">
        <div id="fotos-comentarios-container">
          <p class="text-muted text-center p-5"><i class="fas fa-spinner fa-spin"></i> Cargando fotos y comentarios...</p>
        </div>
      </div>

      <?php // En el controlador, verificar si la propiedad tiene un cliente_id para decidir si se renderiza esta pestaña
      // if ($tiene_cliente) { ... }
      ?>
      <div class="tab-pane" id="cliente-asociado" role="tabpanel">
        <div id="cliente-asociado-container">
          <p class="text-muted text-center p-5"><i class="fas fa-spinner fa-spin"></i> Cargando información del cliente...</p>
        </div>
      </div>
      <?php // } 
      ?>

    </div>

  </div>
</div>