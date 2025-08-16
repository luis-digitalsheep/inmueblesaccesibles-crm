<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title">Validación de Contratos</h1>
        <p class="page-description">Revisa y aprueba los borradores de contrato pendientes asignados a tu rol.</p>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<div class="card shadow-default">
    <div class="card-header">
        <h6 id="cardTitle" class="card-title">Contratos Pendientes de mi Validación</h6>
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
                        <th>Solicitado Por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.App = window.App || {};
    window.App.PageData = {
        permissions: <?php echo json_encode($permissions ?? []); ?>,
        userData: <?php echo json_encode($userData ?? []); ?>
    };
</script>

<script type="module" src="/assets/js/pages/validacionesContratos/list.js"></script>