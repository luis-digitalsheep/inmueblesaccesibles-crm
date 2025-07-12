import { showAlert } from "../../utils/alerts.js";
import { fetchData, postData, putData, fetchCatalog } from "../../utils/api.js";

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

	// Mostrar las propiedades en la tabla
	function renderTable(propiedades) {
		const canValidatePropiedad = window.App.Permissions.hasPermission("validaciones_cartera.validar");

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

			if (canValidatePropiedad && propiedad.estatus === "Pendiente") {
				actionsHtml += `<a href="/validacion-cartera/validar/${propiedad.id}" class="btn btn-primary btn-sm">Validar</a>`;
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
			const result = await fetchData(`/api/validacion-cartera?${queryString}`);

			console.log("Filtros aplicados:", filters);
			console.log("Resultado de la carga de propiedades:", result);

			if (result.status !== "success") {
				return;
			}

			renderTable(result.data);

			totalItems = result.total;
			itemsPerPage = result.limit;

			updatePaginationControls();
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

		currentPage = Math.floor(parseInt(paginationOffsetInput.value) / itemsPerPage) + 1;

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

	// INICIAR

	initPropertiesModule();
});
