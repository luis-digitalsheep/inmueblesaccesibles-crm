import { fetchData, putData } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';

const rolId = window.App.PageData.rolId;
const form = document.getElementById('rolEditForm');
const nombreInput = document.getElementById('nombre');
const permissionsContainer = document.getElementById('permissions-container');

/**
 * Renderiza la matriz de checkboxes de permisos usando tarjetas.
 * @param {object} allPermissions - Todos los permisos, agrupados por módulo.
 * @param {Array} assignedPermissionIds - Un array de IDs de los permisos que el rol ya tiene.
 */
function renderPermissions(allPermissions, assignedPermissionIds = []) {
    let html = '';
    const assignedSet = new Set(assignedPermissionIds); 
    
    for (const modulo in allPermissions) {
        html += `
            <div class="permission-group">
                <div class="permission-group-header">
                    <h6 class="permission-group-title">${modulo.replace(/_/g, ' ')}</h6>
                </div>
                <div class="permission-grid">
        `;

        allPermissions[modulo].forEach(permiso => {
            const isChecked = assignedSet.has(permiso.id) ? 'checked' : '';
            html += `
                <div class="permission-item form-check">
                    <input class="form-check-input" type="checkbox" name="permisos[]" value="${permiso.id}" id="permiso_${permiso.id}" ${isChecked}>
                    <label class="form-check-label" for="permiso_${permiso.id}" title="${permiso.descripcion || ''}">
                        ${permiso.accion}
                    </label>
                </div>
            `;
        });

        html += `</div></div>`
    }

    permissionsContainer.innerHTML = `<div class="permissions-container">${html}</div>`;
}

/**
 * Carga todos los datos necesarios para la página.
 */
async function initPage() {
    try {
        const [{ data: rolData }, { data: allPermissions }] = await Promise.all([
            fetchData(`/api/roles/${rolId}`),
            fetchData('/api/permisos/all-grouped')
        ]);

        nombreInput.value = rolData.nombre;

        console.log(allPermissions)
        renderPermissions(allPermissions, rolData.permisos);

    } catch (error) {
        showAlert('No se pudo cargar la información del rol. ' + error.message, 'error');
        permissionsContainer.innerHTML = '<p class="text-danger">Error al cargar permisos.</p>';
    }
}

/**
 * Maneja el envío del formulario.
 */
async function handleFormSubmit(event) {
    event.preventDefault();

    const formData = new FormData(form);
    const data = {
        nombre: formData.get('nombre'),

        permisos: formData.getAll('permisos[]').map(id => parseInt(id))
    };

    try {
        const result = await putData(`/api/roles/${rolId}`, data);
        showAlert(result.message, 'success');

        setTimeout(() => window.location.href = '/roles', 1500);
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    initPage();
    form.addEventListener('submit', handleFormSubmit);
});