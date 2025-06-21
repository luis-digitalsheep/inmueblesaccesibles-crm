document.addEventListener("DOMContentLoaded", function () {
	const loginForm = document.getElementById("loginForm");
	const alertContainer = document.getElementById("alert-message-container");

	function showAlert(message, type = "error") {
		alertContainer.innerHTML = "";

		const alertDiv = document.createElement("div");

		alertDiv.className = `alert ${type}`;
		alertDiv.textContent = message;

		alertContainer.appendChild(alertDiv);

		if (type === "success") {
			setTimeout(() => {
				alertDiv.remove();
			}, 3000);
		}
	}

	if (loginForm) {
		loginForm.addEventListener("submit", async function (event) {
			event.preventDefault();

			const formData = new FormData(loginForm);

			try {
				const response = await fetch('api/auth/login', {
					method: loginForm.method,
					body: formData,
				});

				const result = await response.json();

				if (response.ok) {
					showAlert(result.message, "success");

					if (result.redirect) {
						window.location.href = result.redirect;
					}
				} else {
					showAlert(result.message, "error");
				}
			} catch (error) {
				console.error("Error al procesar el login:", error);
				showAlert(
					"Hubo un problema al intentar iniciar sesión. Intenta de nuevo más tarde.",
					"error"
				);
			}
		});
	}
});
