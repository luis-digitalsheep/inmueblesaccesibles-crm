import { Modal } from "./utils/modal.js";
import { showAlert } from "./utils/alerts.js";

document.addEventListener("DOMContentLoaded", function () {
	// VARIABLES

	const propiedadesTableBody = document.getElementById("propiedadesTableBody");
	const propertyFiltersForm = document.getElementById("propertyFiltersForm");
	const paginationLimitInput = document.getElementById("pagination_limit");
	const paginationOffsetInput = document.getElementById("pagination_offset");
	const prevPageBtn = document.getElementById("prevPageBtn");
	const nextPageBtn = document.getElementById("nextPageBtn");
	const paginationInfoSpan = document.getElementById("paginationInfo");

	let currentPage = 1;
	let itemsPerPage = parseInt(paginationLimitInput.value);
	let totalItems = 0;

	// EVENTOS
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
		const canValidatePropiedad =
			window.App.Permissions.hasPermission("validaciones_cartera.validar");

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
			row.innerHTML += `<td>${propiedad.cartera_nombre}</td>`;
			row.innerHTML += `<td>${propiedad.numero_credito}</td>`;
			row.innerHTML += `<td class="column-direccion">${propiedad.direccion}</td>`;
			row.innerHTML += `<td>${propiedad.estado}</td>`;
			row.innerHTML += `<td>${propiedad.municipio}</td>`;

			row.innerHTML += `<td>$${parseFloat(
				propiedad.precio_lista
			).toLocaleString("es-MX", {
				minimumFractionDigits: 2,
				maximumFractionDigits: 2,
			})}</td>`;

			let estatusDisplay = propiedad.estatus;
			let claseEstatus = "";
			let tooltipInfo = estatusDisplay;

			switch (estatusDisplay) {
				case "Pendiente":
					claseEstatus = "status-apartada";
					if (propiedad.asignacion_cliente_nombre) {
						tooltipInfo += " por: " + propiedad.asignacion_cliente_nombre;
					}
					break;
				case "Validado":
					claseEstatus = "status-vendida";
					if (propiedad.asignacion_cliente_nombre) {
						tooltipInfo += " a: " + propiedad.asignacion_cliente_nombre;
					}
					break;
				case "Rechazado":
					claseEstatus = "status-retirada";
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

			let actionsHtml = ``;

			if (canValidatePropiedad) {
				actionsHtml += `<a href="/validaciones-cartera/validar/${propiedad.id}" class="btn btn-primary btn-sm">Validar</a>`;
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

	// Función para obtener las propiedades en revision
	async function loadProperties() {
		propiedadesTableBody.innerHTML = `<tr><td colspan="12" class="text-center">Cargando propiedades...</td></tr>`;

		const filters = getCurrentFilters();
		const queryString = new URLSearchParams(filters).toString();

		try {
			const response = await fetch(`/api/validaciones-cartera?${queryString}`);
			const result = await response.json();

			console.log("Filtros aplicados:", filters);
			console.log("Resultado de la carga de propiedades:", result);

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
	async function openValidarPropiedad() {
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
