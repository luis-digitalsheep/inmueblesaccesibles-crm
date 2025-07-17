import { Modal } from './../../utils/modal.js';
import { showAlert } from './../../utils/alerts.js';
import { fetchData, postData, putData, fetchCatalog } from './../../utils/api.js';
import { toggleEditMode } from '../../utils/form-helpers.js';

// --- ESTADO Y CONSTANTES ---
const prospectoId = window.App.PageData.prospectoId;
const permissions = window.App.PageData.permissions || { canUpdate: false, canAddSeguimiento: false, canManageWorkflow: false, canCreateProceso: false };

let originalProspectoData = null;

let formCatalogos = {
    usuarios: [],
    sucursales: []
};

// --- Contenedores de las Pestañas ---
const globalStatusContainer = document.getElementById('global-status-container');
const infoGeneralContainer = document.getElementById('info-general-container');
const procesosVentaContainer = document.getElementById('procesos-venta-container');
const documentosContainer = document.getElementById('documentos-container');
const pageTitleElement = document.getElementById('pageTitle');


// =================================================================
// FUNCIONES DE RENDERIZADO
// =================================================================

/**
 * Renderiza el flujo de trabajo global del prospecto como un Stepper Horizontal.
 * @param {object} prospecto - Los datos del prospecto.
 * @param {Array} estatusGlobalCatalogo - El catálogo de cat_estatus_global_prospecto.
 */
function renderGlobalWorkflow(prospecto, estatusGlobalCatalogo) {
    if (!estatusGlobalCatalogo || estatusGlobalCatalogo.length === 0) {
        globalStatusContainer.innerHTML = '<p class="text-muted">No se pudo cargar el flujo de estatus.</p>';
        return;
    }

    const estatusActualId = prospecto.estatus_global_id || 1;
    let actionHtml = ''; // El HTML para la acción del paso activo se generará por separado

    const stepperItemsHtml = estatusGlobalCatalogo.map((paso, index) => {
        if (paso.id === 4) return '';
        if (paso.id === 5) return '';

        const isActive = estatusActualId == paso.id;
        const isCompleted = estatusActualId > paso.id;
        const liClass = isCompleted ? 'completed' : (isActive ? 'active' : 'pending');

        if (isActive && permissions.canManageWorkflow) {
            // Lógica de botones condicionales para el flujo GLOBAL
            switch (parseInt(paso.id)) {
                case 1: // 'Contacto Inicial'
                    actionHtml = `
                        <p class="mb-2">El siguiente paso es recibir y subir el Aviso de Privacidad firmado por el prospecto.</p>
                        <button class="btn btn-primary btn-sm btn-upload-global" data-doc-type="1" data-doc-name="Aviso de Privacidad">Subir Aviso de Privacidad</button>`;
                    break;
                case 2: // 'Aviso de Privacidad Recibido'
                    actionHtml = `
                        <p>Aviso recibido. Ahora puedes crear procesos de venta para este prospecto.</p>
                        <button class="btn btn-primary btn-sm btn-marcar-paso-global" data-next-status-id="3">Confirmar y Activar Procesos</button>`;
                    break;
                // ... más casos si el flujo global crece
            }
        }

        // El span dentro del marker es para el número del paso
        return `
            <div class="h-stepper-item ${liClass}">
                <div class="h-stepper-marker">
                    ${isCompleted ? '' : `<span>${index + 1}</span>`}
                </div>
                <div class="h-stepper-label">${paso.nombre}</div>
            </div>`;
    }).join('');

    // Une los items con las líneas de conexión
    const finalStepperHtml = stepperItemsHtml.split('</div>').slice(0, -1).join('</div><div class="h-stepper-line"></div>') + '</div>';

    globalStatusContainer.innerHTML = `
        <div class="horizontal-stepper-wrapper">
            <div class="horizontal-stepper-container">${finalStepperHtml}</div>
            ${actionHtml ? `<div class="action-box">${actionHtml}</div>` : ''}
        </div>
    `;

    // Aquí puedes añadir los listeners para los nuevos botones (.btn-upload-global, etc.)
}

/**
 * Renderiza la lista de procesos de venta asociados al prospecto.
 * @param {Array} procesos - El array de procesos de venta.
 */
function renderProcesosVentaList(procesos) {
    let listHtml = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Oportunidades de Venta Asociadas</h5>
            ${permissions.canCreateProceso ? `<button class="btn btn-primary btn-sm" id="btnNuevoProceso">Añadir Proceso de Venta</button>` : ''}
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Proceso</th>
                        <th>Propiedad</th>
                        <th>Estatus del Proceso</th>
                        <th>Creado el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>`;

    if (!procesos || procesos.length === 0) {
        listHtml += '<tr><td colspan="5" class="text-center">Este prospecto no tiene procesos de venta activos.</td></tr>';
    } else {
        procesos.forEach(proceso => {
            listHtml += `
                <tr>
                    <td>${proceso.id}</td>
                    <td><a href="/propiedades/ver/${proceso.propiedad_id}" title="Ver detalle de la propiedad">${proceso.propiedad_direccion || 'N/A'}</a></td>
                    <td><span class="status-badge status-disponible">${proceso.estatus_nombre || 'N/A'}</span></td>
                    <td>${new Date(proceso.created_at).toLocaleDateString('es-MX')}</td>
                    <td class="actions-column">
                        <a href="/procesos-venta/ver/${proceso.id}" class="btn btn-primary btn-sm">Gestionar Proceso</a>
                    </td>
                </tr>`;
        });
    }

    listHtml += '</tbody></table></div>';
    procesosVentaContainer.innerHTML = listHtml;
}

/**
 * Renderiza un mensaje indicando que los procesos de venta están bloqueados.
 */
function renderProcesosVentaPlaceholder() {
    const container = document.getElementById('procesos-venta-container');
    if (!container) return;

    container.innerHTML = `
        <div class="action-box text-center">
            <i class="fas fa-lock fa-2x text-secondary mb-3"></i>
            <h6 class="text-dark">Función Bloqueada</h6>
            <p class="text-muted mb-0">Para añadir y gestionar procesos de venta, primero debe completar el paso "Aviso de Privacidad Recibido" en la sección de "Estatus Global del Prospecto".</p>
        </div>
    `;
}

/**
 * Renderiza el formulario para editar la información general del prospecto.
 */
function renderInfoGeneralForm(prospecto, catalogos) {
    const { usuarios = [], sucursales = [] } = catalogos;
    const formHtml = `
        <form id="formInfoGeneral" class="app-form" novalidate>
            <div id="form-info-actions" class="text-end mb-4">
                ${permissions.canUpdate ? `
                    <button type="button" id="btnEditarInfo" class="btn btn-secondary btn-sm btn-edit-mode">Editar</button>
                    <button type="submit" id="btnGuardarInfo" class="btn btn-primary btn-sm btn-save-mode" style="display: none;">Guardar Cambios</button>
                    <button type="button" id="btnCancelarInfo" class="btn btn-secondary btn-sm btn-cancel-mode" style="display: none;">Cancelar</button>
                ` : ''}
            </div>

            <div class="form-section mb-4">
                <h5 class="form-section-title">Datos de Contacto</h5>
                <div class="form-columns cols-2">
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-input is-readonly" value="${prospecto.nombre || ''}" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="celular" class="form-label">Celular <span class="text-danger">*</span></label>
                        <input type="tel" id="celular" name="celular" class="form-input is-readonly" value="${prospecto.celular || ''}" readonly required>
                    </div>
                    <div class="form-group full-width">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input is-readonly" value="${prospecto.email || ''}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5 class="form-section-title">Asignación y Configuración</h5>
                <div class="form-columns cols-2">
                    <div class="form-group">
                        <label for="usuario_responsable_id" class="form-label">Responsable</label>
                        <select id="usuario_responsable_id" name="usuario_responsable_id" class="form-select is-readonly" disabled>
                            <option value="">Seleccione...</option>
                            ${usuarios.map(u => `<option value="${u.id}" ${prospecto.usuario_responsable_id == u.id ? 'selected' : ''}>${u.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sucursal_id" class="form-label">Sucursal</label>
                        <select id="sucursal_id" name="sucursal_id" class="form-select is-readonly" disabled>
                             <option value="">Seleccione...</option>
                            ${sucursales.map(s => `<option value="${s.id}" ${prospecto.sucursal_id == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>
        </form>
    `;
    infoGeneralContainer.innerHTML = formHtml;
}

function renderDocumentosTab(documentos) {
    let listHtml = `
        <h5 class="form-section-title">Documentos Adjuntos</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre del Archivo</th>
                    <th>Tipo</th>
                    <th>Fecha de Subida</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (!documentos || documentos.length === 0) {
        listHtml += '<tr><td colspan="4" class="text-center">No hay documentos para este prospecto.</td></tr>';
    } else {
        documentos.forEach(doc => {
            listHtml += `
                <tr>
                    <td>${doc.nombre_archivo}</td>
                    <td><span class="status-badge status-retirada">${doc.tipo_documento_nombre}</span></td>
                    <td>${new Date(doc.created_at).toLocaleDateString('es-MX')}</td>
                    <td class="actions-column">
                        <a href="/documentos/descargar/${doc.id}" target="_blank" class="btn btn-sm btn-secondary" title="Ver Documento">Ver</a>
                        <button class="btn btn-sm btn-danger btn-delete-doc" data-doc-id="${doc.id}" title="Eliminar">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    }

    listHtml += '</tbody></table>';
    documentosContainer.innerHTML = listHtml;
}

// =================================================================
// MANEJADORES DE EVENTOS
// =================================================================

/** * Maneja la actualización de la información general del prospecto.
 * @param {Event} e - El evento del formulario.
 * Este evento se dispara al enviar el formulario de información general.
 * Envía los datos actualizados al servidor y muestra un mensaje de éxito o error.
 */
async function handleUpdateInfoGeneral(e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries());

    data.id = prospectoId;

    try {
        const result = await putData(`/api/prospectos/${prospectoId}`, data);
        showAlert(result.message, 'success');

        originalProspectoData = result.data;
        pageTitleElement.textContent = `Prospecto: ${data.nombre}`;

        toggleEditMode(false, infoGeneralContainer);
    } catch (error) {
        console.error(error);
        showAlert(error.message, 'error');
    }
}

/** * Maneja el clic en el botón "Nuevo Proceso de Venta".
 * Abre un modal para buscar propiedades disponibles y crear un nuevo proceso de venta.
 */
async function handleNuevoProcesoVenta() {
    try {
        const propiedadesDisponiblesResult = await fetchData('/api/propiedades/disponibles');
        const propiedadesDisponibles = propiedadesDisponiblesResult.data;


        if (propiedadesDisponibles.length === 0) {
            Modal.alert('Sin Propiedades', 'Actualmente no hay propiedades disponibles para asignar.');
            return;
        }

        const formHtml = `
            <p>Selecciona una propiedad de la lista para iniciar un nuevo proceso de venta para este prospecto.</p>
            <form id="formNuevoProceso" class="app-form">
                <div class="form-group">
                    <label for="propiedad_id_select" class="form-label">Propiedades Disponibles:</label>
                    <select id="propiedad_id_select" name="propiedad_id" class="form-select" required>
                        <option value="">Seleccione una propiedad...</option>
                        ${propiedadesDisponibles.map(p => `
                            <option value="${p.id}">${p.direccion} - $${Number(p.precio_venta).toLocaleString('es-MX')}</option>
                        `).join('')}
                    </select>
                </div>
            </form>
        `;

        Modal.show('Añadir Nuevo Proceso de Venta', formHtml, {
            confirmBtnText: 'Crear Proceso',
            onConfirm: async () => {
                const select = document.getElementById('propiedad_id_select');
                const propiedadId = select.value;

                if (!propiedadId) {
                    showAlert('Debes seleccionar una propiedad.', 'error');
                    return;
                }

                Modal.setLoading(true);

                try {
                    const result = await postData(`/api/prospectos/${prospectoId}/procesos-venta`, { propiedad_id: propiedadId });
                    showAlert(result.message, 'success');
                    Modal.hide();

                    const nuevosProcesosResult = await fetchData(`/api/prospectos/${prospectoId}/procesos-venta`);

                    const nuevosProcesos = nuevosProcesosResult.data;

                    console.log(nuevosProcesos);

                    renderProcesosVentaList(nuevosProcesos);
                } catch (error) {
                    showAlert(error.message, 'error');
                    Modal.setLoading(false);
                }
            }
        });
    } catch (error) {
        showAlert('No se pudieron cargar las propiedades disponibles.', 'error');
    }
}

/** * Maneja el clic en las pestañas del detalle del prospecto.
 * @param {Event} event - El evento del clic.
 */
function handleTabsClick(event) {
    event.preventDefault();
    document.querySelectorAll('#prospectoDetailTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
    this.classList.add('active');
    document.querySelector(this.getAttribute('data-bs-target')).classList.add('show', 'active');
}

/**
 * Maneja el clic en los botones de acción del flujo de trabajo global.
 * @param {Event} e - El evento del clic.
 */
function handleGlobalWorkflowAction(e) {
    const button = e.target.closest('button');
    if (!button) return;

    if (button.matches('.btn-upload-global')) {
        console.log('btn-upload-global', button)
        const docTypeId = button.dataset.docType;
        const docName = button.dataset.docName;

        console.log(docTypeId)

        openUploadModal(docTypeId, docName);
    } else if (button.matches('.btn-marcar-paso-global')) {
        const nextStatusId = button.dataset.nextStatusId;

        if (nextStatusId) {

            Modal.confirm(
                'Confirmar Avance',
                '¿Estás seguro de que deseas mover este prospecto al siguiente paso del flujo?',
                () => {
                    updateGlobalStatus(nextStatusId);
                }
            );
        }
    }
}

// =================================================================
// FUNCIONES DE MODALES
// =================================================================

/**
 * Abre un modal con una instancia de Dropzone para subir un documento.
 * @param {string} docTypeId - El ID del tipo de documento a subir.
 * @param {string} docName - El nombre descriptivo del documento para el título del modal..
 */
function openUploadModal(docTypeId, docName) {
    
    const formHtml = `
        <p>Arrastra el archivo o haz clic en el área para seleccionarlo.</p>
        <form action="/api/prospectos/${prospectoId}/documentos" class="dropzone custom-dropzone" id="documentUploadDropzone"></form>
    `;

    Modal.show(`Subir ${docName}`, formHtml, {
        showFooter: false,
        onContentReady: () => {
            const dropzone = new Dropzone("#documentUploadDropzone", {
                paramName: "file",
                maxFiles: 1,
                acceptedFiles: ".pdf,.jpg,.jpeg,.png",
                dictDefaultMessage: "Arrastra el archivo aquí o haz clic para subir",

                sending: function (file, xhr, formData) {
                    formData.append("tipo_documento_id", docTypeId);
                },

                success: function (file, response) {
                    showAlert(response.message || 'Documento subido con éxito.', 'success');
                    Modal.hide();

                    setTimeout(() => window.location.reload(), 1500);
                },

                error: function (file, errorMessage) {
                    const message = typeof errorMessage === 'object' ? errorMessage.message : errorMessage;

                    showAlert(message || "Error al subir el archivo.", "error");

                    this.removeFile(file);
                }
            });
        }
    });
}

// =================================================================
// FUNCIONES GENERALES
// =================================================================

async function updateGlobalStatus(newStatusId) {
    if (!newStatusId) return;

    try {
        await putData(`/api/prospectos/${prospectoId}/update-global-status`, { estatus_global_id: newStatusId });

        showAlert('Estatus del prospecto actualizado. Recargando...', 'info');

        setTimeout(() => window.location.reload(), 1500);
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// =================================================================
// FUNCIÓN DE INICIALIZACIÓN PRINCIPAL
// =================================================================
async function initPage() {
    try {
        const [
            { data: prospecto },
            { data: procesosVenta },
            { data: estatusGlobal },
            { data: usuarios },
            { data: sucursales },
            { data: documentos }
        ] = await Promise.all([
            fetchData(`/api/prospectos/${prospectoId}`),
            fetchData(`/api/prospectos/${prospectoId}/procesos-venta`),
            fetchCatalog('estatus-global-prospecto'),
            fetchData('/api/usuarios/simple-list'),
            fetchCatalog('sucursales'),
            fetchData(`/api/prospectos/${prospectoId}/documentos`)
        ]);

        console.log('prospecto', prospecto);
        
        let prospectoInfo = prospecto.prospecto;

        console.log('prospecto', prospectoInfo);

        originalProspectoData = prospectoInfo;

        formCatalogos.usuarios = usuarios;
        formCatalogos.sucursales = sucursales;

        // Actualizar título de la página con el nombre del prospecto
        pageTitleElement.textContent = `Prospecto: ${prospectoInfo.nombre}`;



        // Renderizar cada sección con los datos obtenidos
        renderGlobalWorkflow(prospectoInfo, estatusGlobal);
        renderInfoGeneralForm(prospectoInfo, formCatalogos);
        renderDocumentosTab(documentos);

        const estatusGlobalActual = prospectoInfo.estatus_global_id || 1;

        if (estatusGlobalActual >= 2) {
            renderProcesosVentaList(procesosVenta);
        } else {
            renderProcesosVentaPlaceholder();
        }

        const globalStatusContainer = document.getElementById('global-status-container');

        globalStatusContainer.addEventListener('click', handleGlobalWorkflowAction);
    } catch (error) {
        showAlert('No se pudo cargar la información del prospecto. ' + error.message, 'error');
        document.querySelector('.card-body').innerHTML = '<p class="text-danger text-center">Error al cargar los datos. Por favor, intente recargar la página.</p>';
    }
}

// =================================================================
// PUNTO DE ENTRADA DEL SCRIPT
// =================================================================
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#prospectoDetailTabs .nav-link').forEach(button => {
        button.addEventListener('click', handleTabsClick);
    });

    initPage();

    infoGeneralContainer.addEventListener('click', (event) => {
        const editBtn = event.target.closest('.btn-edit-mode');
        const cancelBtn = event.target.closest('.btn-cancel-mode');

        if (editBtn) {
            toggleEditMode(true, infoGeneralContainer);
        }

        if (cancelBtn) {
            renderInfoGeneralForm(originalProspectoData, formCatalogos);
        }
    });

    infoGeneralContainer.addEventListener('submit', (event) => {
        if (event.target.id === 'formInfoGeneral') {
            handleUpdateInfoGeneral(event);
        }
    });

    const containerDetail = document.getElementById('prospectoDetailTabsContent');

    containerDetail.addEventListener('click', (event) => {
        const nuevoProcesoBtn = event.target.closest('#btnNuevoProceso');
        const guardarInfoBtn = event.target.closest('#formInfoGeneral button[type="submit"]');
        const nuevoSeguimientoBtn = event.target.closest('#formNuevoSeguimiento button[type="submit"]');
        const uploadGlobalBtn = event.target.closest('.btn-upload-global');

        if (nuevoProcesoBtn) {
            event.preventDefault();
            handleNuevoProcesoVenta();
            return;
        }

        if (guardarInfoBtn) {
            document.getElementById('formInfoGeneral')?.addEventListener('submit', handleUpdateInfoGeneral);
        }

        if (uploadGlobalBtn) {
            handleGlobalWorkflowAction(event);
        }
    });
});