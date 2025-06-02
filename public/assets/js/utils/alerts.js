export function showAlert(message, type = "error") {
	alertContainer.innerHTML = "";
	
  const alertDiv = document.createElement("div");
	
  alertDiv.className = `alert ${type}`;
	alertDiv.textContent = message;
	alertContainer.appendChild(alertDiv);
	
  setTimeout(() => alertDiv.remove(), 5000);
}
