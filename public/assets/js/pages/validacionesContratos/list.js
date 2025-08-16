import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData } from '../../utils/api.js';

// --- ELEMENTOS DEL DOM ---
const tableBody = document.getElementById('tableBody');
const cardTitle = document.getElementById('cardTitle');

// --- RENDERIZADO ---
function renderTable(validaciones) {
    if (!tableBody) return;

    if (!validaciones || validaciones.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">¡Felicidades! No tienes contratos pendientes de validación.</td></tr>';
        return;
    }
    
    tableBody.innerHTML = validaciones.map(v => `
        <tr>
            <td>${v.solicitud_contrato_id}</td>
            <td>${new Date(v.fecha_solicitud).toLocaleDateString('es-MX')}</td>
            <td><a href="/clientes/ver/${v.cliente_id}" target="_blank">${v.prospecto_nombre}</a></td>
            <td>${v.propiedad_direccion}</td>
            <td>${v.vendedor_nombre}</td>
            <td class="actions-column text-end">
                <button class="btn btn-sm btn-primary btn-review" 
                        data-validacion-id="${v.id}"
                        data-solicitud-id="${v.solicitud_contrato_id}">
                    Revisar y Validar
                </button>
            </td>
        </tr>
    `).join('');
}

// --- LÓGICA DE API Y EVENTOS ---
async function fetchValidacionesPendientes() {
    if (!tableBody) return;
    
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando validaciones pendientes...</td></tr>';

    try {
        const {data: response} = await fetchData('/api/validaciones-contratos');
    
        if (cardTitle) cardTitle.textContent = `Contratos Pendientes de mi Validación (Total: ${response.length})`;
    
        renderTable(response);
    } catch (error) {
        showAlert('No se pudieron cargar las validaciones. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

async function handleReviewClick(validacionId, solicitudId) {
    try {
        const {data: solicitud} = await fetchData(`/api/solicitudes-contratos/${solicitudId}`);
        const contratoUrl = `/documentos/descargar/${solicitud.documento_borrador_id}`;

        const modalContent = `
            <p>Se solicita tu validación para el siguiente contrato. Por favor, revisa el borrador y aprueba o rechaza la operación.</p>
            <div class="text-center my-3">
                <a href="${contratoUrl}" target="_blank" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Ver Borrador del Contrato</a>
            </div>
            <div class="form-group">
                <label for="validacion_comentarios" class="form-label">Comentarios (Opcional)</label>
                <textarea id="validacion_comentarios" class="form-textarea" rows="3"></textarea>
            </div>
            <p class="small text-muted">Al <strong>Aprobar</strong>, registrarás tu visto bueno. El proceso de venta no avanzará hasta que todas las áreas requeridas hayan aprobado.</p>
        `;

        Modal.show(`Revisar Solicitud de Contrato #${solicitudId}`, modalContent, {
            confirmBtnText: 'Aprobar',
            cancelBtnText: 'Rechazar',
            onConfirm: async () => {
                const comentarios = document.getElementById('validacion_comentarios').value;
                
                try {
                    const result = await postData(`/api/validaciones-contrato/${validacionId}/aprobar`, { comentarios });
                    
                    showAlert(result.message, 'success');
                    Modal.hide();
                    fetchValidacionesPendientes();
                } catch (error) {
                    showAlert(error.message, 'error');
                }
            },
            onCancel: () => {
                // TODO: Implementar lógica para rechazar una validación
                showAlert('Función de rechazo no implementada.', 'info');
            }
        });

    } catch (error) {
        showAlert('No se pudieron cargar los detalles para la validación.', 'error');
    }
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchValidacionesPendientes();

    document.body.addEventListener('click', e => {
        const reviewButton = e.target.closest('.btn-review');
        if (reviewButton) {
            const validacionId = reviewButton.dataset.validacionId;
            const solicitudId = reviewButton.dataset.solicitudId;
            handleReviewClick(validacionId, solicitudId);
        }
    });
});