import { fetchData, putData, fetchCatalog } from '../../utils/api.js';
import { showAlert } from '../../utils/alerts.js';
import { applyCurrencyFormatting, parseDateForInput } from '../../utils/formatters.js';
import { Modal } from '../../utils/modal.js';

const revisionId = window.App.PageData.revisionId;
const permissions = window.App.PageData.permissions;

let isDropzoneInitialized = false;

// --- Contenedores ---
const cardBody = document.querySelector('.card-body');
const formContainer = document.getElementById('formValidacion');
const containerGeneral = document.getElementById('container-general');
const containerUbicacion = document.getElementById('container-ubicacion');
const containerDetalles = document.getElementById('container-detalles');
const containerFotos = document.getElementById('container-fotos');

// --- RENDERIZADO ---

function renderTabGeneral(revision, catalogos) {
    const { sucursales = [], administradoras = [] } = catalogos;

    containerGeneral.innerHTML = `
        <input type="hidden" name="cartera_id" value="${revision.cartera_id || ''}">

        <h5 class="form-section-title">Información General</h5>
        <div class="form-columns cols-3">
            <div class="form-group">
                <label class="form-label">Número de Crédito</label>
                <input type="text" name="numero_credito" class="form-input" value="${revision.numero_credito || ''}">
            </div>
            <div class="form-group">
                <label class="form-label">Sucursal Asignada</label>
                <select name="sucursal_id" class="form-select">
                    ${sucursales.map(s => `<option value="${s.id}" ${revision.sucursal_id == s.id ? 'selected' : ''}>${s.nombre}</option>`).join('')}
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Administradora</label>
                 <select name="administradora_id" class="form-select">
                    ${administradoras.map(a => `<option value="${a.id}" ${revision.administradora_id == a.id ? 'selected' : ''}>${a.nombre}</option>`).join('')}
                </select>
            </div>
        </div>
    `;
}

function renderTabUbicacion(revision, catalogos) {
    const { estados = [] } = catalogos;

    if (!containerUbicacion) return;

    const formHtml = `
        <h5 class="form-section-title">Ubicación</h5>

        <div class="form-group mb-3">
            <label class="form-label">Dirección (Calle, Número, etc.)</label>
            <input type="text" name="direccion" class="form-input" value="${revision.direccion || ''}">
        </div>
        
        <div class="form-columns cols-3">
            <div class="form-group">
                <label class="form-label">Dirección extra
                    <small class="text-muted">(Opcional)</small>
                </label>
                <input type="text" name="direccion_extra" class="form-input" value="${revision.direccion_extra || ''}">
            </div>
            <div class="form-group">
                <label class="form-label">Codigo Postal</label>
                <input type="text" name="codigo_postal" class="form-input" value="${revision.codigo_postal || ''}">
            </div>
            <div class="form-group">
                <label class="form-label">Fraccionamiento</label>
                <input type="text" name="fraccionamiento" class="form-input" value="${revision.fraccionamiento || ''}">
            </div>
        </div>

        <div class="form-columns cols-2">
            <div class="form-group">
                <label class="form-label">Estado <small class="text-muted">(Original: ${revision.estado || 'N/A'})</small></label>
                <select id="estado_id" name="estado_id" class="form-select">
                    <option value="">Seleccione...</option>
                    ${estados.map(e => `<option value="${e.id}" data-nombre="${e.nombre.toLowerCase().trim()}">${e.nombre}</option>`).join('')}
                </select>
            </div>
        
            <div class="form-group">
                <label class="form-label">Municipio <small class="text-muted">(Original: ${revision.municipio || 'N/A'})</small></label>
                <select id="municipio_id" name="municipio_id" class="form-select" disabled>
                    <option value="">Seleccione estado...</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Link de Mapa</label>
            <input type="text" name="mapa_url" class="form-input" value="">
        </div>
    `;

    containerUbicacion.innerHTML = formHtml;
}

function renderTabDetalles(revision, catalogos) {
    const fechaOriginal = revision.fecha_etapa_judicial || '';

    const fechaParaInput = parseDateForInput(fechaOriginal);

    const fechaFieldHtml = `
        <div class="form-group">
            <label for="fecha_etapa_judicial" class="form-label">
                Fecha de Última Etapa Judicial
                ${!fechaParaInput && fechaOriginal ? `<small class="text-muted">(Original: ${fechaOriginal})</small>` : ''}
            </label>

            <input type="date" id="fecha_etapa_judicial" name="fecha_etapa_judicial" class="form-input" value="${fechaParaInput || ''}">
        </div>
    `;

    containerDetalles.innerHTML = `
        <h5 class="form-section-title">Detalles del Inmueble</h5>

        <div class="form-columns cols-3 mb-3">
            <div class="form-group">
                <label class="form-label">Tipo de Vivienda</label>
                <input type="text" name="tipo_vivienda" class="form-input" value="${revision.tipo_vivienda || ''}">
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Inmueble</label>
                <input type="text" name="tipo_inmueble" class="form-input" value="${revision.tipo_inmueble || ''}">
            </div>

            <div class="form-group">
                <label class="form-label">Metros Construcción (m²)</label>
                <input type="text" name="metros" class="form-input" value="${revision.metros || ''}">
            </div>
        </div>
        
        <h5 class="form-section-title">Información Financiera</h5>
        
        <div class="form-columns cols-3">
            <div class="form-group">
                <label class="form-label">Avaluo</label>
                <input type="text" name="avaluo_administradora" class="form-input input-currency" value="${revision.avaluo_administradora || ''}">
            </div>

            <div class="form-group">
                <label class="form-label">Precio Lista</label>
                <input type="text" name="precio_lista" class="form-input input-currency" value="${revision.precio_lista || ''}">
            </div>

            <div class="form-group">
                <label class="form-label">COFINAVIT</label>
                <input type="text" name="cofinavit" class="form-input input-currency" value="${revision.cofinavit || ''}">
            </div>            
        </div>

        <div class="form-group">
            <label class="form-label">Precio de Venta</label>
            <input type="text" name="precio_venta" class="form-input input-currency" value="${revision.precio_venta || ''}">
        </div>

        <h5 class="form-section-title">Información Legal</h5>

        <div class="form-columns cols-3">
            <div class="form-group">
                <label class="form-label">Etapa Judicial Actual</label>
                <input type="text" name="etapa_judicial" class="form-input" value="${revision.etapa_judicial || ''}">
            </div>

            <div class="form-group">
                <label class="form-label">Segudna Etapa Judicial</label>
                <input type="text" name="etapa_judicial_segunda" class="form-input" value="${revision.etapa_judicial_segunda || ''}">
            </div>

            ${fechaFieldHtml} 
        </div>

    `;
}

/**
 * Renderiza la pestaña para subir y previsualizar fotos.
 * @param {Array} fotos - Un array de fotos existentes (en este caso, estará vacío inicialmente).
 */
function renderTabFotos(fotos = []) {
    if (!containerFotos) return;

    containerFotos.innerHTML = `
        <h5 class="form-section-title">Fotografías</h5>
        <p>Sube las imágenes que representarán a esta propiedad en el catálogo final. Estas se guardarán permanentemente cuando valides la propiedad.</p>

        <div style="max-width: 600px; margin: 0 auto;">
            <div class="dropzone custom-dropzone mb-4" id="fotosDropzone">
            
            </div>
        </div>

        <h6 class="mt-4">Fotos a Guardar</h6>
        <div id="galeria-revision" class="photo-gallery-horizontal">
            <p id="galeria-placeholder" class="text-muted">Las fotos que subas aparecerán aquí.</p>
        </div>
    `;
}

// --- Funciones ---

/**
 * Llama a la API para obtener los municipios de un estado específico.
 * @param {string|number} estadoId El ID del estado.
 * @returns {Promise<Array>} Una promesa que se resuelve a un array de municipios.
 */
async function fetchMunicipiosByEstado(estadoId) {
    const municipioSelect = document.getElementById('municipio_id');

    if (!estadoId) {
        municipioSelect.innerHTML = '<option value="">Seleccione un Estado primero...</option>';
        municipioSelect.disabled = true;

        return [];
    }

    municipioSelect.disabled = true;
    municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';

    try {
        const response = await fetchData(`/api/catalogos/municipios?estado_id=${estadoId}`);

        return response.data;
    } catch (error) {
        console.error(`Error al obtener municipios para el estado ${estadoId}:`, error);

        showAlert('No se pudieron cargar los municipios.', 'error');

        municipioSelect.innerHTML = '<option value="">Error al cargar</option>';

        return [];
    }
}

/**
 * Rellena el <select> de municipios con los datos proporcionados.
 * @param {Array} municipios - El array de objetos de municipio.
 * @param {string} [municipioOriginalNombre=''] - El nombre del municipio original para intentar preseleccionar.
 */
function populateMunicipioSelect(municipios, municipioOriginalNombre = '') {
    const municipioSelect = document.getElementById('municipio_id');

    municipioSelect.innerHTML = '<option value="">Seleccione un Municipio...</option>';

    municipios.forEach(municipio => {
        const option = document.createElement('option');

        option.value = municipio.id;
        option.textContent = municipio.nombre;
        option.dataset.nombre = municipio.nombre.toLowerCase().trim();

        municipioSelect.appendChild(option);
    });

    municipioSelect.disabled = false;

    if (municipioOriginalNombre) {
        const nombreNormalizado = municipioOriginalNombre.toLowerCase().trim();
        const municipioMatch = Array.from(municipioSelect.options).find(opt => opt.dataset.nombre === nombreNormalizado);

        if (municipioMatch) {
            municipioSelect.value = municipioMatch.value;
        }
    }
}

function initializeDropzone(tabPane) {
    if (isDropzoneInitialized || !tabPane) {
        return;
    }

    const dropzoneForm = tabPane.querySelector("#fotosDropzone");

    if (!dropzoneForm) {
        console.error("No se encontró el elemento #fotosDropzone dentro de la pestaña activa.");
        return;
    }

    new Dropzone(dropzoneForm, {
        paramName: "file",
        acceptedFiles: "image/jpeg,image/png,image/webp",
        addRemoveLinks: true,
        dictDefaultMessage: "Arrastra tus fotos aquí o haz clic para subir",
        url: `/api/uploads/temp-photo`,

        success: function (file, response) {
            const tempPath = response.data.path;

            renderStagedPhoto(tempPath);

            addHiddenPhotoInput(tempPath);

            this.removeFile(file);
        },
        error: function (file, errorMessage) {
            const message = typeof errorMessage === 'object' ? errorMessage.message : errorMessage;
            showAlert(message || "Error al subir el archivo.", "error");
            this.removeFile(file);
        }
    });

    isDropzoneInitialized = true;
}

/**
 * Añade una miniatura de una foto subida a la galería de previsualización.
 * @param {string} tempPath - La ruta temporal de la imagen.
 */
function renderStagedPhoto(tempPath) {
    const galleryContainer = document.getElementById('galeria-revision');
    const placeholder = document.getElementById('galeria-placeholder');

    if (placeholder) placeholder.style.display = 'none';

    const photoItem = document.createElement('div');

    photoItem.className = 'photo-item';
    photoItem.dataset.path = tempPath;

    photoItem.innerHTML = `
        <a href="${tempPath}" class="glightbox" data-gallery="revision-gallery">
            <img src="${tempPath}" alt="Foto en revisión">
        </a>
        <button class="delete-photo-btn" title="Eliminar esta foto">&times;</button>
    `;

    galleryContainer.appendChild(photoItem);

    if (window.lightbox) window.lightbox.reload();
}

/**
 * Añade un input oculto al formulario principal de validación.
 * @param {string} tempPath - La ruta temporal de la imagen.
 */
function addHiddenPhotoInput(tempPath) {
    const form = document.getElementById('formValidacion');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'fotos_temporales[]';
    input.value = tempPath;
    input.dataset.path = tempPath;
    form.appendChild(input);
}

// --- Lógica Principal ---
async function initPage() {
    try {
        // formContainer.innerHTML = '<p class="text-muted p-5 text-center">Cargando...</p>';

        const [
            revision,
            sucursales,
            administradoras,
            estados
        ] = await Promise.all([
            fetchData(`/api/validacion-cartera/${revisionId}`),
            fetchCatalog('sucursales'),
            fetchCatalog('administradoras'),
            fetchCatalog('estados')
        ]);

        const originalRevisionData = revision.data;

        document.getElementById('pageDescription').textContent = `ID de Revisión: ${revisionId} | Cartera: ${revision.data.cartera_nombre || 'N/A'}`;

        const catalogos = { 'sucursales': sucursales.data, 'administradoras': administradoras.data, 'estados': estados.data };

        renderTabGeneral(revision.data, catalogos);
        renderTabUbicacion(revision.data, catalogos);
        renderTabDetalles(revision.data, catalogos);
        renderTabFotos(revision.data.fotos || []);

        // Manejo de municipios dinámicos
        const estadoSelect = document.getElementById('estado_id');

        if (estadoSelect) {
            estadoSelect.addEventListener('change', async (event) => {
                const estadoId = event.target.value;

                const municipios = await fetchMunicipiosByEstado(estadoId);

                populateMunicipioSelect(municipios);
            });

            const estadoOriginalNombre = (originalRevisionData.estado || '').toLowerCase().trim();
            const municipioOriginalNombre = (originalRevisionData.municipio || '').toLowerCase().trim();

            if (estadoOriginalNombre) {
                const estadoMatch = Array.from(estadoSelect.options).find(opt => opt.dataset.nombre === estadoOriginalNombre);

                if (estadoMatch) {
                    estadoSelect.value = estadoMatch.value;

                    const municipios = await fetchMunicipiosByEstado(estadoSelect.value);

                    populateMunicipioSelect(municipios, municipioOriginalNombre);
                }
            }
        }
    } catch (error) {
        showAlert('No se pudo cargar la información para la validación. ' + error.message, 'error');
        formContainer.innerHTML = '<div class="alert alert-danger">Error al cargar los datos. Por favor, intente recargar la página.</div>';
    }
}

// --- Event Listeners ---
document.addEventListener('DOMContentLoaded', () => {
    // Manejo de tabs
    const tabsContainer = document.getElementById('validacionTabs');

    if (tabsContainer) {
        const tabButtons = tabsContainer.querySelectorAll('.nav-link');
        const tabPanes = document.querySelectorAll('.tab-content .tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('show', 'active'));

                this.classList.add('active');

                const targetPaneSelector = this.getAttribute('data-bs-target');
                const targetPane = document.querySelector(this.getAttribute('data-bs-target'));

                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }

                if (targetPaneSelector === '#tab-fotos') {
                    initializeDropzone(targetPane);
                }

                if (targetPaneSelector === '#tab-detalles') {
                    document.querySelectorAll('.input-currency').forEach(input => {
                        applyCurrencyFormatting(input);
                    });
                }

            });
        });
    }

    initPage();



    formContainer.addEventListener('submit', async (e) => {
        if (e.target.id === 'formValidacion') {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            const data = Object.fromEntries(formData.entries());

            const fotosArray = formData.getAll('fotos_temporales[]');

            data['fotos_temporales'] = fotosArray;

            for (const key in data) {
                if (form.querySelector(`[name="${key}"].input-currency`)) {
                    data[key] = String(data[key]).replace(/[^\d.-]/g, '');
                }
            }

            try {
                const result = await putData(`/api/validacion-cartera/${revisionId}`, data);
                showAlert(result.message, 'success');
                setTimeout(() => window.location.href = `/propiedades/ver/${result.data.propiedad_id}`, 1500);
            } catch (error) {
                console.error(error);
                showAlert(error.message, 'error');
            }
        }
    });

    cardBody.addEventListener('click', (event) => {
        if (event.target.matches('.delete-photo-btn')) {
            const photoItem = event.target.closest('.photo-item');
            const pathToDelete = photoItem.dataset.path;

            photoItem.remove();

            const hiddenInput = document.querySelector(`input[name='fotos_temporales[]'][value='${pathToDelete}']`);
            hiddenInput?.remove();

            // TODO (Opcional): Llamar a una API para borrar el archivo del servidor
            // deleteData(`/api/uploads/temp-photo`, { path: pathToDelete });
        }
    });
});