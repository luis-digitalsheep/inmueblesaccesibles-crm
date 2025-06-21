<div class="page-header-area">
  <div class="page-title-group">
    <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle . ': ' . $propiedadDireccion); ?></h1>
    <p id="pageDescription" class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
  </div>

  <div class="page-actions">
    <a href="/propiedades" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
  </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
  <div class="card-header card-header-tabs">
    <ul class="nav nav-tabs-custom" id="propiedadDetailTabs">
      <li class="nav-item"><button class="nav-link active" data-bs-target="#info-general">Info. General y Ubicación</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-target="#info-financiera">Info. Financiera y Legal</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-target="#fotos-comentarios">Fotos y Comentarios</button></li>
      <li class="nav-item" id="cliente-asociado-tab" style="display: none;"><button class="nav-link" data-bs-target="#cliente-asociado">Cliente Asociado</button></li>
    </ul>
  </div>

  <div class="card-body">
    <div class="tab-content" id="propiedadDetailTabsContent">

      <div class="tab-pane active" id="info-general">
        <div id="info-general-container">
          <p class="text-muted p-5 text-center">Cargando...</p>
        </div>
      </div>

      <div class="tab-pane" id="info-financiera">
        <div id="info-financiera-container">
          <p class="text-muted p-5 text-center">Cargando...</p>
        </div>
      </div>

      <div class="tab-pane" id="fotos-comentarios">
        <div id="fotos-comentarios-container">
          <p class="text-muted p-5 text-center">Cargando...</p>
        </div>
      </div>

      <div class="tab-pane" id="cliente-asociado">
        <div id="cliente-asociado-container">
          <p class="text-muted p-5 text-center">Cargando...</p>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  window.App = window.App || {};
  window.App.PageData = {
    propiedadId: <?php echo $propiedadId; ?>,
    permissions: <?php echo json_encode($permissions ?? []); ?>
  };
</script>

<script type="module" src="/assets/js/pages/propiedades/show.js"></script>