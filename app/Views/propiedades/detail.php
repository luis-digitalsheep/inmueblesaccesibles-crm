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
    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
  </div>

  <div class="page-actions">
    <?php // if (PermissionManager::getInstance()->hasPermission('propiedades.editar') || (PermissionManager::getInstance()->hasPermission('propiedades.editar.propia_sucursal') && $propiedad['sucursal_id'] === PermissionManager::getInstance()->getSucursalId())): 
    ?>
    <?php // endif; 
    ?>
    <a href="/propiedades" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Volver al Listado
    </a>
  </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default mb-4 property-detail-card">
  <div class="card-header">
    <h6 class="card-title">Propiedad ID: <?php echo htmlspecialchars($propiedad['id'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($propiedad['direccion'] ?? 'Dirección no disponible'); ?></h6>
    <span class="detail-status-badge status-badge <?php echo $clase_estatus; ?>">
      <?php echo htmlspecialchars($estatus_display); ?>
    </span>
  </div>
  <div class="card-body">
    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-info-circle section-icon"></i> Datos Generales</h5>
      <div class="detail-grid">
        <div class="detail-item">
          <span class="detail-label">Número de Crédito:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['numero_credito'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Tiempo de Entrega:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['tiempo_entrega'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Sucursal:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['sucursal_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Administradora:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['administradora_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item full-width">
          <span class="detail-label">Dirección Completa:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['direccion'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Estado:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['estado_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Municipio:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['municipio_nombre'] ?? 'N/A'); ?></span>
        </div>
      </div>
    </div>

    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-dollar-sign section-icon"></i> Información Financiera</h5>
      <div class="detail-grid">

        <div class="detail-item">
          <span class="detail-label">Precio Remate:</span>
          <span class="detail-value price-value">$<?php echo number_format(htmlspecialchars($propiedad['precio_remate'] ?? 0), 2, ',', '.'); ?></span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Precio Venta:</span>
          <span class="detail-value price-value">$<?php echo number_format(htmlspecialchars($propiedad['precio_venta'] ?? 0), 2, ',', '.'); ?></span>
        </div>
      </div>
    </div>

    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-gavel section-icon"></i> Estatus Legal y Propietario</h5>
      <div class="detail-grid">
        <div class="detail-item">
          <span class="detail-label">Estatus Jurídico Actual:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['estatus_juridico_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Clasificación Legal (R1/R2):</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['clasificacion_legal_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Tipo de Propietario:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['tipo_propietario_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Documento de Venta:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['tipo_documento_venta_nombre'] ?? 'N/A'); ?></span>
        </div>
      </div>
    </div>

    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-map-marker-alt section-icon"></i> Ubicación y Mapa</h5>
      <div class="detail-grid">

        <div class="detail-item full-width">
          <span class="detail-label">Link Mapa:</span>
          <span class="detail-value"><a href="<?php echo htmlspecialchars($propiedad['mapa_url']); ?>" target="_blank" class="map-link">Ver en Google Maps</a></span>
        </div>

      </div>
    </div>

    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-comments section-icon"></i> Comentarios Internos</h5>
      <div class="detail-item full-width">
        <span class="detail-label">Comentarios para Administración:</span>
        <span class="detail-value comments-box"><?php echo htmlspecialchars($propiedad['comentarios_admin'] ?? 'Sin comentarios adicionales.'); ?></span>
      </div>
    </div>

    <div class="detail-section">
      <h5 class="section-title"><i class="fas fa-history section-icon"></i> Fechas Importantes</h5>
      <div class="detail-grid">
        <div class="detail-item">
          <span class="detail-label">Creado el:</span>
          <span class="detail-value"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($propiedad['created_at']))); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Última Actualización:</span>
          <span class="detail-value"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($propiedad['updated_at']))); ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<?php if (!empty($propiedad['cliente_id'])): ?>
  <div class="card shadow-default mb-4">
    <div class="card-header">
      <h6 class="card-title">Cliente Asociado a la Propiedad</h6>
    </div>
    <div class="card-body">
      <div class="detail-grid">
        <div class="detail-item">
          <span class="detail-label">Nombre Cliente:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['cliente_asociado_nombre'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Email Cliente:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['cliente_asociado_email'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Celular Cliente:</span>
          <span class="detail-value"><?php echo htmlspecialchars($propiedad['cliente_asociado_celular'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Ver Cliente:</span>
          <span class="detail-value">
            <a href="/clientes/ver/<?php echo htmlspecialchars($propiedad['cliente_id']); ?>" class="btn btn-sm btn-info">
              <i class="fas fa-user-friends"></i> Ir a Cliente
            </a>
          </span>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>