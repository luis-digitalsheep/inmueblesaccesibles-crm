/**
 * Activa o desactiva el modo de edición para todos los campos de un formulario
 * contenido dentro de un elemento específico.
 * * @param {boolean} isEditing - `true` para activar la edición, `false` para desactivarla (modo solo lectura).
 * @param {HTMLElement} formContainer - El elemento contenedor del formulario o de los campos a modificar.
 */
export function toggleEditMode(isEditing, formContainer) {
    if (!formContainer) {
        console.error("Se requiere un contenedor de formulario para toggleEditMode.");
        return;
    }

    const formElements = formContainer.querySelectorAll('.form-input, .form-select, .form-textarea');

    const btnEditar = formContainer.querySelector('.btn-edit-mode');
    const btnGuardar = formContainer.querySelector('.btn-save-mode');
    const btnCancelar = formContainer.querySelector('.btn-cancel-mode');

    formElements.forEach(input => {
        input.disabled = !isEditing;

        if (isEditing) {
            input.classList.remove('is-readonly');
            input.removeAttribute('readonly');
        } else {
            input.classList.add('is-readonly');
            input.setAttribute('readonly', 'readonly');
        }
    });

    if (btnEditar) btnEditar.style.display = isEditing ? 'none' : 'inline-block';
    if (btnGuardar) btnGuardar.style.display = isEditing ? 'inline-block' : 'none';
    if (btnCancelar) btnCancelar.style.display = isEditing ? 'inline-block' : 'none';

    if (isEditing) {
        const firstInput = formContainer.querySelector('.form-input, .form-select, .form-textarea');
        firstInput?.focus();
    }
}