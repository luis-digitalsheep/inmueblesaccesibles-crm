<div class="page-header-area">
    <h1 class="page-title">Validación de Pagos</h1>
    <p class="page-description">Aprueba o rechaza los pagos de apartado registrados en el sistema.</p>
</div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Pagos Pendientes de Validación</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Prospecto</th>
                        <th>Propiedad</th>
                        <th>Monto</th>
                        <th>Fecha de Pago</th>
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

<script type="module" src="/assets/js/pages/validacionesPagos/list.js"></script>