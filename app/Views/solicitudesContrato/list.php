<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Solicitudes de Contrato</h1>
        <p class="page-description">Gestiona las solicitudes pendientes para la generación de contratos de compra-venta.</p>
    </div>
</div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Cola de Trabajo: Solicitudes Pendientes</h6>
    </div>


    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Fecha de Solicitud</th>
                        <th>Prospecto/Cliente</th>
                        <th>Propiedad</th>
                        <th>Solicitado Por (Vendedor)</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="solicitudesTableBody">
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

    window.App.PageData.currentUserId = <?php echo $currentUserId; ?>;
</script>

<script type="module" src="/assets/js/pages/solicitudesContrato/list.js"></script>