<?php
$pageTitle = $pageTitle ?? 'Propiedades';
$pageDescription = $pageDescription ?? 'Listado de propiedades disponibles en el sistema.';

$canCreatePropiedad = $canCreatePropiedad ?? false;
$canEditPropiedad = $canEditPropiedad ?? false;
$canDeletePropiedad = $canDeletePropiedad ?? false;
$canLoadCartera = $canLoadCartera ?? false;
$userSucursalId = $userSucursalId ?? null;

$sucursales = $sucursales ?? [];
$administradoras = $administradoras ?? [];
$estados = $estados ?? [];
$municipios = $municipios ?? [];
$estatusDisponibilidadEnum = ['Disponible', 'Apartada', 'Vendida', 'En Proceso de Cambio', 'Retirada'];

$currentFilters = $_GET ?? [];

?>

<div class="page-header-area">
  <div class="page-title-group">
    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
  </div>

  <div class="page-actions">
    <?php if ($canCreatePropiedad): ?>
      <a href="/propiedades/crear" class="btn btn-primary">
        <i class="icon-plus"></i> Nueva Propiedad
      </a>
    <?php endif; ?>
    <?php if ($canLoadCartera): ?>
      <a href="/propiedades/cargar-cartera" class="btn btn-secondary" id="loadCarteraButton">
        <i class="icon-upload"></i> Cargar Cartera
      </a>
    <?php endif; ?>
  </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default filter-card mb-4">
  <div class="card-header">
    <h6 class="card-title">Filtros de Propiedades</h6>
  </div>
  <div class="card-body">
    <form id="propertyFiltersForm" method="GET" action="/propiedades">
      <div class="filters-grid">
        <div class="form-group">
          <label for="filter_sucursal" class="form-label">Sucursal:</label>
          <select id="filter_sucursal" name="sucursal" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($sucursales as $sucursal): ?>
              <option value="<?php echo htmlspecialchars($sucursal['id']); ?>"
                <?php echo (isset($currentFilters['sucursal']) && $currentFilters['sucursal'] == $sucursal['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($sucursal['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_administradora" class="form-label">Administradora:</label>
          <select id="filter_administradora" name="administradora" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($administradoras as $administradora): ?>
              <option value="<?php echo htmlspecialchars($administradora['id']); ?>"
                <?php echo (isset($currentFilters['administradora']) && $currentFilters['administradora'] == $administradora['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($administradora['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_estado" class="form-label">Estado:</label>
          <select id="filter_estado" name="estado" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($estados as $estado): ?>
              <option value="<?php echo htmlspecialchars($estado['id']); ?>"
                <?php echo (isset($currentFilters['estado']) && $currentFilters['estado'] == $estado['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($estado['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_municipio" class="form-label">Municipio:</label>
          <select id="filter_municipio" name="municipio" class="form-select">
            <option value="">Todos</option>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_estatus_disponibilidad" class="form-label">Estatus Disponibilidad:</label>
          <select id="filter_estatus_disponibilidad" name="estatus_disponibilidad" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($estatusDisponibilidadEnum as $statusName): ?>
              <option value="<?php echo htmlspecialchars($statusName); ?>"
                <?php echo (isset($currentFilters['estatus_disponibilidad']) && $currentFilters['estatus_disponibilidad'] == $statusName) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($statusName); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <input type="hidden" name="limit" id="pagination_limit" value="<?php echo htmlspecialchars($currentFilters['limit'] ?? 10); ?>">
        <input type="hidden" name="offset" id="pagination_offset" value="<?php echo htmlspecialchars($currentFilters['offset'] ?? 0); ?>">

      </div>
      <div class="filters-actions">
        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
        <a href="/propiedades" class="btn btn-secondary">Limpiar Filtros</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-default">
  <div class="card-header">
    <h6 class="card-title">Propiedades Registradas</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Id</th>
            <th>Número de Crédito</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th>Municipio</th>
            <th>Precio Lista</th>
            <th>Precio Venta</th>
            <th>Estatus</th>
            <th>Sucursal</th>
            <th>Administradora</th>
            <th>Mapa</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="propiedadesTableBody">
          <tr>
            <td colspan="13" class="text-center">Cargando propiedades...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination-container">
      <button id="prevPageBtn" class="btn btn-secondary btn-sm" disabled>&larr; Anterior</button>
      <span id="paginationInfo">Página 1 de 1</span>
      <button id="nextPageBtn" class="btn btn-secondary btn-sm">&rarr; Siguiente</button>
    </div>
  </div>
</div>

<script type="module" src="/assets/js/propiedades.js"></script>