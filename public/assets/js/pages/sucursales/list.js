import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData, putData, deleteData } from '../../utils/api.js';

// --- ESTADO Y CONSTANTES ---
const appState = {
    filters: {},
    pagination: { limit: 15, offset: 0, total: 0 }
};
const permissions = window.App.PageData.permissions;

// --- ELEMENTOS DEL DOM ---
const tableBody = document.getElementById('tableBody');
const cardTitle = document.getElementById('cardTitle');
const paginationContainer = document.getElementById('pagination-container');
const btnNuevo = document.getElementById('btnNuevo');

// --- TEMPLATE DEL FORMULARIO ---
/**
 * Genera el HTML para el formulario de creación/edición de sucursales.
 * @param {object} [sucursal={}] - El objeto de la sucursal para pre-llenar el formulario.
 * @returns {string} El string de HTML del formulario.
 */
function getFormHtml(sucursal = {}) {
    return `
        <form id="sucursalForm" class="app-form" novalidate>
            <input type="hidden" name="id" value="${sucursal.id || ''}">
            <div class="form-columns cols-2">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre de la Sucursal</label>
                    <input type="text" name="nombre" class="form-input" value="${sucursal.nombre || ''}" required>
                </div>
                <div class="form-group">
                    <label for="abreviatura" class="form-label">Abreviatura</label>
                    <input type="text" name="abreviatura" class="form-input" value="${sucursal.abreviatura || ''}" required>
                </div>
            </div>
        </form>
    `;
}

// --- FUNCIONES DE RENDERIZADO ---
/**
 * Renderiza las filas de la tabla de sucursales.
 */
function renderTable(sucursales) {
    if (!tableBody) return;

    if (sucursales.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron sucursales.</td></tr>';
        return;
    }

    tableBody.innerHTML = sucursales.map(item => `
        <tr>
            <td>${item.id}</td>
            <td>${item.nombre}</td>
            <td>${item.abreviatura}</td>
            <td class="actions-column">
                ${permissions.canUpdate ? `<button class="btn btn-sm btn-primary btn-edit" data-id="${item.id}">Editar</button>` : ''}
                ${permissions.canDelete ? `<button class="btn btn-sm btn-danger btn-delete" data-id="${item.id}">Eliminar</button>` : ''}
            </td>
        </tr>
    `).join('');
}

/**
 * Renderiza los controles de paginación.
 */
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
/**
 * Llama a la API para obtener la lista de sucursales y actualiza la vista.
 */
async function fetchSucursales() {
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';

    const params = new URLSearchParams({ ...appState.filters, ...appState.pagination });

    try {
        const response = await fetch(`/api/sucursales?${params.toString()}`).then(res => res.json());

        if (response.status !== 'success') throw new Error(response.message);

        appState.pagination.total = response.total;

        if (cardTitle) cardTitle.textContent = `Listado de Sucursales (Total: ${response.total})`;

        renderTable(response.data);
        renderPagination();
    } catch (error) {
        showAlert('No se pudieron cargar las sucursales. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

/**
 * Maneja el envío del formulario para crear o editar una sucursal.
 */
async function handleFormSubmit(itemId = null) {
    const form = document.getElementById('sucursalForm');
    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.nombre || !data.abreviatura) {
        showAlert('El nombre y la abreviatura son requeridos.', 'error');
        return;
    }

    try {
        const result = itemId
            ? await putData(`/api/sucursales/${itemId}`, data)
            : await postData('/api/sucursales', data);

        showAlert(result.message, 'success');
        Modal.hide();
        fetchSucursales();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

/**
 * Obtiene los datos de una sucursal y abre el modal de edición.
 */
async function handleEditClick(itemId) {
    try {
        const {data: response} = await fetchData(`/api/sucursales/${itemId}`);

        Modal.show('Editar Sucursal', getFormHtml(response, true), {
            confirmBtnText: 'Actualizar',
            onConfirm: () => handleFormSubmit(itemId)
        });
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

/**
 * Muestra una confirmación y elimina una sucursal.
 */
function handleDeleteClick(itemId) {
    Modal.confirm('Confirmar Eliminación', '¿Estás seguro de que deseas eliminar esta sucursal? Esta acción podría fallar si está siendo utilizada por algún usuario o prospecto.', async () => {
        try {
            const result = await deleteData(`/api/sucursales/${itemId}`);
            showAlert(result.message, 'success');
            fetchSucursales();
        } catch (error) {
            showAlert(error.message, 'error');
        }
    });
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchSucursales();

    btnNuevo?.addEventListener('click', () => {
        Modal.show('Nueva Sucursal', getFormHtml({}, false), {
            confirmBtnText: 'Crear',
            onConfirm: () => handleFormSubmit(null)
        });
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.btn-edit')) handleEditClick(e.target.dataset.id);
        if (e.target.matches('.btn-delete')) handleDeleteClick(e.target.dataset.id);

        if (e.target.matches('#prevPageBtn') || e.target.matches('#nextPageBtn')) {
            const { offset, limit, total } = appState.pagination;

            if (e.target.id === 'prevPageBtn' && offset > 0) {
                appState.pagination.offset = Math.max(0, offset - limit);
                fetchSucursales();
            }
            if (e.target.id === 'nextPageBtn' && (offset + limit < total)) {
                appState.pagination.offset += limit;
                fetchSucursales();
            }
        }
    });
});