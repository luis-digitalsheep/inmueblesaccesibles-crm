<div class="page-header-area">
    <div class="page-title-group">
        <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p id="pageDescription" class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
    </div>
    <div class="page-actions">
        <a href="/clientes" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header card-header-tabs">
        <ul class="nav nav-tabs-custom" id="clienteDetailTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-target="#info-general">Información General</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#procesos-venta">Procesos de Venta</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#seguimiento">Historial de Seguimiento</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#documentos">Documentos</button></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="clienteDetailTabsContent">

            <div class="tab-pane active" id="info-general">
                <div id="info-general-container">
                    <p class="text-muted p-5 text-center">Cargando...</p>
                </div>
            </div>

            <div class="tab-pane" id="procesos-venta">
                <div id="procesos-venta-container">
                    <p class="text-muted p-5 text-center">Cargando...</p>
                </div>
            </div>

            <div class="tab-pane" id="seguimiento">
                <div id="seguimiento-container">
                    <p class="text-muted p-5 text-center">Cargando...</p>
                </div>
            </div>

            <div class="tab-pane" id="documentos">
                <div id="documentos-container">
                    <p class="text-muted p-5 text-center">Cargando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.App = {
        PageData: {
            clienteId: <?php echo $clienteId; ?>,
            permissions: <?php echo json_encode($permissions ?? []); ?>
        }
    };
</script>
<script type="module" src="/assets/js/pages/clientes/show.js"></script>