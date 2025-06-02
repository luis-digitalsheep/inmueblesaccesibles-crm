const dropzoneElement = document.getElementById("myDropzone");

if (dropzoneElement) {
	// Prevenir comportamiento por defecto para que el drop funcione
	["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
		dropzoneElement.addEventListener(eventName, preventDefaults, false);
		document.body.addEventListener(eventName, preventDefaults, false);
	});

	function preventDefaults(e) {
		e.preventDefault();
		e.stopPropagation();
	}

	// Añadir clase cuando se arrastra sobre el elemento
	dropzoneElement.addEventListener(
		"dragenter",
		() => {
			dropzoneElement.classList.add("is-dragover");
		},
		false
	);

	dropzoneElement.addEventListener(
		"dragover",
		() => {
			// Ya está en dragenter, pero es bueno tenerlo por si acaso o si se necesita lógica específica aquí
			if (!dropzoneElement.classList.contains("is-dragover")) {
				dropzoneElement.classList.add("is-dragover");
			}
		},
		false
	);

	// Quitar clase cuando se sale del elemento o se suelta el archivo
	dropzoneElement.addEventListener(
		"dragleave",
		() => {
			// Ojo: dragleave se dispara si entras a un hijo.
			// Una mejor comprobación sería ver si relatedTarget está fuera del dropzone.
			// Para simplificar, lo dejamos así, pero en implementaciones complejas se refina.
			dropzoneElement.classList.remove("is-dragover");
		},
		false
	);

	dropzoneElement.addEventListener(
		"drop",
		(e) => {
			dropzoneElement.classList.remove("is-dragover");
			// Aquí manejarías los archivos: e.dataTransfer.files
			console.log("Archivos soltados:", e.dataTransfer.files);
			// Podrías añadir .has-success o .has-error aquí después de procesar
		},
		false
	);

	// Si también quieres que funcione al hacer clic:
	const fileInput = document.createElement("input");
	fileInput.setAttribute("type", "file");
	fileInput.setAttribute("multiple", ""); // Si quieres múltiples archivos
	fileInput.style.display = "none"; // Ocultar el input real
	dropzoneElement.appendChild(fileInput);

	dropzoneElement.addEventListener("click", () => {
		fileInput.click(); // Abrir el diálogo de archivo
	});

	fileInput.addEventListener("change", (e) => {
		// Aquí manejas los archivos seleccionados: e.target.files
		console.log("Archivos seleccionados:", e.target.files);
	});
}
