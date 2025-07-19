<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Gestión de Roles</h1>
        <p class="page-description">Crea y administra los roles de los usuarios y los permisos asociados a cada uno.</p>
    </div>
    <?php if ($permissions['canCreate']): ?>
        <div class="page-actions">
            <button id="btnNuevoRol" class="btn btn-primary">Nuevo Rol</button>
        </div>
    <?php endif; ?>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Listado de Roles del Sistema</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Rol</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.App = window.App || {};
    window.App.PageData = {
        permissions: <?php echo json_encode($permissions ?? []); ?>
    };
</script>

<script type="module" src="/assets/js/pages/roles/list.js"></script>