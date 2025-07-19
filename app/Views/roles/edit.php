<div class="page-header-area">
    <div class="page-title-group">
        <h1 id="pageTitle" class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="page-description">Modifica el nombre y asigna permisos a este rol.</p>
    </div>
    <div class="page-actions">
        <a href="/roles" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<form id="rolEditForm" class="app-form">
    <div class="card shadow-default">
        <div class="card-header">
            <h6 class="card-title">Datos del Rol</h6>
        </div>
        <div class="card-body">
            <div class="form-group" style="max-width: 400px;">
                <label for="nombre" class="form-label">Nombre del Rol</label>
                <input type="text" id="nombre" name="nombre" class="form-input" required>
            </div>
        </div>
    </div>

    <div class="card shadow-default mt-4">
        <div class="card-header">
            <h6 class="card-title">Asignación de Permisos</h6>
        </div>
        <div class="card-body" id="permissions-container">
            <p class="text-muted text-center p-5">Cargando permisos...</p>
        </div>
    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="/roles" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<script>
    window.App = {
        PageData: {
            rolId: <?php echo $rolId; ?>
        }
    };
</script>
<script type="module" src="/assets/js/pages/roles/edit.js"></script>