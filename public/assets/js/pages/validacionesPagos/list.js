import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData } from '../../utils/api.js';

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

// --- FUNCIONES DE RENDERIZADO ---

/**
 * Renderiza las filas de la tabla de pagos pendientes.
 */
function renderTable(pagos) {
    if (!tableBody) return;

    if (!pagos || pagos.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">¡Excelente! No hay pagos pendientes de validación.</td></tr>';
        return;
    }

    tableBody.innerHTML = pagos.map(p => `
        <tr>
            <td>${p.id}</td>
            <td>
                <a href="/prospectos/ver/${p.prospecto_id}" target="_blank">${p.prospecto_nombre}</a>
            </td>
            <td>
                <a href="/propiedades/ver/${p.propiedad_id}" target="_blank">${p.propiedad_direccion}</a>
            </td>
            <td>$${Number(p.monto).toLocaleString('es-MX')}</td>
            <td>${new Date(p.fecha_pago).toLocaleDateString('es-MX')}</td>
            <td class="actions-column">
                <button class="btn btn-sm btn-primary btn-review" 
                        data-pago-id="${p.id}" 
                        data-documento-id="${p.documento_comprobante_id}">
                    Revisar Pago
                </button>
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
 * Llama a la API para obtener la lista de pagos pendientes y actualiza la vista.
 */
async function fetchPagosPendientes() {
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando pagos pendientes...</td></tr>';

    const params = new URLSearchParams({ ...appState.filters, ...appState.pagination });

    try {
        const response = await fetch(`/api/validaciones-pagos?${params.toString()}`).then(res => res.json());

        if (response.status !== 'success') throw new Error(response.message);

        appState.pagination.total = response.total;
        if (cardTitle) cardTitle.textContent = `Pagos Pendientes de Validación (Total: ${response.total})`;

        renderTable(response.data);
        renderPagination();
    } catch (error) {
        console.error(error);
        showAlert('No se pudieron cargar los pagos pendientes. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}

/**
 * Maneja el clic en el botón "Revisar Pago", mostrando un modal con opciones.
 */
function handleReviewClick(pagoId, documentoId) {
    const comprobanteUrl = `/documentos/descargar/${documentoId}`;

    const modalContent = `
        <p>Se ha recibido un pago de apartado. Por favor, revisa el comprobante y aprueba o rechaza la operación.</p>
        <div class="text-center my-3">
            <a href="${comprobanteUrl}" target="_blank" class="btn btn-secondary"><i class="fas fa-eye"></i> Ver Comprobante</a>
        </div>
        <p class="small text-muted">Al <strong>Aprobar</strong>, el prospecto se convertirá formalmente en <strong>Cliente</strong> y el proceso de venta avanzará.</p>
    `;

    Modal.show('Revisar Comprobante de Pago', modalContent, {
        confirmBtnText: 'Aprobar Pago',
        cancelBtnText: 'Rechazar',
        onConfirm: async () => {
            Modal.setLoading(true, 'Procesando...');
            try {
                const result = await postData(`/api/validaciones-pagos/${pagoId}/aprobar`);

                showAlert(result.message, 'success');
                Modal.hide();

                fetchPagosPendientes();
            } catch (error) {
                showAlert(error.message, 'error');
                Modal.setLoading(false);
            }
        },
        onCancel: () => {
            Modal.hide();

            setTimeout(() => {
                const rejectFormHtml = `
                    <p>Por favor, especifica el motivo por el cual se rechaza este pago. El vendedor será notificado.</p>
                    <form id="rejectForm" class="app-form">
                        <div class="form-group">
                            <label for="motivo_rechazo" class="form-label">Motivo del Rechazo</label>
                            <textarea id="motivo_rechazo" name="motivo" class="form-textarea" rows="4" required></textarea>
                        </div>
                    </form>
                `;

                Modal.show('Motivo del Rechazo', rejectFormHtml, {
                    confirmBtnText: 'Confirmar Rechazo',
                
                    onConfirm: async () => {
                        const motivo = document.getElementById('motivo_rechazo').value;
                
                        if (!motivo.trim()) {
                            showAlert('Debes especificar un motivo.', 'error');
                            return;
                        }
                
                        try {
                            const result = await postData(`/api/validaciones-pagos/${pagoId}/rechazar`, { motivo });
                            showAlert(result.message, 'success');
                
                            Modal.hide();
                
                            fetchPagosPendientes();
                        } catch (error) {
                            showAlert(error.message, 'error');
                        }
                    }
                });
            }, 300);
        }
    });
}

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchPagosPendientes();

    document.body.addEventListener('click', e => {
        const reviewButton = e.target.closest('.btn-review');
        if (reviewButton) {
            const pagoId = reviewButton.dataset.pagoId;
            const documentoId = reviewButton.dataset.documentoId;
            handleReviewClick(pagoId, documentoId);
        }
    });

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