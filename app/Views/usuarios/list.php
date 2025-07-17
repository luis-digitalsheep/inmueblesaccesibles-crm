<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Gestión de Usuarios</h1>
        <p class="page-description">Administra los usuarios del sistema, sus roles y permisos.</p>
    </div>
    <?php if ($permissions['canCreate']): 
    ?>
        <div class="page-actions">
            <button id="btnNuevoUsuario" class="btn btn-primary">Nuevo Usuario</button>
        </div>
    <?php endif; ?>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default filter-card mb-4">
    <div class="card-header">
        <h6 class="card-title">Filtros de Búsqueda</h6>
    </div>
    <div class="card-body">
        <form id="userFiltersForm">
            <div class="filters-grid column-3">
                <div class="form-group">
                    <label for="filter_nombre" class="form-label">Nombre o Email:</label>
                    <input type="text" name="nombre" id="filter_nombre" class="form-input" placeholder="Buscar usuario...">
                </div>
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <button type="reset" class="btn btn-secondary">Limpiar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitleUsuarios" class="card-title">Listado de Usuarios</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Sucursal</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usuariosTableBody">
                </tbody>
            </table>
        </div>
        <div id="pagination-container" class="pagination-container mt-3"></div>
    </div>
</div>

<script>
    window.App = window.App || {};
    window.App.PageData = {
        permissions: <?php echo json_encode($permissions ?? []); ?>
    };
</script>
<script type="module" src="/assets/js/pages/usuarios/list.js"></script>