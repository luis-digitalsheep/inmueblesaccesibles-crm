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
});
