<div class="page-header-area">
    <h1 class="page-title">Gestión de Sucursales</h1>
    <?php if ($permissions['canCreate']): ?>
        <div class="page-actions">
            <button id="btnNuevo" class="btn btn-primary">Nueva Sucursal</button>
        </div>
    <?php endif; ?>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Listado de Sucursales</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Abreviatura</th>
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
<script type="module" src="/assets/js/pages/sucursales/list.js"></script>