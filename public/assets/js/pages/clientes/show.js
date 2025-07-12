import { fetchData, putData, postData, fetchCatalog } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';
import { Modal } from '../../utils/modal.js';
import { toggleEditMode } from '../../utils/form-helpers.js';
import { applyCurrencyFormatting } from '../../utils/formatters.js';

// --- ESTADO Y CONSTANTES ---
const clienteId = window.App.PageData.clienteId;
const permissions = window.App.PageData.permissions;
let originalClienteData = null;
let pageCatalogos = {};

// --- CONTENEDORES ---
const pageTitleElement = document.getElementById('pageTitle');
const pageDescriptionElement = document.getElementById('pageDescription');
const infoGeneralContainer = document.getElementById('info-general-container');
const procesosVentaContainer = document.getElementById('procesos-venta-container');
const seguimientoContainer = document.getElementById('seguimiento-container');
const documentosContainer = document.getElementById('documentos-container');
const clienteDetailTabsContent = document.getElementById('clienteDetailTabsContent');


// =================================================================
// FUNCIONES DE RENDERIZADO
// =================================================================

/**
 * Renderiza el formulario de Información General para el cliente.
 */
function renderInfoGeneral(cliente, catalogos) {
    const { usuarios = [], sucursales = [] } = catalogos;

    const formHtml = `
        <form id="formInfoGeneral" class="app-form" novalidate>
            <div class="text-end mb-4">
                ${permissions.canUpdate ? `
                    <button type="button" class="btn btn-secondary btn-sm btn-edit-mode">Editar</button>
                    <button type="submit" class="btn btn-primary btn-sm btn-save-mode" style="display: none;">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary btn-sm btn-cancel-mode" style="display: none;">Cancelar</button>
                ` : ''}
            </div>
            <h5 class="form-section-title">Datos de Contacto</h5>
            <div class="form-columns cols-2">
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-input is-readonly" value="${cliente.nombre_completo || ''}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Celular</label>
                    <input type="tel" name="celular" class="form-input is-readonly" value="${cliente.celular || ''}" readonly>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input is-readonly" value="${cliente.email || ''}" readonly>
                </div>
            </div>
            <hr class="my-4">
            <h5 class="form-section-title">Asignación</h5>
             <div class="form-columns cols-2">
                <div class="form-group">
                    <label class="form-label">Responsable</label>
                    <select name="usuario_responsable_id" class="form-select is-readonly" disabled>
                        ${usuarios.map(u => `<option value="${u.id}" ${cliente.usuario_responsable_id == u.id ? 'selected' : ''}>${u.nombre}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sucursal</label>
                    <select name="sucursal_id" class="form-select is-readonly" disabled>
                        ${sucursales.map(s => `<option value="${s.id}" ${cliente.sucursal_id == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                    </select>
                </div>
            </div>
        </form>
    `;
    infoGeneralContainer.innerHTML = formHtml;
}


/**
 * Renderiza la lista de procesos de venta del cliente.
 */
function renderProcesosVenta(procesos) {
    let listHtml = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="form-section-title mb-0">Oportunidades de Venta</h5>
            ${permissions.canCreateProceso ? `<button class="btn btn-primary btn-sm" id="btnNuevoProcesoCliente"><i class="fas fa-plus"></i> Añadir Proceso</button>` : ''}
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>ID Proceso</th><th>Propiedad</th><th>Estatus</th><th>Creado el</th><th>Acciones</th></tr></thead>
                <tbody>`;
    if (!procesos || procesos.length === 0) {
        listHtml += '<tr><td colspan="5" class="text-center">Este cliente no tiene procesos de venta.</td></tr>';
    } else {
        procesos.forEach(p => {
            listHtml += `
                <tr>
                    <td>${p.id}</td>
                    <td><a href="/propiedades/ver/${p.propiedad_id}">${p.propiedad_direccion}</a></td>
                    <td><span class="status-badge status-disponible">${p.estatus_nombre}</span></td>
                    <td>${new Date(p.created_at).toLocaleDateString('es-MX')}</td>
                    <td class="actions-column"><a href="/procesos-venta/ver/${p.id}" class="btn btn-sm btn-info">Gestionar</a></td>
                </tr>`;
        });
    }

    listHtml += '</tbody></table></div>';
    procesosVentaContainer.innerHTML = listHtml;
}

/**
 * Renderiza el timeline unificado de todos los seguimientos.
 */
function renderSeguimiento(seguimientos) {
    let timelineHtml = '<div class="timeline-container">';

    if (!seguimientos || seguimientos.length === 0) {
        timelineHtml += '<p>No hay seguimientos registrados para este cliente.</p>';
    } else {
        seguimientos.forEach(seg => {
            timelineHtml += `
                <div class="timeline-item">
                    <div class="timeline-icon"><i class="fas fa-comment-dots"></i></div>
                    <div class="timeline-content">
                        <span class="timeline-date">${new Date(seg.fecha_interaccion).toLocaleString('es-MX')} por <strong>${seg.usuario_nombre}</strong> (Proceso #${seg.proceso_venta_id})</span>
                        <p class="timeline-text"><strong>${seg.tipo_interaccion}:</strong> ${seg.comentarios.replace(/\n/g, '<br>')}</p>
                    </div>
                </div>`;
        });
    }

    timelineHtml += '</div>';
    seguimientoContainer.innerHTML = timelineHtml;
}

/**
 * Renderiza la tabla de documentos del cliente.
 */
function renderDocumentos(documentos) {
    let tableHtml = `
        <h5 class="form-section-title">Documentos Adjuntos</h5>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Archivo</th><th>Tipo</th><th>Proceso de Venta</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody>`;
    if (!documentos || documentos.length === 0) {
        tableHtml += '<tr><td colspan="5" class="text-center">No hay documentos para este cliente.</td></tr>';
    } else {
        documentos.forEach(doc => {
            tableHtml += `
                <tr>
                    <td><i class="fas fa-file-alt text-secondary me-2"></i> ${doc.nombre_archivo}</td>
                    <td><span class="status-badge status-retirada">${doc.tipo_documento_nombre}</span></td>
                    <td>${doc.proceso_venta_id ? `<a href="/procesos-venta/ver/${doc.proceso_venta_id}">Proceso #${doc.proceso_venta_id}</a>` : 'General'}</td>
                    <td>${new Date(doc.created_at).toLocaleDateString('es-MX')}</td>
                    <td class="actions-column">
                        <a href="/documentos/descargar/${doc.id}" target="_blank" class="btn btn-sm btn-secondary">Ver</a>
                    </td>
                </tr>`;
        });
    }

    tableHtml += '</tbody></table></div>';
    documentosContainer.innerHTML = tableHtml;
}

// --- LÓGICA DE MANEJO DE EVENTOS ---
async function handleUpdateInfoGeneral(e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries());

    try {
        const result = await putData(`/api/clientes/${clienteId}`, data);
        showAlert(result.message, 'success');
        originalClienteData = result.data;
        toggleEditMode(false, infoGeneralContainer);
    } catch (error) {
        showAlert(error.message, 'error');
    }
}


// =================================================================
// LÓGICA DE INICIALIZACIÓN
// =================================================================

async function initPage() {
    try {
        // Hacemos todas las llamadas a la API en paralelo
        const [
            { data: cliente },
            { data: procesosVenta },
            // {data: seguimientos},  // Necesitarás endpoints para estos
            // {data: documentos},    // y para este
            { data: usuarios },
            { data: sucursales }
        ] = await Promise.all([
            fetchData(`/api/clientes/${clienteId}`),
            fetchData(`/api/clientes/${clienteId}/procesos-venta`), // Endpoint nuevo
            fetchData(`/api/clientes/${clienteId}/seguimientos`), // Endpoint nuevo
            fetchData(`/api/clientes/${clienteId}/documentos`),   // Endpoint nuevo
            fetchData('/api/usuarios/simple-list'),
            fetchCatalog('sucursales')
        ]);

        originalClienteData = cliente;
        pageCatalogos = { usuarios, sucursales };

        pageTitleElement.textContent = `Cliente: ${cliente.nombre_completo}`;
        pageDescriptionElement.textContent = `ID de Cliente: ${cliente.id}`;

        // Renderizamos el contenido de cada pestaña
        renderInfoGeneral(cliente, pageCatalogos);
        renderProcesosVenta(procesosVenta);
        renderSeguimiento(seguimientos);
        renderDocumentos(documentos);
    } catch (error) {
        showAlert('No se pudo cargar la información del cliente. ' + error.message, 'error');
        document.querySelector('.card-body').innerHTML = `<div class="alert alert-danger">Error al cargar los datos.</div>`;
    }
}


// =================================================================
// PUNTO DE ENTRADA DEL SCRIPT
// =================================================================

document.addEventListener('DOMContentLoaded', () => {
    const tabsContainer = document.getElementById('clienteDetailTabs');
    if (tabsContainer) {
        const tabButtons = tabsContainer.querySelectorAll('.nav-link');
        const tabPanes = document.querySelectorAll('#clienteDetailTabsContent .tab-pane');
        tabButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active', 'show'));
                this.classList.add('active');
                document.querySelector(this.getAttribute('data-bs-target')).classList.add('active', 'show');
            });
        });
    }

    clienteDetailTabsContent.addEventListener('click', (event) => {
        const target = event.target;
        if (target.matches('.btn-edit-mode')) {
            toggleEditMode(true, infoGeneralContainer);
        }
        if (target.matches('.btn-cancel-mode')) {
            renderInfoGeneral(originalClienteData, pageCatalogos);
        }
    });

    clienteDetailTabsContent.addEventListener('submit', (event) => {
        if (event.target.id === 'formInfoGeneral') {
            handleUpdateInfoGeneral(event);
        }
    });

    initPage();
});