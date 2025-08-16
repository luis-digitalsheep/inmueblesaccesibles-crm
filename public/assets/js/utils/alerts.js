// export function showAlert(message, type = "error") {
// 	alertContainer.innerHTML = "";

//   const alertDiv = document.createElement("div");

//   alertDiv.className = `alert ${type}`;
// 	alertDiv.textContent = message;
// 	alertContainer.appendChild(alertDiv);

//   setTimeout(() => alertDiv.remove(), 5000);
// }

/**
 * Muestra una notificación flotante (toast) usando Notyf.
 * @param {string} message - El mensaje a mostrar.
 * @param {string} [type='info'] - El tipo de alerta ('success' o 'error').
 */
export function showAlert(message, type = 'info') {
  if (!window.notyf) {
    alert(message);
    return;
  }

  switch (type) {
    case 'success':
      window.notyf.success(message);
      break;
    case 'error':
      window.notyf.error(message);
      break;
    case 'info':
    default:
      window.notyf.open({
        type: 'info',
        message: message
      });
      break;
  }
}