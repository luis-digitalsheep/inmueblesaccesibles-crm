const formHtml = `
  <form id="createPropertyForm" class="app-form" action="/api/propiedades/guardar" method="POST">
    <div class="filters-grid"> 

      <div class="form-group">
        <label for="modal_numero_credito" class="form-label">Número de Crédito:</label>
        <input type="text" id="modal_numero_credito" name="numero_credito" class="form-input">
      </div>

      <div class="form-group full-width">
        <label for="modal_direccion" class="form-label">Dirección:</label>
        <input type="text" id="modal_direccion" name="direccion" class="form-input" required>
      </div>
      
      <div class="form-group full-width">
        <label for="modal_comentarios_admin" class="form-label">Comentarios Admin:</label>
        <textarea id="modal_comentarios_admin" name="comentarios_admin" class="form-textarea"></textarea>
      </div>
      
      <div class="form-group">
        <label for="modal_mapa_url" class="form-label">Link Mapa:</label>
        <input type="url" id="modal_mapa_url" name="mapa_url" class="form-input">
      </div>
      
      <div class="form-group">
        <label for="modal_admin_id" class="form-label">Administradora (carga inicial):</label>
        <select id="modal_admin_id" name="administradora_id" class="form-select" required>
          <option value="">Selecciona una Administradora</option>
        </select>
      </div>

      <input type="hidden" name="estatus_disponibilidad" value="Disponible">

    </div>
  </form>
`;

Modal.show("Cargar Cartera", formHtml, {
	size: "lg",
	confirmBtnText: "Subir archivo",
	cancelBtnText: "Cancelar",

	onConfirm: (modalBodyElement) => {
		const form = modalBodyElement.querySelector("#createPropertyForm");

		if (form) {
			const formData = new FormData(form);
			submitPropertyForm(formData);
		}
	},

	onCancel: () => {
		console.log("Creación de propiedad cancelada.");
	},

	onContentReady: (modalBodyElement) => {
		// Procesos al agregar dinamicamente el contenido del modal

		// Ejemplo: Inicializar Dropzone
		const dropzoneFormElement = modalBodyElement.querySelector(".dropzone");
		if (dropzoneFormElement) {
			Dropzone.autoDiscover = false;

			// Crear una nueva instancia de Dropzone
			const myDropzone = new Dropzone(dropzoneFormElement, {
				url: "/api/propiedades/upload-cartera",
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
				// autoProcessQueue: false, // Puedes establecerlo en false si quieres que el botón del modal inicie la subida
				// y luego Dropzone.processQueue()

				init: function () {
					this.on("success", function (file, response) {
						console.log("Archivo subido con éxito:", response);

						showAlert(
							"Archivo subido con éxito. ¡Iniciando revisión!",
							"success"
						);
						// Aquí puedes redirigir al módulo de revisión o actualizar la UI
						Modal.hide(); // Cerrar el modal al éxito de la subida
						// O si el procesamiento es asincrónico, tal vez mostrar un mensaje y no cerrar el modal.
					});
					this.on("error", function (file, message, xhr) {
						console.error("Error al subir archivo:", message, xhr);
						showAlert(`Error al subir archivo: ${message}`, "error");
						// Opcional: Remover el archivo de la vista previa de Dropzone.
						// this.removeFile(file);
					});
					this.on("addedfile", function (file) {
						if (this.files.length > 1) {
							this.removeFile(this.files[0]);
						}
					});
					this.on("queuecomplete", function () {
						// Una vez que la cola de subida está completa (si autoProcessQueue es true)
						// myDropzone.removeAllFiles(true); // Opcional: Limpiar después de subir
					});
				},
			});
		}
	},
});
