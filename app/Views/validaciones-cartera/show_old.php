<div class="page-header-area">
    <div class="page-title-group">
        <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="page-description"><?php echo htmlspecialchars($pageDescription); ?></p>
    </div>
    <div class="page-actions">
        <a href="/validaciones-cartera" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
    </div>
</div>

<div id="alert-message-container" class="alert-container"></div>

<form id="validacionForm" action="<?php echo htmlspecialchars($formAction); ?>" method="POST" class="app-form">
    <div class="card shadow-default">
        <div class="card-header card-header-tabs">
            <ul class="nav nav-tabs-custom" id="validacionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">Información General</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ubicacion-detalles-tab" data-bs-toggle="tab" data-bs-target="#ubicacion-detalles" type="button" role="tab" aria-controls="ubicacion-detalles" aria-selected="false">Ubicación y Detalles</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="financiero-legal-tab" data-bs-toggle="tab" data-bs-target="#financiero-legal" type="button" role="tab" aria-controls="financiero-legal" aria-selected="false">Info. Financiera y Legal</button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="validacionTabsContent">
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <h3>Información General de la Propiedad</h3>
                    <div class="filters-grid">
                        <div class="form-group">
                            <label for="numero_credito" class="form-label">Número de Crédito: <span class="text-danger">*</span></label>
                            <input disabled type="text" id="numero_credito" name="numero_credito" class="form-input"
                                value="<?php echo htmlspecialchars($propiedadRevision['numero_credito'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="sucursal_id" class="form-label">Sucursal Asignada: <span class="text-danger">*</span></label>
                            <select disabled id="sucursal_id" name="sucursal_id" class="form-select" required>
                                <?php foreach ($sucursales as $sucursal): ?>
                                    <option value="<?php echo $sucursal['id']; ?>" <?php echo ($propiedadRevision['sucursal_id'] ?? '') == $sucursal['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sucursal['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="administradora_id" class="form-label">Administradora: <span class="text-danger">*</span></label>
                            <select disabled id="administradora_id" name="administradora_id" class="form-select" required>
                                <?php foreach ($administradoras as $admin): ?>
                                    <option value="<?php echo $admin['id']; ?>" <?php echo ($propiedadRevision['administradora_id'] ?? '') == $admin['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ubicacion-detalles" role="tabpanel" aria-labelledby="ubicacion-detalles-tab">
                    <div class="mb-6">
                        <h3>Ubicación</h3>
                        <div class="form-group mb-3 full-width">
                            <label for="direccion" class="form-label">Dirección Completa: <span class="text-danger">*</span></label>
                            <input type="text" id="direccion" name="direccion" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['direccion'] ?? ''); ?>" required>
                        </div>

                        <div class="filters-grid">
                            <div class="form-group"><label for="direccion_extra" class="form-label">Dirección Extra:</label><input type="text" id="direccion_extra" name="direccion_extra" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['direccion_extra'] ?? ''); ?>"></div>
                            <div class="form-group"><label for="fraccionamiento" class="form-label">Fraccionamiento/Colonia:</label><input type="text" id="fraccionamiento" name="fraccionamiento" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['fraccionamiento'] ?? ''); ?>"></div>
                            <div class="form-group"><label for="codigo_postal" class="form-label">Código Postal:</label><input type="text" id="codigo_postal" name="codigo_postal" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['codigo_postal'] ?? ''); ?>"></div>
                        </div>


                        <div class="filters-grid">
                            <div class="form-group">
                                <label for="estado_id" class="form-label">Estado: <span class="text-danger">*</span></label>
                                <span class="form-label">Valor del archivo: <em><?php echo htmlspecialchars($propiedadRevision['estado'] ?? 'N/A'); ?></em></span>
                                <select id="estado_id" name="estado_id" class="form-select" required>
                                    <option value="">Selecciona el estado</option><?php foreach ($estados as $estado): ?><option value="<?php echo $estado['id']; ?>" data-nombre="<?php echo htmlspecialchars(strtolower($estado['nombre'])); ?>"><?php echo htmlspecialchars($estado['nombre']); ?></option><?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="municipio_id" class="form-label">Municipio: <span class="text-danger">*</span></label>
                                <span class="form-label">Valor del archivo: <em><?php echo htmlspecialchars($propiedadRevision['municipio'] ?? 'N/A'); ?></em></span>
                                <select id="municipio_id" name="municipio_id" class="form-select" required disabled>
                                    <option value="">Primero selecciona el Estado</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group"><label for="mapa_url" class="form-label">Link Mapa:</label><input type="url" id="mapa_url" name="mapa_url" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['mapa_url'] ?? ''); ?>"></div>

                    </div>

                    <div>
                        <h3>Detalles del Inmueble</h3>
                        <div class="filters-grid column-3">
                            <div class="form-group"><label for="tipo_vivienda" class="form-label">Tipo Vivienda:</label><input type="text" id="tipo_vivienda" name="tipo_vivienda" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['tipo_vivienda'] ?? ''); ?>"></div>
                            <div class="form-group"><label for="tipo_inmueble" class="form-label">Tipo Inmueble:</label><input type="text" id="tipo_inmueble" name="tipo_inmueble" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['tipo_inmueble'] ?? ''); ?>"></div>
                            <div class="form-group"><label for="metros_construccion" class="form-label">Metros Construcción (m²):</label><input type="number" step="0.01" id="metros_construccion" name="metros_construccion" class="form-input" value="<?php echo htmlspecialchars($propiedadRevision['metros_construccion'] ?? ''); ?>"></div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="financiero-legal" role="tabpanel" aria-labelledby="financiero-legal-tab">
                    <div class="mb-6">
                        <h3>Información Financiera y Legal</h3>
                        <div class="filters-grid">
                            <div class="form-group"><label for="avaluo_administradora" class="form-label">Avalúo:</label><input type="text" id="avaluo_administradora" name="avaluo_administradora" class="form-input input-currency" value="<?php echo htmlspecialchars(number_format((float)($propiedadRevision['avaluo_administradora'] ?? 0), 2, '.', ',')); ?>"></div>
                            <div class="form-group"><label for="precio_lista" class="form-label">Precio Lista:</label><input type="text" id="precio_lista" name="precio_lista" class="form-input input-currency" value="<?php echo htmlspecialchars(number_format((float)($propiedadRevision['precio_lista'] ?? 0), 2, '.', ',')); ?>"></div>
                            <div class="form-group"><label for="cofinavit" class="form-label">COFINAVIT:</label><input type="text" id="cofinavit" name="cofinavit" class="form-input input-currency" value="<?php echo htmlspecialchars(number_format((float)($propiedadRevision['cofinavit'] ?? 0), 2, '.', ',')); ?>"></div>
                        </div>
                        </fieldset>

                        <div class="mb-6">
                            <h3>Datos para Venta</h3>
                            <div class="filters-grid">
                                <div class="form-group"><label for="precio_venta" class="form-label">Precio de Venta Sugerido: <span class="text-danger">*</span></label><input type="text" id="precio_venta" name="precio_venta" class="form-input input-currency" required value="<?php echo htmlspecialchars(number_format((float)($propiedadRevision['precio_venta_sugerido_o_final'] ?? 0), 2, '.', ',')); ?>"></div>
                            </div>
                        </div>

                        <div class="form-group full-width mt-4">
                            <label for="comentarios_admin" class="form-label">Comentarios aAdicionales:</label>
                            <textarea id="comentarios_admin" name="comentarios_admin" class="form-textarea" rows="3"><?php echo htmlspecialchars($propiedadRevision['comentarios_para_admin_iniciales'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Validar y Guardar Propiedad</a>
                <a href="/validaciones-cartera" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
</form>

<script type="module" src="/assets/js/validaciones-cartera-edit.js"></script>