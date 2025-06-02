<?php
// if (isset($data) && is_array($data)) {
//   extract($data);
// }

$pageTitle = $pageTitle ?? 'Panel de Control';
$pageDescription = $pageDescription ?? '';


?>

<div class="page-header-area">
  <div class="page-title-group">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-description">Resumen general y métricas clave de su negocio.</p>
  </div>
  <div class="page-actions">
    <a href="#" class="btn btn-primary">
      <i class="icon-download"></i> Generar Reporte
    </a>
  </div>
</div>



<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
  <div class="card-header">
    <h6 class="card-title">Listado de <?php echo htmlspecialchars($pageTitle); ?></h6>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table" width="100%" cellspacing="0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Columna 1</th>
            <th>Columna 2</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $items = $items ?? [];

          if (empty($items)): ?>
            <tr>
              <td colspan="4" class="text-center">No hay datos para mostrar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td><?php echo htmlspecialchars($item['id']); ?></td>
                <td><?php echo htmlspecialchars($item['columna_1']); ?></td>
                <td><?php echo htmlspecialchars($item['columna_2']); ?></td>
                <td>
                  <a href="/modulo/editar/<?php echo $item['id']; ?>" class="btn btn-info btn-sm">Editar</a>
                  <button data-id="<?php echo $item['id']; ?>" class="btn btn-danger btn-sm delete-btn">Eliminar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>