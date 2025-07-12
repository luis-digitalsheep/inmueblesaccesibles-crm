import { fetchData } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';

// --- ESTADO Y ELEMENTOS DEL DOM ---
const appState = {
    filters: {},
    pagination: {
        limit: 15,
        offset: 0,
        total: 0
    }
};

const tableBody = document.getElementById('clientesTableBody');
const paginationContainer = document.getElementById('pagination-container');
const filtersForm = document.getElementById('clienteFiltersForm');
const cardTitleClientes = document.getElementById('cardTitleClientes');

// --- RENDERIZADO ---

function renderTable(clientes) {
    if (clientes.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No se encontraron clientes.</td></tr>';
        return;
    }

    tableBody.innerHTML = clientes.map(c => `
        <tr>
            <td>${c.id}</td>
            <td><a href="/clientes/ver/${c.id}">${c.nombre_completo}</a></td>
            <td><div>${c.celular}</div><small>${c.email || ''}</small></td>
            <td>${c.usuario_responsable_nombre || 'N/A'}</td>
            <td>${c.sucursal_nombre || 'N/A'}</td>
            <td class="actions-column">
                <a href="/clientes/ver/${c.id}" class="btn btn-primary btn-sm btn-info">Gestionar</a>
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

// --- FUNCIONES ---

/**
 * Llama a la API para obtener la lista de clientes y actualiza la vista.
 */
async function fetchClientes() {
    if (!tableBody) return;
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando clientes...</td></tr>';

    const params = new URLSearchParams({
        ...appState.filters,
        limit: appState.pagination.limit,
        offset: appState.pagination.offset
    });

    try {
        const response = await fetchData(`/api/clientes?${params.toString()}`);

        console.log(response);

        appState.pagination.total = response.total;

        if (cardTitleClientes) {
            cardTitleClientes.textContent = `Listado de Clientes (Total: ${response.total})`;
        }

        renderTable(response.data);
        renderPagination();

    } catch (error) {
        console.error('Error al obtener clientes:', error);
        showAlert('No se pudieron cargar los clientes. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    fetchClientes();

    if (filtersForm) {
        filtersForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const formData = new FormData(filtersForm);
            appState.filters = Object.fromEntries(formData.entries());
            appState.pagination.offset = 0;

            fetchClientes();
        });

        const btnLimpiar = filtersForm.querySelector('button[type="reset"]');
        
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => {
                appState.filters = {};
                appState.pagination.offset = 0;

                setTimeout(() => fetchClientes(), 0);
            });
        }
    }

    if (paginationContainer) {
        paginationContainer.addEventListener('click', (event) => {
            const buttonId = event.target.id;
            const { offset, limit, total } = appState.pagination;

            if (buttonId === 'prevPageBtn' && offset > 0) {
                appState.pagination.offset = Math.max(0, offset - limit);
                fetchClientes();
            }

            if (buttonId === 'nextPageBtn' && (offset + limit < total)) {
                appState.pagination.offset += limit;
                fetchClientes();
            }
        });
    }
});