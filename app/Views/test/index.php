<?php
$pageTitle = $pageTitle ?? 'Propiedades';
$pageDescription = $pageDescription ?? 'Listado de propiedades disponibles en el sistema.';
$currentRoute = $currentRoute ?? '';
?>

<div class="page-header-area">
  <div class="page-title-group">
    <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
  </div>
</div>

<div id="alert-message-container" class="alert-container"></div>