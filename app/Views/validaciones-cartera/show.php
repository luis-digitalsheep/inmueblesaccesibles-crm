<div class="page-header-area">
    <div class="page-title-group">
        <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p id="pageDescription" class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
    </div>
    <div class="page-actions">
        <a href="/validaciones-cartera" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header card-header-tabs">
        <ul class="nav nav-tabs-custom" id="validacionTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-target="#tab-general">Info. General</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#tab-ubicacion">Ubicación</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#tab-detalles">Detalles y Finanzas</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-target="#tab-fotos">Fotografías</button></li>
        </ul>
    </div>
    <div class="card-body">
        <form id="formValidacion" class="app-form" novalidate>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-general">
                    <div id="container-general">
                        <p class="text-muted p-5 text-center">Cargando...</p>
                    </div>
                </div>
                <div class="tab-pane" id="tab-ubicacion">
                    <div id="container-ubicacion">
                        <p class="text-muted p-5 text-center">Cargando...</p>
                    </div>
                </div>
                <div class="tab-pane" id="tab-detalles">
                    <div id="container-detalles">
                        <p class="text-muted p-5 text-center">Cargando...</p>
                    </div>
                </div>
                <div class="tab-pane" id="tab-fotos">
                    <div id="container-fotos">
                        <p class="text-muted p-5 text-center">Cargando...</p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end mt-4">
                <button type="submit" class="btn btn-primary">Validar y Crear Propiedad</button>
                <a href="/validaciones-cartera" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    window.App = {
        PageData: {
            revisionId: <?php echo $propiedadRevisionID; ?>,
            permissions: <?php echo json_encode($permissions ?? []); ?>
        }
    };
</script>
<script type="module" src="/assets/js/pages/validaciones-cartera/show.js"></script>