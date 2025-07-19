<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Gestión de Permisos</h1>
        <p class="page-description">Crea y administra los permisos de la aplicación.</p>
    </div>

    <?php if ($permissions['canCreate']): ?>
        <div class="page-actions">
            <button id="btnNuevo" class="btn btn-primary">Nuevo Permiso</button>
        </div>
    <?php endif; ?>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Listado de Permisos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Clave</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div id="pagination-container" class="pagination-container mt-3"></div>
    </div>
</div>

<script>
    window.App = {
        PageData: {
            permissions: <?php echo json_encode($permissions ?? []); ?>
        }
    };
</script>
<script type="module" src="/assets/js/pages/permisos/list.js"></script>