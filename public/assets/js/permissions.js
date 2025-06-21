window.App = window.App || {};
window.App.Permissions = {
	_userPermissions: [],
	_userId: null,
	_rolId: null,
	_sucursalId: null,
	_loaded: false,

	async loadPermissions() {
		try {
			const response = await fetch("/api/auth/permissions");

			if (response.status === 401) {
				console.warn("Usuario no autenticado, no se cargarán los permisos.");
				return false;
			}

			if (!response.ok) {
				const errorData = await response.json();
				console.error("Error al cargar permisos:", errorData.message);
				return false;
			}

			const data = await response.json();

			this._userPermissions = data.permissions || [];
			this._userId = data.user_id;
			this._rolId = data.rol_id;
			this._sucursalId = data.sucursal_id;

			document.dispatchEvent(new CustomEvent("permissionsLoaded"));
			return true;
		} catch (error) {
			console.error("Fallo de red o API al cargar permisos:", error);
			return false;
		}
	},

	isLoaded() {
		return this._loaded;
	},

	async waitForPermissions() {
		if (this._loaded) {
			return;
		}
		return new Promise((resolve) => {
			document.addEventListener(
				"permissionsLoaded",
				() => {
					resolve();
				},
				{ once: true }
			);
		});
	},

	hasPermission(permissionName) {
		return this._userPermissions.includes(permissionName);
	},

	// Obtiene el ID del usuario
	getUserId() {
		return this._userId;
	},

	// Obtiene el ID del rol
	getRolId() {
		return this._rolId;
	},

	// Obtiene el ID de la sucursal
	getSucursalId() {
		return this._sucursalId;
	},
};

document.addEventListener("DOMContentLoaded", async () => {
	const currentPath = window.location.pathname;
	if (!currentPath.includes("/login") && !currentPath.includes("/logout")) {
		await window.App.Permissions.loadPermissions();
	} else {
		window.App.Permissions._loaded = true;
		document.dispatchEvent(new CustomEvent("permissionsLoaded"));
	}
});
