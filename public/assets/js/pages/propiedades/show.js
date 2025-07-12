import { fetchData, putData, fetchCatalog } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';
import { Modal } from '../../utils/modal.js';
import { toggleEditMode } from '../../utils/form-helpers.js';

// --- ESTADO Y CONSTANTES ---
const propiedadId = window.App.PageData.propiedadId;
const permissions = window.App.PageData.permissions;
let originalPropiedadData = null;
let pageCatalogos = {};

// --- CONTENEDORES ---
const pageTitleElement = document.getElementById('pageTitle');
const pageDescriptionElement = document.getElementById('pageDescription');
const infoGeneralContainer = document.getElementById('info-general-container');
const infoFinancieraContainer = document.getElementById('info-financiera-container');
const fotosComentariosContainer = document.getElementById('fotos-comentarios-container');
const clienteAsociadoContainer = document.getElementById('cliente-asociado-container');
const clienteAsociadoTab = document.getElementById('cliente-asociado-tab');

// --- RENDERIZADO ---
function renderInfoGeneral(propiedad, catalogos) {
    const formHtml = `
        <form id="formInfoGeneral" class="app-form" novalidate>
             <div id="form-info-actions" class="text-end mb-4">
                ${permissions.canUpdate ? `
                    <button type="button" id="btnEditarInfo" class="btn btn-secondary btn-sm btn-edit-mode">Editar</button>
                    <button type="submit" id="btnGuardarInfo" class="btn btn-primary btn-sm btn-save-mode" style="display: none;">Guardar Cambios</button>
                    <button type="button" id="btnCancelarInfo" class="btn btn-secondary btn-sm btn-cancel-mode" style="display: none;">Cancelar</button>
                ` : ''}
            </div>
            <h5 class="form-section-title">Información General y Ubicación</h5>
            <div class="form-columns cols-2">
                <div class="form-group">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-input is-readonly" value="${propiedad.direccion || ''}" readonly>
                </div>
                 <div class="form-group">
                    <label for="numero_credito" class="form-label">Número de Crédito</label>
                    <input type="text" name="numero_credito" class="form-input is-readonly" value="${propiedad.numero_credito || ''}" readonly>
                </div>
                 <div class="form-group">
                    <label for="sucursal_id" class="form-label">Sucursal</label>
                    <select name="sucursal_id" class="form-select is-readonly" disabled>
                        ${catalogos.sucursales.map(s => `<option value="${s.id}" ${propiedad.sucursal_id == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                    </select>
                </div>
                </div>
        </form>
    `;
    infoGeneralContainer.innerHTML = formHtml;
}

/**
 * Renderiza el contenido de la pestaña "Información Financiera y Legal".
 * @param {object} propiedad - El objeto con los datos de la propiedad.
 * @param {object} catalogos - Un objeto que contiene los catálogos necesarios, ej. { estatusJuridico: [...] }.
 */
function renderInfoFinanciera(propiedad) {
    const container = document.getElementById('info-financiera-container');
    if (!container) return;

    // Formateamos los valores numéricos para mostrarlos correctamente
    const formatCurrency = (value) => {
        const num = parseFloat(value);
        return !isNaN(num) ? num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
    };

    const formHtml = `
        <form id="formInfoFinanciera" class="app-form" novalidate>
            <div id="form-financiero-actions" class="text-end mb-4">
                ${permissions.canUpdate ? `
                    <button type="button" class="btn btn-secondary btn-sm btn-edit-mode">Editar</button>
                    <button type="submit" class="btn btn-primary btn-sm btn-save-mode" style="display: none;">Guardar Cambios</button>
                    <button type="button" class="btn btn-cancel-mode" class="btn btn-secondary btn-sm" style="display: none;">Cancelar</button>
                ` : ''}
            </div>

            <div class="form-section mb-4">
                <h5 class="form-section-title">Información Financiera</h5>
                <div class="form-columns cols-2">
                    <div class="form-group">
                        <label for="precio_venta" class="form-label">Precio de Venta</label>
                        <input type="text" name="precio_venta" class="form-input is-readonly input-currency" value="${formatCurrency(propiedad.precio_venta)}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="precio_lista" class="form-label">Precio de Lista</label>
                        <input type="text" name="precio_lista" class="form-input is-readonly input-currency" value="${formatCurrency(propiedad.precio_lista)}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="avaluo_administradora" class="form-label">Avalúo de Administradora</label>
                        <input type="text" name="avaluo_administradora" class="form-input is-readonly input-currency" value="${formatCurrency(propiedad.avaluo_administradora)}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="cofinavit" class="form-label">COFINAVIT</label>
                        <input type="text" name="cofinavit" class="form-input is-readonly" value="${propiedad.cofinavit || ''}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5 class="form-section-title">Información Legal</h5>
                <div class="form-columns cols-2">
                    

                </div>
            </div>
        </form>
    `;
    container.innerHTML = formHtml;
}

/**
 * Renderiza el contenido de la pestaña "Fotos y Comentarios".
 * @param {object} propiedad - El objeto con los datos de la propiedad.
 */
function renderFotosComentarios(propiedad) {
    const container = document.getElementById('fotos-comentarios-container');
    if (!container) return;

    const fotos = propiedad.fotos_rutas ? propiedad.fotos_rutas.split(',') : [];

    const addPhotoButtonHtml = `
        <div class="photo-item is-add-button">
            <a id="btnAddPhoto" class="add-photo-link" title="Añadir nueva foto">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    `;

    let galeriaHtml = '<div class="photo-gallery-horizontal">';

    galeriaHtml += addPhotoButtonHtml;

    if (fotos.length > 0) {
        fotos.forEach(fotoUrl => {
            galeriaHtml += `
                <div class="photo-item">
                    <a href="${fotoUrl}" class="glightbox" data-gallery="propiedad-gallery">
                        <img src="${fotoUrl}" alt="Foto de la propiedad">
                    </a>
                </div>
            `;
        });
    }
    galeriaHtml += '</div>';

    const comentariosHtml = `
        <form id="formComentarios" class="app-form" novalidate>
            <div id="form-comentarios-actions" class="text-end mb-3">
                ${permissions.canUpdate ? `
                    <button type="button" class="btn btn-secondary btn-sm btn-edit-mode">Editar Comentarios</button>
                    <button type="submit" class="btn btn-primary btn-sm btn-save-mode" style="display: none;">Guardar</button>
                    <button type="button" class="btn btn-cancel-mode" class="btn btn-secondary btn-sm btn-cancel-mode" style="display: none;">Cancelar</button>
                ` : ''}
            </div>
            <div class="form-group">
                <label for="comentarios_internos" class="form-label">Comentarios Internos</label>
                <textarea name="comentarios_internos" id="comentarios_internos" class="form-textarea is-readonly" rows="5" readonly>${propiedad.comentarios_internos || ''}</textarea>
            </div>
        </form>
    `;

    container.innerHTML = `
        <div class="form-section mb-4">
            <h5 class="form-section-title">Galería de Fotos</h5>
            ${galeriaHtml}
        </div>

        <div class="form-section">
            <h5 class="form-section-title">Notas y Comentarios</h5>
            ${comentariosHtml}
        </div>
    `;
}

// --- INICIALIZACIÓN ---
async function initPage() {
    try {
        infoGeneralContainer.innerHTML = '<p class="text-muted p-5 text-center">Cargando...</p>';
        infoFinancieraContainer.innerHTML = '<p class="text-muted p-5 text-center">Cargando...</p>';
        fotosComentariosContainer.innerHTML = '<p class="text-muted p-5 text-center">Cargando...</p>';

        const [
            propiedad,
            sucursales,
            administradoras,
        ] = await Promise.all([
            fetchData(`/api/propiedades/${propiedadId}`),
            fetchCatalog('sucursales'),
            fetchCatalog('administradoras'),
        ]);

        originalPropiedadData = propiedad.data;
        

        pageCatalogos = { 'sucursales': sucursales.data, 'administradoras': administradoras.data };

        pageTitleElement.textContent = `Propiedad: ${originalPropiedadData.direccion}`;
        pageDescriptionElement.textContent = `ID: ${originalPropiedadData.id} | Número de Crédito: ${originalPropiedadData.numero_credito}`;

        renderInfoGeneral(originalPropiedadData, pageCatalogos);
        renderInfoFinanciera(originalPropiedadData, pageCatalogos);
        renderFotosComentarios(originalPropiedadData);

        if (originalPropiedadData.cliente_id) {
            clienteAsociadoTab.style.display = 'list-item';
            // fetch adicional para los detalles del cliente si es necesario
            // const cliente = await fetchData(`/api/clientes/${propiedad.cliente_id}`);
            // renderClienteAsociado(cliente);
            clienteAsociadoContainer.innerHTML = `<p>Cliente asociado: <a href="/clientes/ver/${originalPropiedadData.cliente_id}">${originalPropiedadData.cliente_nombre_asociado}</a></p>`;
        }

        if (window.GLightbox) {
            GLightbox({ selector: '.glightbox' });
        }

    } catch (error) {
        showAlert('No se pudo cargar la información de la propiedad. ' + error.message, 'error');
        document.querySelector('.card-body').innerHTML = `
            <div class="alert alert-danger">
                <strong>Error al cargar:</strong> ${error.message}. Por favor, intente recargar la página.
            </div>`;
    }
}


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#propiedadDetailTabs .nav-link').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            document.querySelectorAll('#propiedadDetailTabs .nav-link').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.querySelector(this.getAttribute('data-bs-target'));
            if (targetPane) {
                targetPane.classList.add('active');
            }
        });
    });

    const cardBody = document.querySelector('#propiedadDetailTabsContent');

    cardBody.addEventListener('click', (event) => {
        const formContainer = event.target.closest('.tab-pane');
        if (!formContainer) return;

        if (event.target.matches('.btn-edit-mode')) {
            toggleEditMode(true, formContainer);
        }
        if (event.target.matches('.btn-cancel-mode')) {
            if (formContainer.id === 'info-general') {
                renderInfoGeneral(originalPropiedadData, pageCatalogos);
            }

            if (formContainer.id === 'info-financiera') {
                renderInfoFinanciera(originalPropiedadData, pageCatalogos);
            }

            if (formContainer.id === 'fotos-comentarios') {
                renderFotosComentarios(originalPropiedadData);
            }
        }
    });

    initPage();
});