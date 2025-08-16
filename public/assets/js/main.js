import { fetchData, postData } from './utils/api.js';

document.addEventListener("DOMContentLoaded", function () {
	// Lógica para el dropdown de usuario
	const userDropdown = document.querySelector(".user-dropdown");

	if (userDropdown) {
		const userInfo = userDropdown.querySelector(".user-info");
		const dropdownMenu = userDropdown.querySelector(".dropdown-menu");

		userInfo.addEventListener("click", function (event) {
			event.preventDefault();
			dropdownMenu.classList.toggle("show");
			userDropdown.classList.toggle("active");
		});

		document.addEventListener("click", function (event) {
			if (!userDropdown.contains(event.target)) {
				dropdownMenu.classList.remove("show");
				userDropdown.classList.remove("active");
			}
		});
	}

	window.alertContainer = document.getElementById("alert-message-container");

	const sidebarToggleBtn = document.querySelector(".sidebar-toggle-btn");
	const sidebar = document.querySelector(".sidebar");
	const contentWrapper = document.querySelector(".content-wrapper");

	if (sidebarToggleBtn && sidebar && contentWrapper) {
		sidebarToggleBtn.addEventListener("click", function () {
			sidebar.classList.toggle("collapsed");
			contentWrapper.classList.toggle("sidebar-collapsed");
		});
	}

	const submenuToggles = document.querySelectorAll(".submenu-toggle");

	submenuToggles.forEach((toggle) => {
		toggle.addEventListener("click", function (event) {
			event.preventDefault();
			const parentItem = this.closest(".nav-item");
			const submenuContent = parentItem.querySelector(".submenu-content");

			submenuToggles.forEach((otherToggle) => {
				const otherParent = otherToggle.closest(".nav-item");
				if (
					otherParent !== parentItem &&
					otherParent.classList.contains("open")
				) {
					otherParent.classList.remove("open");
					otherParent.querySelector(".submenu-content").style.maxHeight = "0";
				}
			});

			parentItem.classList.toggle("open");

			if (parentItem.classList.contains("open")) {
				submenuContent.style.maxHeight = submenuContent.scrollHeight + "px";
			} else {
				submenuContent.style.maxHeight = "0";
			}
		});
	});

	const activeSubmenuItem = document.querySelector(
		".submenu-content li.active"
	);

	if (activeSubmenuItem) {
		const parentSubmenu = activeSubmenuItem.closest(".nav-item.has-submenu");

		if (parentSubmenu) {
			parentSubmenu.classList.add("open");

			const submenuContent = parentSubmenu.querySelector(".submenu-content");

			if (submenuContent) {
				submenuContent.style.maxHeight = submenuContent.scrollHeight + "px";
			}
		}
	}

	window.notyf = new Notyf({
		duration: 4000,
		position: {
			x: 'right',
			y: 'top',
		},
		types: [
			{
				type: 'success',
				backgroundColor: '#28a745',
				icon: {
					className: 'fas fa-check-circle',
					tagName: 'i',
					color: '#fff'
				}
			},
			{
				type: 'error',
				backgroundColor: '#dc3545',
				icon: {
					className: 'fas fa-times-circle',
					tagName: 'i',
					color: '#fff'
				}
			},
			{
				type: 'info',
				backgroundColor: '#007bff',
				icon: {
					className: 'fas fa-info-circle',
					tagName: 'i',
					color: '#fff'
				}
			}
		]
	});

	// Notificaciones

	const bell = document.getElementById('notificationBell');
	const panel = document.getElementById('notificationPanel');
	const badge = document.getElementById('notificationBadge');
	const list = document.getElementById('notificationList');

	if (bell && panel) {
		// --- Lógica para mostrar/ocultar el panel ---
		bell.addEventListener('click', (event) => {
			event.stopPropagation(); // Evita que el clic se propague al 'document'
			panel.classList.toggle('is-active');
		});

		// Cierra el panel si se hace clic fuera de él
		document.addEventListener('click', (event) => {
			if (!panel.contains(event.target) && panel.classList.contains('is-active')) {
				panel.classList.remove('is-active');
			}
		});

		/**
		 * Llama a la API para obtener las notificaciones no leídas.
		 */
		async function fetchNotifications() {
			try {
				const {data: notifications} = await fetchData('/api/notificaciones');
				updateNotificationUI(notifications);
			} catch (error) {
				console.error("Error al cargar notificaciones:", error);
				
				if (list) list.innerHTML = '<div class="notification-item-placeholder">No se pudieron cargar.</div>';
			}
		}

		/**
		 * Actualiza la interfaz (contador y lista) con las notificaciones recibidas.
		 */
		function updateNotificationUI(notifications) {
			if (!badge || !list) return;

			// Actualizar el contador
			if (notifications && notifications.length > 0) {
				badge.textContent = notifications.length;
				badge.style.display = 'flex';
			} else {
				badge.style.display = 'none';
			}

			// Renderizar la lista
			renderNotificationList(notifications);
		}

		/**
		 * Construye el HTML para la lista de notificaciones.
		 */
		function renderNotificationList(notifications) {
			if (!notifications || notifications.length === 0) {
				list.innerHTML = '<div class="notification-item-placeholder">No tienes notificaciones nuevas.</div>';
				return;
			}

			list.innerHTML = notifications.map(n => `
                <a href="${n.url_destino}" class="notification-item ${n.leida ? '' : 'is-unread'}" data-id="${n.id}">
                    <div class="notification-item-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="notification-item-content">
                        <p>${n.mensaje}</p>
                        <span class="timestamp">${new Date(n.created_at).toLocaleString('es-MX')}</span>
                    </div>
                </a>
            `).join('');
		}

		// --- Marcar como leída al hacer clic ---
		list.addEventListener('click', async (event) => {
			const item = event.target.closest('.notification-item');
			if (item && !item.classList.contains('is-read')) {
				const notificationId = item.dataset.id;
				try {
					postData(`/api/notificaciones/${notificationId}/marcar-leida`);
				} catch (error) {
					console.error("No se pudo marcar la notificación como leída:", error);
				}
			}
		});

		// --- Iniciar la carga de notificaciones ---
		fetchNotifications();
	}
});
