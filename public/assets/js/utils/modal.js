class ModalManager {
	constructor() {
		this._modalElement = null;
		this._modalTitleElement = null;
		this._modalBodyElement = null;
		this._modalFooterElement = null;
		this._confirmBtn = null;
		this._cancelBtn = null;
		this._modalCloseBtn = null;
		this._modalOverlay = null;

		this._activeConfirmCallback = null;
		this._activeCancelCallback = null;
		this._activeCloseCallback = null;
	}

	/**
	 * Inicializa el componente del modal. Busca los elementos del DOM y enlaza los eventos.
	 * Debe ser llamado cuando el DOM esté completamente cargado.
	 */
	init() {
		this._modalElement = document.getElementById("appModal");

		if (!this._modalElement) {
			console.error("Error: No se encontró el elemento #appModal. Asegúrate de que el template del modal esté incluido en la página.");
			return;
		}

		this._modalTitleElement = this._modalElement.querySelector(".app-modal-title");
		this._modalBodyElement = this._modalElement.querySelector(".app-modal-body");
		this._modalFooterElement = this._modalElement.querySelector(".app-modal-footer");
		this._confirmBtn = this._modalElement.querySelector(".app-modal-confirm-btn");
		this._cancelBtn = this._modalElement.querySelector(".app-modal-cancel-btn");
		this._modalCloseBtn = this._modalElement.querySelector(".app-modal-close-btn");
		this._modalOverlay = this._modalElement.querySelector(".app-modal-overlay");

		this._bindEvents();
	}

	/**
	 * Enlaza los eventos permanentes del modal.
	 * @private
	 */
	_bindEvents() {
		this._modalCloseBtn?.addEventListener("click", () => this.hide());
		this._modalOverlay?.addEventListener("click", () => this.hide());

		document.addEventListener("keydown", this._handleKeydown.bind(this));
	}

	/**
	 * Maneja el evento de presionar una tecla (para cerrar con 'Escape').
	 * @param {KeyboardEvent} e - El evento del teclado.
	 * @private
	 */
	_handleKeydown(e) {
		if (e.key === "Escape" && this._modalElement?.classList.contains("is-active")) {
			this.hide();
		}
	}

	/**
	 * Muestra el modal con el contenido y opciones especificadas.
	 * @param {string} title Título del modal.
	 * @param {string} contentHtml Contenido HTML a inyectar.
	 * @param {object} [options={}] Opciones de configuración.
	 */
	show(title, contentHtml, options = {}) {
		if (!this._modalElement) {
			console.error("Modal no inicializado. Llama a Modal.init() primero.");
			return;
		}

		// Limpiar callbacks de la sesión anterior
		this._activeConfirmCallback = options.onConfirm || null;
		this._activeCancelCallback = options.onCancel || null;
		this._activeCloseCallback = options.onClose || null;

		// Rellenar contenido
		this._modalTitleElement.textContent = title;
		this._modalBodyElement.innerHTML = contentHtml;

		// Ajustar tamaño del modal
		const modalContent = this._modalElement.querySelector(".app-modal-content");

		modalContent.classList.remove("modal-sm", "modal-lg", "modal-xl");

		if (options.size) {
			modalContent.classList.add(`modal-${options.size}`);
		}

		// Configurar el footer
		this._configureFooter(options);

		// Mostrar el modal
		this._modalElement.classList.add("is-active");
		document.body.style.overflow = "hidden";

		if (typeof options.onContentReady === "function") {
			setTimeout(() => options.onContentReady(this._modalBodyElement), 50);
		}
	}

	/**
	 * Configura el footer, botones y sus callbacks.
	 * @param {object} options Opciones pasadas al método show.
	 * @private
	 */
	_configureFooter(options) {
		if (options.showFooter === false) {
			this._modalFooterElement.style.display = "none";
			return;
		}

		this._modalFooterElement.style.display = "flex";
		this._confirmBtn.textContent = options.confirmBtnText || "Aceptar";
		this._cancelBtn.textContent = options.cancelBtnText || "Cancelar";

		// Re-asignamos los listeners para evitar acumulación
		this._confirmBtn.onclick = () => {
			if (this._activeConfirmCallback) {
				this._activeConfirmCallback(this._modalBodyElement);
			} else {
				this.hide();
			}
		};

		this._cancelBtn.onclick = () => {
			if (this._activeCancelCallback) {
				this._activeCancelCallback();
			}
			this.hide();
		};

		this._cancelBtn.style.display = options.showCancelButton === false ? 'none' : 'inline-block';
		this._confirmBtn.style.width = options.showCancelButton === false ? '100%' : 'auto';
	}

	/**
	 * Oculta el modal y limpia su estado.
	 */
	hide() {
		if (!this._modalElement || !this._modalElement.classList.contains("is-active")) return;

		this._modalElement.classList.remove("is-active");

		if (typeof this._activeCloseCallback === "function") {
			this._activeCloseCallback();
		}

		setTimeout(() => {
			document.body.style.overflow = "";
			this._modalBodyElement.innerHTML = "";

			this._activeConfirmCallback = null;
			this._activeCancelCallback = null;
			this._activeCloseCallback = null;
		}, 300);
	}

	/**
	 * Muestra un modal de confirmación simple.
	 * @param {string} title Título de la confirmación.
	 * @param {string} message Mensaje de la confirmación.
	 * @param {function} onConfirm Callback a ejecutar si se confirma.
	 */
	confirm(title, message, onConfirm) {
		this.show(title, `<p>${message}</p>`, {
			size: 'sm',
			confirmBtnText: 'Confirmar',
			cancelBtnText: 'Cancelar',
			onConfirm: () => {
				onConfirm();
				this.hide();
			}
		});
	}

	/**
	 * Muestra un modal de alerta simple con un solo botón de 'OK'.
	 * @param {string} title Título de la alerta.
	 * @param {string} message Mensaje de la alerta.
	 * @param {function} [onClose=null] Callback opcional al cerrar.
	 */
	alert(title, message, onClose = null) {
		this.show(title, `<p>${message}</p>`, {
			size: 'sm',
			showCancelButton: false,
			confirmBtnText: 'OK',
			onConfirm: onClose,
		});
	}

	/**
	 * Activa/desactiva un estado de carga en el botón de confirmar del modal.
	 * @param {boolean} isLoading True para mostrar estado de carga, false para quitarlo.
	 * @param {string} [loadingText='Guardando...'] Texto a mostrar durante la carga.
	 */
	setLoading(isLoading, loadingText = 'Guardando...') {
		if (!this._confirmBtn) return;
		if (isLoading) {
			this._confirmBtn.disabled = true;
			this._confirmBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${loadingText}`;
		} else {
			this._confirmBtn.disabled = false;
			// El texto se restaurará en la próxima llamada a .show()
			// Pero podríamos guardarlo para restaurarlo aquí si fuera necesario
			this._confirmBtn.innerHTML = this._confirmBtn.textContent; // Restaura el texto original (ej. 'Aceptar')
		}
	}
}

export const Modal = new ModalManager();

document.addEventListener("DOMContentLoaded", () => {
	Modal.init();
});