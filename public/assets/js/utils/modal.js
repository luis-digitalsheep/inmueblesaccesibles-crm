export const Modal = {
	_modalElement: null, // div principal del modal (#appModal)
	_modalTitleElement: null, // Título del modal (.app-modal-title)
	_modalBodyElement: null, // Contenido del modal (.app-modal-body)
	_modalFooterElement: null, // Pie del modal (.app-modal-footer)
	_modalCloseBtn: null, // Botón de cerrar (.app-modal-close-btn)
	_modalOverlay: null, // Overlay del modal (.app-modal-overlay)
	_activeConfirmCallback: null, // Callback para el botón de confirmar
	_activeCancelCallback: null, // Callback para el botón de cancelar
	_activeCloseCallback: null, // Callback para cerrar con X, overlay o Escape

	init() {
		this._modalElement = document.getElementById("appModal");

		if (!this._modalElement) {
			console.error(
				"Error: No se encontró el elemento #appModal. Asegúrate de incluir modal_template.php."
			);
			return;
		}

		this._modalTitleElement =
			this._modalElement.querySelector(".app-modal-title");
		this._modalBodyElement =
			this._modalElement.querySelector(".app-modal-body");
		this._modalFooterElement =
			this._modalElement.querySelector(".app-modal-footer");
		this._modalCloseBtn = this._modalElement.querySelector(
			".app-modal-close-btn"
		);
		this._modalOverlay = this._modalElement.querySelector(".app-modal-overlay");

		if (this._modalCloseBtn) {
			this._modalCloseBtn.addEventListener("click", () => this.hide());
		}

		if (this._modalOverlay) {
			this._modalOverlay.addEventListener("click", () => this.hide());
		}

		document.addEventListener("keydown", this._handleKeydown);
	},

	// Manejador de teclado para cerrar con Escape
	_handleKeydown: (e) => {
		if (
			e.key === "Escape" &&
			Modal._modalElement &&
			Modal._modalElement.classList.contains("is-active")
		) {
			Modal.hide();
		}
	},

	/**
	 * Muestra el modal con el contenido HTML especificado.
	 * @param {string} title Título del modal.
	 * @param {string} contentHtml Contenido HTML como string a inyectar en el body del modal.
	 * @param {object} [options={}] Opciones: { size: 'sm'|'lg'|'xl', showFooter: boolean, onConfirm: function, onCancel: function, onClose: function, confirmBtnText: string, cancelBtnText: string, onContentReady: function }
	 */
	show(title, contentHtml, options = {}) {
		if (!this._modalElement) {
			console.error(
				"Modal no inicializado. Asegúrate de llamar Modal.init() en DOMContentLoaded."
			);
			return;
		}

		// Limpiar callbacks anteriores
		this._activeConfirmCallback = null;
		this._activeCancelCallback = null;
		this._activeCloseCallback = null;

		this._modalTitleElement.textContent = title;
		this._modalBodyElement.innerHTML = contentHtml;

		// Limpiar clases de tamaño anteriores y aplicar nueva
		const modalContent = this._modalElement.querySelector(".app-modal-content");

		modalContent.classList.remove("modal-sm", "modal-lg", "modal-xl");

		if (options.size) {
			modalContent.classList.add(`modal-${options.size}`);
		}

		// Configurar footer y botones
		const confirmBtn = this._modalFooterElement.querySelector(
			".app-modal-confirm-btn"
		);

		const cancelBtn = this._modalFooterElement.querySelector(
			".app-modal-cancel-btn"
		);

		if (options.showFooter === false) {
			this._modalFooterElement.style.display = "none";
		} else {
			this._modalFooterElement.style.display = "flex";

			confirmBtn.textContent = options.confirmBtnText || "Aceptar";
			cancelBtn.textContent = options.cancelBtnText || "Cancelar";

			confirmBtn.onclick = null;
			cancelBtn.onclick = null;

			if (options.onConfirm && typeof options.onConfirm === "function") {
				this._activeConfirmCallback = options.onConfirm;

				confirmBtn.onclick = () => {
					// El callback de onConfirm no cierra el modal automáticamente
					// para permitir validaciones antes del cierre.
					// Debe llamarse Modal.hide() manualmente después de la confirmación.
					this._activeConfirmCallback(this._modalBodyElement); // Pasa el body del modal para que el callback acceda al form/contenido
				};
			} else {
				confirmBtn.onclick = () => this.hide();
			}

			if (options.onCancel && typeof options.onCancel === "function") {
				this._activeCancelCallback = options.onCancel;

				cancelBtn.onclick = () => {
					this._activeCancelCallback();
					this.hide();
				};
			} else {
				cancelBtn.onclick = () => this.hide();
			}
		}

		this._activeCloseCallback = options.onClose || null;

		// Mostrar el modal
		this._modalElement.classList.add("is-active");
		document.body.style.overflow = "hidden";

		// Permite que otras funciones actúen sobre el nuevo DOM
		if (
			options.onContentReady &&
			typeof options.onContentReady === "function"
		) {
			// Un pequeño setTimeout asegura que el navegador tenga tiempo de parsear el HTML
			// antes de que el callback intente manipularlo.
			setTimeout(() => {
				options.onContentReady(this._modalBodyElement); // Pasa el body del modal
			}, 50);
		}
	},

	/**
	 * Oculta el modal.
	 */
	hide() {
		if (!this._modalElement) return;

		this._modalElement.classList.remove("is-active");

		setTimeout(() => {
			document.body.style.overflow = "";

			// Ejecutar callback de cierre general si existe
			if (
				this._activeCloseCallback &&
				typeof this._activeCloseCallback === "function"
			) {
				this._activeCloseCallback();
			}

			this._modalBodyElement.innerHTML = "";
			// Limpiar callbacks y referencias

			this._activeConfirmCallback = null;
			this._activeCancelCallback = null;
			this._activeCloseCallback = null;

			this._modalElement
				.querySelector(".app-modal-content")
				.classList.remove("modal-sm", "modal-lg", "modal-xl");
		}, 500);
	},

	/**
	 * Devuelve el elemento body del modal para acceder a su contenido.
	 * @returns {HTMLElement} El elemento body del modal.
	 */
	getBodyElement() {
		return this._modalBodyElement;
	},

	/**
	 * Muestra un modal de confirmación simple.
	 * @param {string} title
	 * @param {string} message
	 * @param {function} onConfirm Callback al confirmar.
	 * @param {function} [onCancel=null] Callback al cancelar.
	 */
	confirm(title, message, onConfirm, onCancel = null) {
		const contentHtml = `<p class="text-center">${message}</p>`;

		this.show(title, contentHtml, {
			size: "sm",
			confirmBtnText: "Confirmar",
			cancelBtnText: "Cancelar",
			onConfirm: (modalBody) => {
				onConfirm();
				this.hide();
			},
			onCancel: onCancel,
		});

		const cancelButton = this._modalFooterElement.querySelector(
			".app-modal-cancel-btn"
		);

		const confirmButton = this._modalFooterElement.querySelector(
			".app-modal-confirm-btn"
		);

		if (cancelButton) cancelButton.style.display = "inline-block";
		if (confirmButton) confirmButton.style.width = "auto";
	},

	/**
	 * Muestra un modal de alerta simple (solo botón de OK).
	 * @param {string} title
	 * @param {string} message
	 * @param {function} [onClose=null] Callback al cerrar o hacer OK.
	 */
	alert(title, message, onClose = null) {
		const contentHtml = `<p class="text-center">${message}</p>`;
		this.show(title, contentHtml, {
			size: "sm",
			showFooter: true,
			confirmBtnText: "OK",
			cancelBtnText: "Cancelar",
			onConfirm: (modalBody) => {
				if (onClose) onClose();
				this.hide();
			},
			onCancel: () => {
				if (onClose) onClose();
				this.hide();
			},
		});

		const cancelButton = this._modalFooterElement.querySelector(
			".app-modal-cancel-btn"
		);

		const confirmButton = this._modalFooterElement.querySelector(
			".app-modal-confirm-btn"
		);

		if (cancelButton) cancelButton.style.display = "none";
		if (confirmButton) confirmButton.style.width = "100%";
	},
};

// Inicializar el modal cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", () => {
	Modal.init();
});
