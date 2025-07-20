import { fetchData, postData, putData, fetchCatalog } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';
import { Modal } from '../../utils/modal.js';
import { triggerDownload } from '../../utils/downloads.js';

const procesoId = window.App.PageData.procesoVentaId;
const permissions = window.App.PageData.permissions;

// --- Contenedores ---
const workflowContainer = document.getElementById('proceso-workflow-container');
const seguimientoContainer = document.getElementById('proceso-seguimiento-container');
const documentosContainer = document.getElementById('proceso-documentos-container');
const pageTitle = document.getElementById('pageTitle');
const backLink = document.getElementById('back-to-prospect-link');

// --- Funciones de Renderizado ---
function renderWorkflow(proceso, estatusCatalogo) {
    const estatusActualId = proceso.estatus_proceso_id;
    let workflowHtml = '<ul class="process-flow-list">';


    console.log(estatusActualId)
    estatusCatalogo.forEach(paso => {
        if (paso.id < 5) {
            console.log(paso.id)
            const isActive = estatusActualId == (paso.id);
            const isCompleted = estatusActualId > paso.id;
            const liClass = isCompleted ? 'is-completed' : (isActive ? 'is-active' : 'is-pending');

            let actionBoxHtml = '';

            if (isActive && permissions.canManageWorkflow) {
                let actionContent = '';

                switch (parseInt(paso.id)) {
                    case 1: // Estado: "Proceso de Venta Iniciado"
                        actionContent = `
                            <p class="process-step-description">El prospecto ha mostrado interés en esta propiedad. El siguiente paso es agendar y confirmar la visita.</p>
                            <button class="btn btn-primary btn-sm btn-marcar-paso" data-next-status-id="2">Marcar Visita como Realizada</button>`;
                        break;
                    case 2: // Estado: "Visita a Propiedad Realizada"
                        actionContent = `
                            <p class="process-step-description">La visita se ha realizado. Ahora puedes generar el contrato de apartado.</p>
                            <button class="btn btn-primary btn-sm" id="btnGenerarFolio">Generar Contrato de Apartado</button>`;
                        break;
                    case 3: // 'Contrato de Apartado Generado'
                        actionContent = `<p>El contrato de apartado ha sido generado. Sube el comprobante de pago para continuar.</p>
                                     <button class="btn btn-primary btn-sm btn-upload-pago" data-doc-type-id="2">Subir Comprobante</button>`; // Asumiendo que "Comprobante de Apartado" es tipo 2
                        break;
                }
                if (actionContent) {
                    actionBoxHtml = `<div class="process-step-action-box">${actionContent}</div>`;
                }
            }

            workflowHtml += `
                <li class="process-step ${liClass}">
                    <div class="process-step-marker"></div>
                    <div class="process-step-content">
                        <h6 class="process-step-title">${paso.nombre}</h6>
                        <p class="process-step-description">${paso.descripcion || ''}</p>
                        ${actionBoxHtml}
                    </div>
                </li>`;
        }
    });

    workflowHtml += '</ul>';
    workflowContainer.innerHTML = workflowHtml;
}

/**
 * Renderiza el contenido de la pestaña "Seguimiento de Ventas".
 * Incluye el formulario para añadir nuevas interacciones y el historial.
 * @param {Array} seguimientos - El array con el historial de seguimientos para este proceso.
 */
function renderSeguimiento(seguimientos) {
    const container = document.getElementById('proceso-seguimiento-container');
    if (!container) return;

    const formHtml = permissions.canAddSeguimiento ? `
        <fieldset class="form-fieldset mb-4">
            <legend class="fieldset-legend">Añadir Nuevo Seguimiento</legend>
            <form id="formNuevoSeguimiento" class="app-form">
                <div class="form-group mb-2">
                    <label for="tipo_interaccion" class="form-label">Tipo de Interacción</label>
                    <select name="tipo_interaccion" id="tipo_interaccion" class="form-select">
                        <option value="llamada">Llamada</option>
                        <option value="email">Email</option>
                        <option value="cita">Cita</option>
                        <option value="visita_propiedad">Visita a Propiedad</option>
                        <option value="nota">Nota General</option>
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label for="comentarios_seguimiento" class="form-label">Comentarios</label>
                    <textarea name="comentarios" id="comentarios_seguimiento" rows="3" class="form-textarea" required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-sm">Agregar Seguimiento</button>
                </div>
            </form>
        </fieldset>
        <hr class="my-4">
    ` : '';

    const timelineHtml = seguimientos && seguimientos.length > 0
        ? seguimientos.map(seg => `
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fas fa-comment-dots"></i></div>
                <div class="timeline-content">
                    <span class="timeline-date">${new Date(seg.fecha_interaccion).toLocaleString('es-MX')} por <strong>${seg.usuario_nombre || 'N/A'}</strong></span>
                    <p class="timeline-text"><strong>${seg.tipo_interaccion || 'Nota'}:</strong> ${seg.comentarios.replace(/\n/g, '<br>')}</p>
                </div>
            </div>`).join('')
        : '<p>No hay seguimientos registrados para este proceso de venta.</p>';

    container.innerHTML = `
        ${formHtml}
        <h5 class="mt-4">Historial de Interacciones</h5>
        <div class="timeline-container">${timelineHtml}</div>
    `;

    if (permissions.canAddSeguimiento) {
        document.getElementById('formNuevoSeguimiento')?.addEventListener('submit', handleAddSeguimiento);
    }
}

/**
 * Renderiza el contenido de la pestaña "Documentos".
 * Muestra una tabla con los documentos asociados al proceso de venta.
 * @param {Array} documentos - El array con los objetos de documento.
 */
function renderDocumentos(documentos) {
    const container = document.getElementById('proceso-documentos-container');

    if (!container) return;

    let tableHtml = `
        <h5 class="form-section-title">Documentos Adjuntos al Proceso</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Tipo de Documento</th>
                        <th>Fecha de Subida</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (!documentos || documentos.length === 0) {
        tableHtml += '<tr><td colspan="4" class="text-center text-muted">No hay documentos adjuntos a este proceso.</td></tr>';
    } else {
        documentos.forEach(doc => {
            const fechaSubida = new Date(doc.created_at).toLocaleString('es-MX', {
                year: 'numeric', month: 'long', day: 'numeric'
            });

            tableHtml += `
                <tr>
                    <td>
                        <i class="fas fa-file-alt text-secondary me-2"></i>
                        ${doc.nombre_archivo || 'Nombre no disponible'}
                    </td>
                    <td>
                        <span class="status-badge status-retirada">${doc.tipo_documento_nombre || 'General'}</span>
                    </td>
                    <td>${fechaSubida}</td>
                    <td class="actions-column text-end">
                        <a href="/documentos/descargar/${doc.id}" target="_blank" class="btn btn-sm btn-secondary" title="Ver/Descargar Documento">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <button class="btn btn-sm btn-danger btn-delete-doc" data-doc-id="${doc.id}" title="Eliminar Documento">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    tableHtml += '</tbody></table></div>';
    container.innerHTML = tableHtml;
}

// -- Handerls ---
/**
 * Llama a la API para generar el folio y avanza el estado del proceso.
 */
async function handleGenerarFolio() {
    Modal.confirm(
        'Generar Folio',
        '¿Estás seguro de que deseas generar el folio de apartado? Esta acción avanzará el proceso al siguiente paso.',
        async () => {
            Modal.setLoading(true, 'Generando...');

            try {
                const result = await postData(`/api/procesos-venta/${procesoId}/generar-folio`);

                Modal.setLoading(false);

                const pdfUrl = result.data.pdf_url;

                if (pdfUrl) {
                    console.log(pdfUrl)
                    triggerDownload(pdfUrl, `recibo-apartado-${result.data.folio}.pdf`);
                }

                showAlert(result.message, 'success');

                setTimeout(() => window.location.reload(), 2000);

            } catch (error) {
                Modal.setLoading(false);
                showAlert(error.message, 'error');
            }
        }
    );
}


/**
 * Abre un modal con Dropzone para subir el comprobante de pago.
 * @param {string} docTypeId - El ID del tipo de documento "Comprobante de Pago".
 */
function handleUploadPago(docTypeId) {
    const formHtml = `
        <p>Sube el comprobante de pago del apartado..</p>

        <form id="pagoDropzone" action="/api/procesos-venta/${procesoId}/subir-comprobante" class="dropzone custom-dropzone">
            <div class="dz-message needsclick">
                Arrastra y suelta el comprobante de pago aquí o haz clic para seleccionarlo.<br>
            </div>
        </form>
    `;

    Modal.show('Subir Comprobante de Pago', formHtml, {
        showFooter: false,
        onContentReady: () => {
            new Dropzone("#pagoDropzone", {
                sending: (file, xhr, formData) => formData.append("tipo_documento_id", docTypeId),
                success: (file, response) => {
                    showAlert('Comprobante subido.', 'success');
                    Modal.hide();
                    setTimeout(() => window.location.reload(), 1500);
                },
                error: (file, response) => {
                    showAlert(response.message, 'error');
                }
            });
        }
    });
}

/**
 * Maneja el envío del formulario para añadir un nuevo seguimiento.
 * Llama a la API para guardar los datos y actualiza la vista.
 * @param {Event} e - El evento de submit del formulario.
 */
async function handleAddSeguimiento(e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.comentarios.trim()) {
        showAlert('El campo de comentarios no puede estar vacío.', 'error');
        return;
    }

    try {
        const result = await postData(`/api/procesos-venta/${procesoId}/seguimientos`, data);

        showAlert(result.message, 'success');

        form.reset();

        const seguimientosActualizados = await fetchData(`/api/procesos-venta/${procesoId}/seguimientos`);
        renderSeguimiento(seguimientosActualizados);

    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// --- Lógica Principal ---
async function initPage() {
    try {
        const [procesoResult, seguimientosResult, documentosResult, estatusCatalogoResult] = await Promise.all([
            fetchData(`/api/procesos-venta/${procesoId}`),
            fetchData(`/api/procesos-venta/${procesoId}/seguimientos`),
            fetchData(`/api/procesos-venta/${procesoId}/documentos`),
            fetchCatalog('estatus-prospeccion')
        ]);



        const proceso = procesoResult.data;
        const seguimientos = seguimientosResult.data;
        const documentos = documentosResult.data;
        const estatusCatalogo = estatusCatalogoResult.data;

        pageTitle.textContent = `Proceso de Venta: ${proceso.propiedad_direccion}`;
        backLink.href = `/prospectos/ver/${proceso.prospecto_id}`;

        renderWorkflow(proceso, estatusCatalogo);
        renderSeguimiento(seguimientos);
        renderDocumentos(documentos);

        // ... Enlazar los listeners para los botones del workflow ...

    } catch (error) {
        showAlert(error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // --- LÓGICA PARA MANEJAR LAS PESTAÑAS (TABS) ---
    const tabsContainer = document.getElementById('procesoDetailTabs');
    if (tabsContainer) {
        const tabButtons = tabsContainer.querySelectorAll('.nav-link');
        const tabPanes = document.querySelectorAll('.tab-content .tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('show', 'active'));

                this.classList.add('active');
                const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
    }

    // --- MANEJO DE ACCIONES (DELEGACIÓN DE EVENTOS) ---
    const mainContentContainer = document.querySelector('.tab-content');

    if (mainContentContainer) {
        mainContentContainer.addEventListener('click', (event) => {
            const button = event.target.closest('button');

            if (!button) return;

            // Para el botón de "Generar Folio"
            if (button.matches('#btnGenerarFolio')) {
                event.preventDefault();
                handleGenerarFolio();
            }

            // Para el botón de "Subir Comprobante de Pago"
            if (button.matches('.btn-upload-pago')) {
                event.preventDefault();
                const docTypeId = button.dataset.docTypeId;

                handleUploadPago(docTypeId);
            }

            // Para el botón de "Marcar Visita como Realizada" o cualquier otro paso genérico
            if (button.matches('.btn-marcar-paso')) {
                event.preventDefault();
                const nextStatusId = button.dataset.nextStatusId;

                Modal.confirm('Confirmar Avance', '¿Confirmas que este paso se ha completado?', async () => {
                    try {
                        await putData(`/api/procesos-venta/${procesoId}/update-status`, { estatus_proceso_id: nextStatusId });

                        showAlert('Proceso actualizado.', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        showAlert(error.message, 'error');
                    }
                });
            }
        });
    }

    // --- LLAMADA A LA FUNCIÓN DE INICIALIZACIÓN PRINCIPAL ---
    initPage();
});