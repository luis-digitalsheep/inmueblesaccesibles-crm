import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, fetchCatalog, postData, putData, deleteData } from '../../utils/api.js';

// --- ESTADO Y CONSTANTES ---
const appState = {
    filters: {},
    pagination: { limit: 15, offset: 0, total: 0 }
};

const permissions = window.App.PageData.permissions;
let pageCatalogos = {};

// --- ELEMENTOS DEL DOM ---
const tableBody = document.getElementById('usuariosTableBody');
const cardTitle = document.getElementById('cardTitleUsuarios');
const paginationContainer = document.getElementById('pagination-container');
const filtersForm = document.getElementById('userFiltersForm');

// --- TEMPLATE DEL FORMULARIO (para el modal) ---
function getFormHtml(usuario = {}, esEdicion = false) {
    const { roles = [], sucursales = [] } = pageCatalogos;
    const passwordHelpText = esEdicion ? 'Dejar en blanco para no cambiar la contraseña.' : '';

    return `
        <form id="usuarioForm" class="app-form" novalidate>
            <input type="hidden" name="id" value="${usuario.id || ''}">
            <div class="form-columns cols-2">
                <div class="form-group"><label for="nombre" class="form-label">Nombre Completo</label><input type="text" name="nombre" class="form-input" value="${usuario.nombre || ''}" required></div>
                <div class="form-group"><label for="email" class="form-label">Email</label><input type="email" name="email" class="form-input" value="${usuario.email || ''}" required></div>
                <div class="form-group"><label for="telefono" class="form-label">Teléfono</label><input type="tel" name="telefono" class="form-input" value="${usuario.telefono || ''}"></div>
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-input" ${esEdicion ? '' : 'required'}>
                    <small class="text-muted">${passwordHelpText}</small>
                </div>
                <div class="form-group"><label for="rol_id" class="form-label">Rol</label><select name="rol_id" class="form-select">${roles.map(r => `<option value="${r.id}" ${usuario.rol_id == r.id ? 'selected' : ''}>${r.nombre}</option>`).join('')}</select></div>
                <div class="form-group"><label for="sucursal_id" class="form-label">Sucursal</label><select name="sucursal_id" class="form-select">${sucursales.map(s => `<option value="${s.id}" ${usuario.sucursal_id == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}</select></div>
                <div class="form-group"><label for="activo" class="form-label">Estatus</label><select name="activo" class="form-select"><option value="1" ${usuario.activo == 1 ? 'selected' : ''}>Activo</option><option value="0" ${usuario.activo == 0 ? 'selected' : ''}>Inactivo</option></select></div>
            </div>
        </form>
    `;
}

// --- RENDERIZADO ---
function renderTable(usuarios) {
    tableBody.innerHTML = usuarios.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.nombre}</td>
            <td>${user.email}</td>
            <td>${user.rol_nombre || 'N/A'}</td>
            <td>${user.sucursal_nombre || 'N/A'}</td>
            <td>
                <span class="status-badge ${user.activo == 1 ? 'status-vendida' : 'status-retirada'}">
                    ${user.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td class="actions-column">
                ${permissions.canUpdate ? `<button class="btn btn-sm btn-primary btn-edit" data-id="${user.id}">Editar</button>` : ''}
                ${permissions.canDelete && user.activo == 1 ? `<button class="btn btn-sm btn-danger btn-delete" data-id="${user.id}">Desactivar</button>` : ''}
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
            <button class="btn btn-secondary btn-sm" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>
                &larr; Anterior
            </button>
            <button class="btn btn-secondary btn-sm" id="nextPageBtn" ${currentPage >= totalPages ? 'disabled' : ''}>
                Siguiente &rarr;
            </button>
        </div>
    `;
}

// --- LÓGICA DE API Y EVENTOS ---
/**
 * Llama a la API para obtener la lista de usuarios con los filtros
 * y paginación actuales, y luego actualiza la vista.
 */
async function fetchUsuarios() {
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Cargando usuarios...</td></tr>';
    
    const params = new URLSearchParams({
        ...appState.filters,
        limit: appState.pagination.limit,
        offset: appState.pagination.offset
    });

    try {
        const response = await fetch(`/api/usuarios?${params.toString()}`).then(res => res.json());

        if (response.status !== 'success') {
            throw new Error(response.message || 'Error en la respuesta de la API');
        }

        appState.pagination.total = response.total;
        
        if (cardTitle) {
            cardTitle.textContent = `Listado de Usuarios (Total: ${response.total})`;
        }
        
        renderTable(response.data);
        renderPagination();

    } catch (error) {
        console.error('Error al obtener usuarios:', error);
        showAlert('No se pudieron cargar los usuarios. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar datos. Por favor, intente de nuevo.</td></tr>';
    }
}

async function handleFormSubmit(userId = null) {
    const form = document.getElementById('usuarioForm');
    const data = Object.fromEntries(new FormData(form).entries());

    // No enviar la contraseña si está vacía en modo edición
    if (userId && data.password === '') {
        delete data.password;
    }

    try {
        const result = userId
            ? await putData(`/api/usuarios/${userId}`, data)
            : await postData('/api/usuarios', data);

        showAlert(result.message, 'success');
        Modal.hide();
        fetchUsuarios();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

async function handleEditClick(userId) {
    try {
        const {data: usuario} = await fetchData(`/api/usuarios/${userId}`);
        Modal.show('Editar Usuario', getFormHtml(usuario, true), {
            confirmBtnText: 'Actualizar',
            onConfirm: () => handleFormSubmit(userId)
        });
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

function handleDeleteClick(userId) {
    Modal.confirm('Confirmar Desactivación', '¿Estás seguro de que deseas desactivar este usuario?', async () => {
        try {
            const result = await deleteData(`/api/usuarios/${userId}`);
            showAlert(result.message, 'success');
            fetchUsuarios();
        } catch (error) {
            showAlert(error.message, 'error');
        }
    });
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const [{ data: roles }, { data: sucursales }] = await Promise.all([
            fetchCatalog('roles'),
            fetchCatalog('sucursales')
        ]);
        pageCatalogos = { roles, sucursales };
    } catch (error) {
        showAlert('No se pudieron cargar los datos para los formularios.', 'error');
    }

    fetchUsuarios();

    // Listeners principales
    document.getElementById('btnNuevoUsuario')?.addEventListener('click', () => {
        Modal.show('Nuevo Usuario', getFormHtml({}, false), {
            confirmBtnText: 'Crear Usuario',
            onConfirm: () => handleFormSubmit(null)
        });
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.btn-edit')) handleEditClick(e.target.dataset.id);
        if (e.target.matches('.btn-delete')) handleDeleteClick(e.target.dataset.id);
    });

    if (filtersForm) {
        filtersForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(filtersForm);
            appState.filters = Object.fromEntries(formData.entries());

            appState.pagination.offset = 0;

            fetchUsuarios();
        });

        const btnLimpiar = filtersForm.querySelector('button[type="reset"]');

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', (e) => {
                e.preventDefault();
                filtersForm.reset();

                appState.filters = {};
                appState.pagination.offset = 0;

                fetchUsuarios();
            });
        }
    }

    if (paginationContainer) {
        paginationContainer.addEventListener('click', (event) => {
            const buttonId = event.target.id;
            const { offset, limit, total } = appState.pagination;

            if (buttonId === 'prevPageBtn' && offset > 0) {
                appState.pagination.offset = Math.max(0, offset - limit);
                fetchUsuarios();
            }

            if (buttonId === 'nextPageBtn' && (offset + limit < total)) {
                appState.pagination.offset += limit;
                fetchUsuarios();
            }
        });
    }
});