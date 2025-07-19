import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData, deleteData } from '../../utils/api.js';

// --- ESTADO Y CONSTANTES ---
const permissions = window.App.PageData.permissions;

// --- ELEMENTOS DEL DOM ---
const tableBody = document.getElementById('rolesTableBody');
const cardTitle = document.getElementById('cardTitle');
const btnNuevoRol = document.getElementById('btnNuevoRol');

// --- TEMPLATE DEL FORMULARIO (para el modal de creación) ---
function getCreateFormHtml() {
    return `
        <form id="rolForm" class="app-form" novalidate>
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre del Rol</label>
                <input type="text" name="nombre" class="form-input" placeholder="Ej: Vendedor, Administrador" required>
            </div>
            <div class="form-group">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-textarea" rows="3" placeholder="Describe brevemente la función de este rol"></textarea>
            </div>
        </form>
    `;
}

// --- FUNCIONES DE RENDERIZADO ---
function renderTable(roles) {
    if (!tableBody) return;
    if (roles.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No se encontraron roles.</td></tr>';
        return;
    }
    tableBody.innerHTML = roles.map(rol => `
        <tr>
            <td>${rol.id}</td>
            <td><strong>${rol.nombre}</strong></td>
            <td>${rol.descripcion || ''}</td>
            <td class="actions-column text-end">
                ${permissions.canUpdate ? `<a href="/roles/editar/${rol.id}" class="btn btn-sm btn-primary">Editar Permisos</a>` : ''}
                ${permissions.canDelete ? `<button class="btn btn-sm btn-danger btn-delete" data-id="${rol.id}">Eliminar</button>` : ''}
            </td>
        </tr>
    `).join('');
}

// --- LÓGICA DE API Y EVENTOS ---
async function fetchRoles() {
    if (!tableBody) return;
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';

    try {
        const {data: response} = await fetchData('/api/roles');

        if (cardTitle) cardTitle.textContent = `Listado de Roles del Sistema (Total: ${response.length})`;

        renderTable(response);
    } catch (error) {
        showAlert('No se pudieron cargar los roles. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

async function handleCreateSubmit() {
    const form = document.getElementById('rolForm');
    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.nombre.trim()) {
        showAlert('El nombre del rol es requerido.', 'error');
        return;
    }

    try {
        const result = await postData('/api/roles', data);
        showAlert(result.message, 'success');
        Modal.hide();
        fetchRoles();
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

function handleDeleteClick(rolId) {
    Modal.confirm('Confirmar Eliminación', '¿Estás seguro de que deseas eliminar este rol? Esta acción no se puede deshacer.', async () => {
        try {
            const result = await deleteData(`/api/roles/${rolId}`);
            
            showAlert(result.message, 'success');
            fetchRoles();
        } catch (error) {
            showAlert(error.message, 'error');
        }
    });
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchRoles();

    btnNuevoRol?.addEventListener('click', () => {
        Modal.show('Crear Nuevo Rol', getCreateFormHtml(), {
            confirmBtnText: 'Crear Rol',
            onConfirm: handleCreateSubmit
        });
    });

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.btn-delete')) {
            handleDeleteClick(e.target.dataset.id);
        }
    });
});