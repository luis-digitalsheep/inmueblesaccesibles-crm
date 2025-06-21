import { Modal } from './utils/modal.js';
import { showAlert } from './utils/alerts.js';
import { fetchCatalog, fetchData, postData, deleteData } from './utils/api.js';

// --- Variables ---
const appState = {
    filters: {},
    pagination: { limit: 15, offset: 0, total: 0 }
};

const tableBody = document.getElementById('prospectosTableBody');
const paginationContainer = document.getElementById('pagination-container');
const filtersForm = document.getElementById('prospectoFiltersForm');
const btnNuevoProspecto = document.getElementById('btnNuevoProspecto');
const cardTitleProspectos = document.getElementById('cardTitleProspectos');

// --- Funciones ---
function getCreateFormHtml(catalogos) {
    const { usuarios = [], sucursales = [] } = catalogos;
    const currentUserId = window.App.Permissions.getUserId();
    const currentUserSucursalId = window.App.Permissions.getSucursalId();

    return `
        <form id="quickCreateForm" class="app-form" novalidate>
            <p class="mb-3">Registra un nuevo prospecto con la información esencial. Podrás añadir más detalles y seguimiento desde su perfil.</p>
            <div class="filters-grid column-2">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre Completo: <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="celular" class="form-label">Celular: <span class="text-danger">*</span></label>
                    <input type="tel" id="celular" name="celular" class="form-input" required>
                </div>
                <div class="form-group full-width">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-input">
                </div>
                <div class="form-group">
                    <label for="usuario_responsable_id" class="form-label">Responsable:</label>
                    <select id="usuario_responsable_id" name="usuario_responsable_id" class="form-select">
                        <option value="">Seleccione...</option>
                        ${usuarios.map(u => `<option value="${u.id}" ${currentUserId == u.id ? 'selected' : ''}>${u.nombre}</option>`).join('')}
                    </select>
                </div>
                 <div class="form-group">
                    <label for="sucursal_id" class="form-label">Sucursal:</label>
                    <select id="sucursal_id" name="sucursal_id" class="form-select">
                        <option value="">Seleccione...</option>
                        ${sucursales.map(s => `<option value="${s.id}" ${currentUserSucursalId == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                    </select>
                </div>
            </div>
        </form>
    `;
}

function renderTable(prospectos) {
    if (prospectos.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron prospectos con los filtros actuales.</td></tr>';
        return;
    }
    tableBody.innerHTML = prospectos.map(p => `
        <tr>
            <td>${p.id}</td>
            <td><a href="/prospectos/ver/${p.id}">${p.nombre}</a></td>
            <td><div>${p.celular}</div><small class="text-secondary">${p.email || ''}</small></td>
            <td>${p.usuario_responsable_nombre || 'N/A'}</td>
            <td>${p.sucursal_nombre || 'N/A'}</td>
            <td>${new Date(p.created_at).toLocaleDateString('es-MX')}</td>
            <td class="actions-column">
                <a href="/prospectos/ver/${p.id}" class="btn btn-sm btn-info" title="Ver Detalles y Seguimiento">Gestionar</a>
                <button class="btn btn-sm btn-danger btn-delete" data-id="${p.id}" title="Eliminar Prospecto">Eliminar</button>
            </td>
        </tr>
    `).join('');
}

function renderPagination() {
    const { total, limit, offset } = appState.pagination;

    const totalPages = Math.ceil(total / limit);
    const currentPage = Math.floor(offset / limit) + 1;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    paginationContainer.innerHTML = `
        <button class="btn btn-secondary btn-sm" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>&larr; Anterior</button>
        <span id="paginationInfo">Página ${currentPage} de ${totalPages} (Total: ${total})</span>
        <button class="btn btn-secondary btn-sm" id="nextPageBtn" ${currentPage === totalPages ? 'disabled' : ''}>Siguiente &rarr;</button>
    `;

}

async function fetchProspectos() {
    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Cargando...</td></tr>';

    const params = new URLSearchParams({ ...appState.filters, ...appState.pagination });

    try {
        const response = await fetch(`/api/prospectos?${params.toString()}`);

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const result = await response.json();

        appState.pagination.total = result.total;
        cardTitleProspectos.textContent = `Listado de Prospectos (Total: ${result.total})`;

        renderTable(result.data);
        renderPagination();
    } catch (error) {
        console.error('Error al obtener prospectos:', error);
        showAlert('No se pudieron cargar los prospectos.', 'error');

        tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Error al cargar datos.</td></tr>';
    }
}

async function fetchCatalogosParaFiltros() {
    try {
        const [usuarios, sucursales, estatus] = await Promise.all([
            fetchData('/api/usuarios'),
            fetchCatalog('sucursales'),
        ]);

        // Poblar los selects de los filtros
        document.getElementById('filter_usuario').innerHTML += usuarios.map(u => `<option value="${u.id}">${u.nombre}</option>`).join('');
        document.getElementById('filter_sucursal').innerHTML += sucursales.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');

        return { usuarios, sucursales };
    } catch (error) {
        console.error("Error cargando catálogos para filtros:", error);
        showAlert("No se pudieron cargar los filtros. Por favor, recarga la página.", 'error');
        return null;
    }
}

async function handleNuevoProspecto() {
    const catalogosParaForm = await fetchCatalogosParaFiltros(); // Reutiliza la misma función
    if (!catalogosParaForm) {
        showAlert('No se pudieron cargar los datos necesarios para crear un prospecto.', 'error');
        return;
    }
    Modal.show('Creación Rápida de Prospecto', getCreateFormHtml(catalogosParaForm), {
        onConfirm: async () => {
            const form = document.getElementById('quickCreateForm');
            const data = Object.fromEntries(new FormData(form).entries());
            // Validación simple en cliente
            if (!data.nombre || !data.celular) {
                showAlert('Nombre y celular son requeridos.', 'error'); return;
            }
            // Llamada a la API
            try {
                const response = await fetch('/api/prospectos', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (response.ok) {
                    showAlert('Prospecto creado con éxito.', 'success');
                    Modal.hide();
                    fetchProspectos(); // Recargar la lista
                } else {
                    showAlert(result.message || 'Error al crear el prospecto.', 'error');
                }
            } catch (error) {
                showAlert('Error de conexión.', 'error');
            }
        },
        confirmBtnText: 'Crear Prospecto'
    });
}

function handleDeleteProspecto(id) {
    Modal.confirm('Confirmar Eliminación', `¿Estás seguro de que deseas eliminar el prospecto con ID ${id}? Esta acción no se puede deshacer.`, async () => {
        try {
            const response = await fetch(`/api/prospectos/${id}`, { method: 'DELETE' });
            const result = await response.json();
            if (response.ok) {
                showAlert(result.message, 'success');
                fetchProspectos();
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            showAlert('Error de conexión al eliminar el prospecto.', 'error');
        }
    });
}

// --- Eventos ---
document.addEventListener('DOMContentLoaded', () => {
    fetchProspectos();
    fetchCatalogosParaFiltros();

    btnNuevoProspecto?.addEventListener('click', handleNuevoProspecto);

    filtersForm?.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(filtersForm);

        appState.filters = Object.fromEntries(formData.entries());
        appState.pagination.offset = 0;

        fetchProspectos();
    });

    filtersForm?.addEventListener('reset', () => {
        appState.filters = {};
        appState.pagination.offset = 0;

        fetchProspectos();
    });

    document.body.addEventListener('click', (event) => {
        if (event.target.matches('.btn-delete')) {
            handleDeleteProspecto(event.target.dataset.id);
        }

        if (event.target.matches('#prevPageBtn, #nextPageBtn')) {
            const { offset, limit, total } = appState.pagination;

            const direction = event.target.id === 'nextPageBtn' ? 'next' : 'prev';

            if (direction === 'next' && (offset + limit < total)) {
                appState.pagination.offset += limit;
                fetchProspectos();
            } else if (direction === 'prev' && offset > 0) {
                appState.pagination.offset = Math.max(0, offset - limit);
                fetchProspectos();
            }
        }
    });
});