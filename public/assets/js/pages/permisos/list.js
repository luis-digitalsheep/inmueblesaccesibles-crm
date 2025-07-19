import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData, putData, deleteData } from '../../utils/api.js';

const appState = {
    filters: {},
    pagination: { limit: 15, offset: 0, total: 0 }
};
const permissions = window.App.PageData.permissions;

const tableBody = document.getElementById('tableBody');
const cardTitle = document.getElementById('cardTitle');
const paginationContainer = document.getElementById('pagination-container');
const btnNuevo = document.getElementById('btnNuevo');

// --- TEMPLATE DEL FORMULARIO ---
/**
 * Genera el HTML para el formulario de creación/edición de permisos.
 * @param {object} [permiso={}] - El objeto del permiso para pre-llenar el form.
 * @returns {string} El string de HTML del formulario.
 */
function getFormHtml(permiso = {}) {
    return `
        <form id="permisoForm" class="app-form" novalidate>
            <div class="form-columns cols-2">
                <div class="form-group">
                    <label for="modulo" class="form-label">Módulo</label>
                    <input type="text" name="modulo" class="form-input" value="${permiso.modulo || ''}" placeholder="Ej: prospectos" required>
                </div>
                <div class="form-group">
                    <label for="accion" class="form-label">Acción</label>
                    <input type="text" name="accion" class="form-input" value="${permiso.accion || ''}" placeholder="Ej: ver, crear, ver.todos" required>
                </div>
                <div class="form-group full-width">
                    <label for="nombre" class="form-label">Nombre Clave (Autogenerado)</label>
                    <input type="text" name="nombre" class="form-input" value="${permiso.nombre || ''}" readonly style="background-color: #e9ecef;">
                    <small class="text-muted">Se genera a partir del módulo y la acción (ej. modulo.accion)</small>
                </div>
                <div class="form-group full-width">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-textarea" rows="3">${permiso.descripcion || ''}</textarea>
                </div>
            </div>
        </form>
    `;
}

// --- FUNCIONES DE RENDERIZADO ---
function renderTable(permisos) {
    if (!tableBody) return;

    tableBody.innerHTML = permisos.length === 0
        ? '<tr><td colspan="6" class="text-center">No se encontraron permisos.</td></tr>'
        : permisos.map(item => `
            <tr>
                <td>${item.id}</td>
                <td><strong class="text-primary">${item.nombre}</strong></td>
                <td>${item.modulo}</td>
                <td>${item.accion}</td>
                <td>${item.descripcion || ''}</td>
                <td class="actions-column">
                    ${permissions.canUpdate ? `<button class="btn btn-sm btn-primary btn-edit" data-id="${item.id}">Editar</button>` : ''}
                    ${permissions.canDelete ? `<button class="btn btn-sm btn-danger btn-delete" data-id="${item.id}">Eliminar</button>` : ''}
                </td>
            </tr>
        `).join('');
}

function renderPagination() {
    if (!paginationContainer) return;

    const { total, limit, offset } = appState.pagination;
    const totalPages = Math.ceil(total / limit);
    const currentPage = Math.floor(offset / limit) + 1;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    paginationContainer.innerHTML = `
        <span id="paginationInfo" class="me-3">Página ${currentPage} de ${totalPages} (Total: ${total})</span>
        <div class="pagination-buttons">
            <button class="btn btn-secondary btn-sm" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>&larr; Anterior</button>
            <button class="btn btn-secondary btn-sm" id="nextPageBtn" ${currentPage >= totalPages ? 'disabled' : ''}>Siguiente &rarr;</button>
        </div>
    `;
}

// --- LÓGICA DE API Y EVENTOS ---
async function fetchPermisos() {
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando...</td></tr>';

    const params = new URLSearchParams({ ...appState.filters, ...appState.pagination });

    try {
        const response = await fetch(`/api/permisos?${params.toString()}`).then(res => res.json());
        if (response.status !== 'success') throw new Error(response.message);

        appState.pagination.total = response.total;
        if (cardTitle) cardTitle.textContent = `Listado de Permisos (Total: ${response.total})`;

        renderTable(response.data);
        renderPagination();
    } catch (error) {
        showAlert('No se pudieron cargar los permisos. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

async function handleFormSubmit(itemId = null) {
    const form = document.getElementById('permisoForm');
    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.modulo || !data.accion) {
        showAlert('El módulo y la acción son requeridos.', 'error');
        return;
    }

    try {
        const result = itemId
            ? await putData(`/api/permisos/${itemId}`, data)
            : await postData('/api/permisos', data);

        showAlert(result.message, 'success');
        Modal.hide();
        fetchPermisos();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

async function handleEditClick(itemId) {
    try {
        const {data: response} = await fetchData(`/api/permisos/${itemId}`);
        Modal.show('Editar Permiso', getFormHtml(response), {
            confirmBtnText: 'Actualizar',
            onConfirm: () => handleFormSubmit(itemId)
        });
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

function handleDeleteClick(itemId) {
    Modal.confirm('Confirmar Eliminación', '¿Estás seguro de que deseas eliminar este permiso? Esta acción no se puede deshacer.', async () => {
        try {
            const result = await deleteData(`/api/permisos/${itemId}`);
            showAlert(result.message, 'success');
            fetchPermisos();
        } catch (error) {
            showAlert(error.message, 'error');
        }
    });
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchPermisos();

    btnNuevo?.addEventListener('click', () => {
        Modal.show('Nuevo Permiso', getFormHtml(), {
            confirmBtnText: 'Crear',
            onConfirm: () => handleFormSubmit(null)
        });
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.btn-edit')) handleEditClick(e.target.dataset.id);
        if (e.target.matches('.btn-delete')) handleDeleteClick(e.target.dataset.id);
    });

    document.body.addEventListener('input', (e) => {
        const form = e.target.closest('#permisoForm');
        if (!form) return;

        if (e.target.name === 'modulo' || e.target.name === 'accion') {
            const moduloInput = form.querySelector('input[name="modulo"]');
            const accionInput = form.querySelector('input[name="accion"]');
            const nombreInput = form.querySelector('input[name="nombre"]');

            const modulo = moduloInput.value.trim().toLowerCase();
            const accion = accionInput.value.trim().toLowerCase();

            if (modulo && accion) {
                nombreInput.value = `${modulo}.${accion}`;
            } else {
                nombreInput.value = '';
            }
        }
    });

    if (paginationContainer) {
        paginationContainer.addEventListener('click', (event) => {
            const buttonId = event.target.id;
            const { offset, limit, total } = appState.pagination;

            if (buttonId === 'prevPageBtn' && offset > 0) {
                appState.pagination.offset = Math.max(0, offset - limit);
                fetchPermisos();
            }

            if (buttonId === 'nextPageBtn' && (offset + limit < total)) {
                appState.pagination.offset += limit;
                fetchPermisos();
            }
        });
    }
});