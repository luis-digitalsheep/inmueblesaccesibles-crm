import { Modal } from "../../utils/modal.js";
import { showAlert } from "../../utils/alerts.js";

document.addEventListener("DOMContentLoaded", function () {
	// VARIABLES

	const propiedadesTableBody = document.getElementById("propiedadesTableBody");
	const propertyFiltersForm = document.getElementById("propertyFiltersForm");
	const paginationLimitInput = document.getElementById("pagination_limit");
	const paginationOffsetInput = document.getElementById("pagination_offset");
	const prevPageBtn = document.getElementById("prevPageBtn");
	const nextPageBtn = document.getElementById("nextPageBtn");
	const paginationInfoSpan = document.getElementById("paginationInfo");

	const filterEstadoSelect = document.getElementById("filter_estado");
	const filterMunicipioSelect = document.getElementById("filter_municipio");

	const loadCarteraBtn = document.querySelector("#loadCarteraButton");

	let currentPage = 1;
	let itemsPerPage = parseInt(paginationLimitInput.value);
	let totalItems = 0;

	// EVENTOS

	if (loadCarteraBtn) {
		loadCarteraBtn.addEventListener("click", (event) => {
			event.preventDefault();
			openLoadCartera();
		});
	}

	prevPageBtn.addEventListener("click", function () {
		if (currentPage > 1) {
			const newOffset = parseInt(paginationOffsetInput.value) - itemsPerPage;
			paginationOffsetInput.value = newOffset;
			loadProperties();
		}
	});

	nextPageBtn.addEventListener("click", function () {
		const totalPages = Math.ceil(totalItems / itemsPerPage);
		if (currentPage < totalPages) {
			const newOffset = parseInt(paginationOffsetInput.value) + itemsPerPage;
			paginationOffsetInput.value = newOffset;
			loadProperties();
		}
	});

	propertyFiltersForm.addEventListener("submit", function (event) {
		event.preventDefault();
		paginationOffsetInput.value = 0;
		loadProperties();
	});

	filterEstadoSelect.addEventListener("change", function () {
		const estadoId = this.value;
		if (estadoId) {
			loadMunicipiosByEstado(estadoId);
		} else {
			filterMunicipioSelect.innerHTML =
				'<option value="">Selecciona un Estado primero</option>';
			filterMunicipioSelect.disabled = true;
		}
	});

	// FUNCIONES

	// Función para limpiar y rellenar el select de municipios
	function populateMunicipios(municipios, selectedMunicipioId = null) {
		filterMunicipioSelect.innerHTML = '<option value="">Todos</option>';
		municipios.forEach((municipio) => {
			const option = document.createElement("option");
			option.value = municipio.id;
			option.textContent = municipio.nombre;
			if (selectedMunicipioId && municipio.id == selectedMunicipioId) {
				option.selected = true;
			}
			filterMunicipioSelect.appendChild(option);
		});
		filterMunicipioSelect.disabled = false;
	}

	// Función para cargar municipios desde la API
	async function loadMunicipiosByEstado(estadoId, selectedMunicipioId = null) {
		filterMunicipioSelect.innerHTML =
			'<option value="">Cargando municipios...</option>';
		filterMunicipioSelect.disabled = true;

		try {
			const response = await fetch(
				`/api/catalogos/municipios?estado_id=${estadoId}`
			); // Usamos la nueva API
			const result = await response.json();

			if (result.status === "success") {
				populateMunicipios(result.data, selectedMunicipioId);
			} else {
				showAlert("Error al cargar municipios: " + result.message, "error");
				filterMunicipioSelect.innerHTML =
					'<option value="">Error al cargar</option>';
			}
		} catch (error) {
			console.error("Fallo de red al cargar municipios:", error);
			showAlert(
				"No se pudieron cargar los municipios. Error de conexión.",
				"error"
			);
			filterMunicipioSelect.innerHTML =
				'<option value="">Error de conexión</option>';
		}
	}

	// Mostrar las propiedades en la tabla
	function renderTable(propiedades) {
		const canDeletePropiedad = window.App.Permissions.hasPermission(
			"propiedades.eliminar"
		);

		propiedadesTableBody.innerHTML = "";

		if (propiedades.length === 0) {
			const colspan = 13;

			propiedadesTableBody.innerHTML = `
        <tr>
          <td colspan="${colspan}" class="text-center">No hay propiedades registradas que coincidan con los filtros.</td>
        </tr>
      `;
			return;
		}

		propiedades.forEach((propiedad) => {
			const row = document.createElement("tr");

			row.innerHTML += `<td>${propiedad.id}</td>`;
			row.innerHTML += `<td>${propiedad.numero_credito}</td>`;
			row.innerHTML += `<td class="column-direccion">${propiedad.direccion}</td>`;
			row.innerHTML += `<td>${propiedad.estado_nombre}</td>`;
			row.innerHTML += `<td>${propiedad.municipio_nombre}</td>`;

			row.innerHTML += `<td>$${parseFloat(
				propiedad.precio_lista
			).toLocaleString("es-MX", {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			})}</td>`;

			row.innerHTML += `<td>$${parseFloat(
				propiedad.precio_venta
			).toLocaleString("es-MX", {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			})}</td>`;

			let estatusDisplay = propiedad.estatus_disponibilidad;
			let claseEstatus = "";
			let tooltipInfo = estatusDisplay;

			switch (estatusDisplay) {
				case "Apartada":
					claseEstatus = "status-apartada";
					if (propiedad.asignacion_cliente_nombre) {
						tooltipInfo += " por: " + propiedad.asignacion_cliente_nombre;
					}
					break;
				case "Vendida":
					claseEstatus = "status-vendida";
					if (propiedad.asignacion_cliente_nombre) {
						tooltipInfo += " a: " + propiedad.asignacion_cliente_nombre;
					}
					break;
				case "En Proceso de Cambio":
					claseEstatus = "status-en-proceso-cambio";
					break;
				case "Retirada":
					claseEstatus = "status-retirada";
					break;
				default: // Disponible
					claseEstatus = "status-disponible";
					break;
			}

			row.innerHTML += `
        <td>
          <span class="status-badge ${claseEstatus}" title="${tooltipInfo}">
            ${estatusDisplay}
          </span>
        </td>
      `;

			row.innerHTML += `<td>${propiedad.sucursal_nombre}</td>`;
			row.innerHTML += `<td>${propiedad.administradora_nombre}</td>`;

			row.innerHTML += `<td><a class="btn btn-info btn-sm" href="${propiedad.mapa_url}" target="_blank">Ver Mapa</a></td>`;

			let actionsHtml = `<a href="/propiedades/ver/${propiedad.id}" class="btn btn-primary btn-sm">Ver</a>`;

			if (canDeletePropiedad) {
				actionsHtml += `<a href="#" data-id="${propiedad.id}" class="btn btn-danger btn-sm delete-btn">Eliminar</a>`;
			}

			row.innerHTML += `<td class="actions-column">${actionsHtml}</td>`;

			propiedadesTableBody.appendChild(row);
		});
	}

	// Función para obtener los filtros seleccionados
	function getCurrentFilters() {
		const filters = {};
		const formData = new FormData(propertyFiltersForm);
		for (const [key, value] of formData.entries()) {
			if (value !== "") {
				filters[key] = value;
			}
		}
		filters.limit = paginationLimitInput.value;
		filters.offset = paginationOffsetInput.value;

		return filters;
	}

	// Función para obtener las propiedades
	async function loadProperties() {
		propiedadesTableBody.innerHTML = `<tr><td colspan="12" class="text-center">Cargando propiedades...</td></tr>`;
		const filters = getCurrentFilters();
		const queryString = new URLSearchParams(filters).toString();

		try {
			const response = await fetch(`/api/propiedades?${queryString}`);
			const result = await response.json();

			if (result.status === "success") {
				renderTable(result.data);
				totalItems = result.total;
				itemsPerPage = result.limit;
				updatePaginationControls();
			} else {
				showAlert(result.message, "error");
				propiedadesTableBody.innerHTML = `<tr><td colspan="12" class="text-center">Error al cargar propiedades: ${result.message}</td></tr>`;
			}
		} catch (error) {
			console.error("Error al cargar propiedades:", error);
			showAlert(
				"No se pudieron cargar las propiedades. Intenta de nuevo más tarde.",
				"error"
			);
			propiedadesTableBody.innerHTML = `<tr><td colspan="12" class="text-center">No se pudieron cargar las propiedades.</td></tr>`;
		}
	}

	// Función para actualizar los controles de paginación
	function updatePaginationControls() {
		const totalPages = Math.ceil(totalItems / itemsPerPage);

		currentPage =
			Math.floor(parseInt(paginationOffsetInput.value) / itemsPerPage) + 1;

		paginationInfoSpan.textContent = `Página ${currentPage} de ${totalPages}`;
		prevPageBtn.disabled = currentPage === 1;
		nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;

		if (totalPages === 0) {
			paginationInfoSpan.textContent = `Página 0 de 0`;
		}
	}

	// Función para inicializar el módulo
	async function initPropertiesModule() {
		await window.App.Permissions.waitForPermissions();

		loadProperties();
	}

	async function getSucursales() {
		try {
			const response = await fetch("/api/sucursales");
			const result = await response.json();

			if (result.status === "success") {
				return result.data;
			}
		} catch (error) {
			console.error("Error al cargar sucursales:", error);

			return [];
		}
	}

	async function getAdministradoras() {
		try {
			const response = await fetch("/api/administradoras");
			const result = await response.json();

			if (result.status === "success") {
				return result.data;
			}
		} catch (error) {
			console.error("Error al cargar administradoras:", error);

			return [];
		}
	}

	// Función para abrir el modal de carga de cartera
	async function openLoadCartera() {
		const formHtml = `
			<div>
				<div class="filters-grid"> 
					<div class="form-group">
						<label for="carga_codigo_cartera">Codigo de Cartera:</label>
						<input type="text" id="carga_codigo_cartera" name="codigo_cartera" class="form-input">
					</div>

					<div class="form-group">
						<label for="carga_nombre_cartera">Nombre de Cartera:</label>
						<input type="text" id="carga_nombre_cartera" name="nombre_cartera" class="form-input">
					</div>
				</div>

				<div class="filters-grid">
					<div class="form-group">
						<label for="carga_sucursal_id">Sucursal:</label>
						<select id="carga_sucursal_id" name="sucursal_id" class="form-select">
							<option disabled selected value="">Selecciona una Sucursal</option>
						</select>
					</div>

					<div class="form-group">
						<label for="carga_administradora_id">Administradora:</label>
						<select id="carga_administradora_id" name="administradora_id" class="form-select" disabled>
							<option value="">Selecciona una Administradora</option>
						</select>
					</div>
				</div>

				<form id="cargaForm" action="/api/propiedades/upload-cartera" class="dropzone custom-dropzone" style="display: none;">
					<div class="dz-message needsclick">
						Arrastra y suelta tu archivo Excel aquí o haz clic para seleccionarlo.<br>
						<span class="note">(Solo archivos .xlsx o .csv)</span>
					</div>
				</form>
				<div id="dropzonePreview"></div>
			</div>
		`;

		Modal.show("Cargar Cartera", formHtml, {
			size: "lg",
			confirmBtnText: "Subir archivo",
			cancelBtnText: "Cancelar",
			onConfirm: (modalBodyElement) => {
				const form = modalBodyElement.querySelector("#cargaForm");

				if (form) {
					const formData = new FormData(form);
					submitPropertyForm(formData);
				}

				console.log("Cartera cargada correctamente.");

				showAlert("Cartera cargada correctamente.", "success");

				Modal.hide();
			},
			onCancel: () => {
				console.log("Creación de propiedad cancelada.");
			},

			onContentReady: (modalBodyElement) => {
				const cargaCodigoCartera = modalBodyElement.querySelector(
					"#carga_codigo_cartera"
				);

				const cargaNombreCartera = modalBodyElement.querySelector(
					"#carga_nombre_cartera"
				);

				const cargaSucursalId =
					modalBodyElement.querySelector("#carga_sucursal_id");

				const cargaAdministradoraId = modalBodyElement.querySelector(
					"#carga_administradora_id"
				);

				const dropzoneFormElement = modalBodyElement.querySelector(".dropzone");

				cargaSucursalId.addEventListener("change", () => {
					cargaAdministradoraId.disabled = false;
					cargaAdministradoraId.value = "";
				});

				cargaAdministradoraId.addEventListener("change", () => {
					cargaAdministradoraId.disabled = false;
					cargaAdministradoraId.value = "";

					dropzoneFormElement.style.display = "block";
				});

				if (dropzoneFormElement) {
					Dropzone.autoDiscover = false;

					const myDropzone = new Dropzone(dropzoneFormElement, {
						url: "/api/carteras/upload",
						paramName: "file",
						maxFilesize: 5, // MB
						acceptedFiles: ".xlsx,.csv",
						addRemoveButton: true,
						dictDefaultMessage:
							"Arrastra tu archivo aquí o haz clic para seleccionar.",
						dictRemoveFile: "Quitar archivo",
						dictCancelUpload: "Cancelar subida",
						dictInvalidFileType: "Solo se permiten archivos .xlsx o .csv.",
						dictFileTooBig:
							"El archivo es demasiado grande ({{filesize}}MB). Tamaño máximo: {{maxFilesize}}MB.",

						init: function () {
							this.on("success", function (file, response) {
								showAlert(
									"Archivo subido con éxito. ¡Iniciando revisión!",
									"success"
								);

								Modal.hide();
							});
							this.on("error", function (file, { message }, xhr) {
								console.error("Error al subir archivo:", message, xhr);
								showAlert(`Error al subir archivo: ${message}`, "error");

								// Mensaje de error personalizado en dz-error-message
								const dzErrorMessage =
									document.querySelector(".dz-error-message");
								if (dzErrorMessage) {
									dzErrorMessage.textContent = message;
								}

								const markButton = document.querySelector(".dz-error-mark");

								if (markButton) {
									console.log(markButton);
									markButton.addEventListener("click", function () {
										myDropzone.removeAllFiles();
									});
								}
							});

							this.on("addedfile", function (file) {
								if (this.files.length > 1) {
									this.removeFile(this.files[0]);
								}
							});

							this.on("sending", function (file, xhr, formData) {
								formData.append(
									"carga_codigo_cartera",
									cargaCodigoCartera.value
								);
								formData.append(
									"carga_nombre_cartera",
									cargaNombreCartera.value
								);
								formData.append("carga_sucursal_id", cargaSucursalId.value);
								formData.append(
									"carga_administradora_id",
									cargaAdministradoraId.value
								);
							});
						},
					});
				}

				// cargar catalogos

				const loadCargaSucursales = async () => {
					await getSucursales().then((sucursales) => {
						sucursales.forEach((sucursal) => {
							const option = document.createElement("option");
							option.value = sucursal.id;
							option.textContent = sucursal.nombre;
							cargaSucursalId.appendChild(option);
						});
					});
				};

				const loadCargaAdministradoras = async () => {
					await getAdministradoras().then((administradoras) => {
						administradoras.forEach((administradora) => {
							const option = document.createElement("option");
							option.value = administradora.id;
							option.textContent = administradora.nombre;
							cargaAdministradoraId.appendChild(option);
						});
					});
				};

				loadCargaSucursales();
				loadCargaAdministradoras();
			},
		});
	}

	// INICIAR

	initPropertiesModule();
});
