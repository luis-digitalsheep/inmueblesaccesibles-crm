<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Gestión de Clientes</h1>
        <p class="page-description">Consulta la información y procesos de tus clientes.</p>
    </div>
    </div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default filter-card mb-4">
    <div class="card-header"><h6 class="card-title">Filtros de Búsqueda</h6></div>
    <div class="card-body">
        <form id="clienteFiltersForm">
            <div class="filters-grid column-3">
                <div class="form-group">
                    <label for="filter_nombre" class="form-label">Nombre / Email / Celular:</label>
                    <input type="text" name="nombre" id="filter_nombre" class="form-input" placeholder="Buscar cliente...">
                </div>
                <div class="form-group">
                    <label for="filter_usuario" class="form-label">Responsable:</label>
                    <select name="usuario_responsable_id" id="filter_usuario" class="form-select">
                        <option value="">Todos</option>
                        </select>
                </div>
                <div class="form-group">
                    <label for="filter_sucursal" class="form-label">Sucursal:</label>
                    <select name="sucursal_id" id="filter_sucursal" class="form-select">
                        <option value="">Todas</option>
                         </select>
                </div>
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                <button type="reset" class="btn btn-secondary">Limpiar</button>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-default">
    <div class="card-header"><h6 id="cardTitleClientes" class="card-title">Listado de Clientes</h6></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Responsable</th><th>Sucursal</th><th>Acciones</th></tr></thead>
                <tbody id="clientesTableBody"></tbody>
            </table>
        </div>
        <div id="pagination-container" class="pagination-container mt-3"></div>
    </div>
</div>

<script type="module" src="/assets/js/pages/clientes/list.js"></script>