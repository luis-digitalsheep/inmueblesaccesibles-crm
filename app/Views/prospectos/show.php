<div class="page-header-area">
    <div class="page-title-group">
        <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle . ': ' . $prospectoNombre); ?></h1>
        <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
    </div>
    <div class="page-actions">
        <a href="/prospectos" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</div>

<div class="alert-container"></div>

<div class="card shadow-default mb-4">
    <div class="card-header">
        <h6 class="card-title">Estatus Global del Prospecto</h6>
    </div>
    <div class="card-body" id="global-status-container">
        <p class="text-muted">Cargando estatus del prospecto...</p>
    </div>
</div>


<div class="card shadow-default">
    <div class="card-header card-header-tabs">
        <ul class="nav nav-tabs-custom" id="prospectoDetailTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#procesos-venta" type="button">Procesos de Venta</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#info-general" type="button">Información General</button></li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content" id="prospectoDetailTabsContent">

            <div class="tab-pane fade show active" id="procesos-venta" role="tabpanel">
                <div id="procesos-venta-container">
                    <p class="text-muted">Cargando procesos de venta...</p>
                </div>
            </div>

            <div class="tab-pane fade" id="info-general" role="tabpanel">
                <div id="info-general-container">
                    <p class="text-muted">Cargando información del prospecto...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.App = window.App || {};
    window.App.PageData = {
        prospectoId: <?php echo $prospectoId; ?>,
        permissions: <?php echo json_encode($permissions ?? []); ?>
    };
</script>
<script type="module" src="/assets/js/pages/prospectos/show.js"></script>