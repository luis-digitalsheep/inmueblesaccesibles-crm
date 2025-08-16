<div class="page-header-area">
    <div class="page-title-group">
        <h1 id="pageTitle" class="page-title">Cargando Proceso de Venta...</h1>
        <p id="pageDescription" class="page-description">Gestiona el seguimiento y avance de esta oportunidad.</p>
    </div>
    <div class="page-actions">
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo htmlspecialchars($back_label); ?>
        </a>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header card-header-tabs">
        <ul class="nav nav-tabs-custom" id="procesoDetailTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-target="#tab-proceso">Estatus del Proceso</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#tab-seguimiento">Seguimiento de Ventas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#tab-documentos">Documentos</button></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="tab-proceso">
                <div id="proceso-workflow-container">
                    <p class="text-muted p-5 text-center">Cargando flujo de trabajo...</p>
                </div>
            </div>
            <div class="tab-pane" id="tab-seguimiento">
                <div id="proceso-seguimiento-container">
                    <p class="text-muted p-5 text-center">Cargando seguimiento...</p>
                </div>
            </div>
            <div class="tab-pane" id="tab-documentos">
                <div id="proceso-documentos-container">
                    <p class="text-muted p-5 text-center">Cargando documentos...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.App = {
        PageData: {
            procesoVentaId: <?php echo $procesoVentaId; ?>,
            permissions: <?php echo json_encode($permissions ?? []); ?>
        }
    };
</script>

<script type="module" src="/assets/js/pages/procesosVenta/show.js"></script>