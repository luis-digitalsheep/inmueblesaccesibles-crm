import { Modal } from '../../utils/modal.js';
import { showAlert } from '../../utils/alerts.js';
import { fetchData, postData } from '../../utils/api.js';

// --- ESTADO Y CONSTANTES ---
const appState = {
    filters: {},
    pagination: { limit: 15, offset: 0, total: 0 }
};

const currentUserId = window.App.PageData.currentUserId;
const permissions = window.App.PageData.permissions;

// --- ELEMENTOS DEL DOM ---
const tableBody = document.getElementById('solicitudesTableBody');
const cardTitle = document.getElementById('cardTitle');
const paginationContainer = document.getElementById('pagination-container');

// --- FUNCIONES DE RENDERIZADO ---

/**
 * Renderiza las filas de la tabla de solicitudes de contrato.
 */
function renderTable(solicitudes) {
    if (!tableBody) return;
    if (!solicitudes || solicitudes.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">¡Excelente! No hay solicitudes de contrato pendientes.</td></tr>';
        return;
    }
    tableBody.innerHTML = solicitudes.map(s => `
        <tr>
            <td>${s.id}</td>
            <td>${new Date(s.fecha_solicitud).toLocaleDateString('es-MX')}</td>
            <td><a href="/clientes/ver/${s.cliente_id}" target="_blank">${s.cliente_nombre}</a></td>
            <td><a href="/propiedades/ver/${s.propiedad_id}" target="_blank">${s.propiedad_direccion}</a></td>
            <td>${s.vendedor_nombre}</td>
            <td><span class="status-badge status-apartada">${s.estatus_solicitud}</span></td>
            <td class="actions-column text-end">
                ${permissions.canManage ? `
                    <button class="btn btn-sm btn-primary btn-gestionar" 
                            data-solicitud-id="${s.id}"
                            data-proceso-id="${s.proceso_venta_id}">
                        Gestionar
                    </button>
                ` : ''}
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
 * Llama a la API para obtener la lista de solicitudes pendientes y actualiza la vista.
 */
async function fetchSolicitudes() {
    if (!tableBody) return;
    tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Cargando solicitudes...</td></tr>';

    const params = new URLSearchParams({ ...appState.filters, ...appState.pagination });
    try {
        const response = await fetch(`/api/solicitudes-contrato?${params.toString()}`).then(res => res.json());
        if (response.status !== 'success') throw new Error(response.message);

        appState.pagination.total = response.total;
        if (cardTitle) cardTitle.textContent = `Cola de Trabajo: Solicitudes Pendientes (Total: ${response.total})`;

        renderTable(response.data);
        renderPagination();
    } catch (error) {
        showAlert('No se pudieron cargar las solicitudes. ' + error.message, 'error');
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar datos.</td></tr>';
    }
}


/**
 * Abre un modal con Dropzone para subir el contrato generado.
 */
function openContractUploadModal(solicitudId) {
    const formHtml = `
        <p>Sube el archivo del contrato final (PDF). Al hacerlo, la solicitud se marcará como completada y el proceso de venta avanzará al siguiente paso.</p>
 
        <form id="contratoDropzone" action="/api/solicitudes-contrato/${solicitudId}/subir-contrato" class="dropzone custom-dropzone">
            <div class="dz-message needsclick">
                Arrastra y suelta el archivo aquí o haz clic para seleccionarlo.<br>
            </div>
        </form>
    `;
 
    Modal.show('Subir Contrato Generado', formHtml, {
        showFooter: false,
        onContentReady: () => {
            new Dropzone("#contratoDropzone", {
                paramName: "file",
                maxFiles: 1,
                acceptedFiles: ".pdf",
                success: (file, response) => {
                    showAlert(response.message, 'success');
                    Modal.hide();
                    fetchSolicitudes();
                },
                error: (file, errorMessage) => {
                    showAlert(errorMessage.message || "Error al subir el archivo.", "error");
                }
            });
        }
    });
}

/**
 * Maneja el clic en el botón "Gestionar", mostrando un modal con las acciones disponibles.
 */
async function handleGestionarClick(solicitudId, procesoId) {
    try {
        const { data: solicitud } = await fetchData(`/api/solicitudes-contrato/${solicitudId}`);

        if (solicitud.estatus_solicitud === 'pendiente') {
            Modal.confirm(
                `Asignar Solicitud #${solicitud.id}`,
                '¿Deseas asignarte esta tarea para comenzar a trabajar en el contrato?',
                async () => {
                    try {
                        await postData(`/api/solicitudes-contrato/${solicitud.id}/asignar`);
                        showAlert('Tarea asignada con éxito.', 'success');
                        fetchSolicitudes();
                    } catch (error) {
                        showAlert(error.message, 'error');
                    }
                }
            );
        } else if (solicitud.estatus_solicitud === 'en_proceso') {
            if (solicitud.asignado_a_usuario_id === currentUserId) {
                Modal.show(`Gestionar Solicitud #${solicitud.id}`,
                    `<p>Esta tarea está asignada a ti. El siguiente paso es subir el borrador del contrato para su validación.</p>`,
                    {
                        confirmBtnText: 'Subir Contrato',
                        onConfirm: () => {
                            Modal.hide();
                            setTimeout(() => {
                                openContractUploadModal(solicitudId);
                            }, 310);
                        }
                    }
                );
            } else {
                Modal.alert(
                    `Solicitud #${solicitud.id} en Proceso`,
                    `Esta tarea ya está siendo atendida por <strong>${solicitud.asignado_a_nombre}</strong>.`
                );
            }
        } else {
            Modal.alert(
                `Solicitud #${solicitud.id}`,
                `El estado de esta solicitud es <strong>${solicitud.estatus_solicitud}</strong>. No hay más acciones pendientes.`
            );
        }
    } catch (error) {
        showAlert('No se pudieron cargar los detalles de la solicitud.', 'error');
    }
}


// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    fetchSolicitudes();

    document.body.addEventListener('click', (e) => {
        const gestionarButton = e.target.closest('.btn-gestionar');
        if (gestionarButton) {
            const solicitudId = gestionarButton.dataset.solicitudId;
            const procesoId = gestionarButton.dataset.procesoId;
            handleGestionarClick(solicitudId, procesoId);
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