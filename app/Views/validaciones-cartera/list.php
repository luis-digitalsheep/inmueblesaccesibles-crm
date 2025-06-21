<?php
$pageTitle = $pageTitle ?? 'Validaciones de Cartera';
$pageDescription = $pageDescription ?? 'Listado de propiedades pendientes de validación.';

$sucursales = $sucursales ?? [];
$administradoras = $administradoras ?? [];
$carteras = $carteras ?? [];

$currentFilters = $_GET ?? [];

$canValidate = $canValidate ?? false;

// $estatus = ['Pendiente', 'Validado', 'Rechazado'];

?>

<div class="page-header-area">
  <div class="page-title-group">
    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
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
            <option value="" selected>Todas</option>
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
            <option value="" selected>Todas</option>
            <?php foreach ($administradoras as $administradora): ?>
              <option value="<?php echo htmlspecialchars($administradora['id']); ?>"
                <?php echo (isset($currentFilters['administradora']) && $currentFilters['administradora'] == $administradora['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($administradora['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_cartera" class="form-label">Carteras:</label>
          <select id="filter_cartera" name="cartera" class="form-select">
            <option value="" selected>Todas</option>
            <?php foreach ($carteras as $cartera): ?>
              <option value="<?php echo htmlspecialchars($cartera['id']); ?>"
                <?php echo (isset($currentFilters['cartera']) && $currentFilters['cartera'] == $cartera['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cartera['codigo']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="filter_estatus" class="form-label">Estatus:</label>
          <select id="filter_estatus" name="estatus" class="form-select">
            <option value="">Todos</option>
            <option value="Pendiente" selected>Pendiente</option>
            <option value="Validado">Validado</option>
            <option value="Rechazado">Rechazado</option>
          </select>
        </div>

        <input type="hidden" name="limit" id="pagination_limit" value="<?php echo htmlspecialchars($currentFilters['limit'] ?? 10); ?>">
        <input type="hidden" name="offset" id="pagination_offset" value="<?php echo htmlspecialchars($currentFilters['offset'] ?? 0); ?>">

      </div>

      <div class="filters-actions">
        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
        <a href="/validaciones-cartera" class="btn btn-secondary">Limpiar Filtros</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-default">
  <div class="card-header">
    <h6 class="card-title">Propiedades por validar</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>Id</th>
            <th>Nombre de cartera</th>
            <th>Número de Crédito</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th>Municipio</th>
            <th>Precio Lista</th>
            <th>Estatus</th>
            <th>Sucursal</th>
            <th>Administradora</th>
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

<script type="module" src="/assets/js/validaciones-cartera.js"></script>