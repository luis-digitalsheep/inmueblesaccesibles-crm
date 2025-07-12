/**
 * Aplica formato de moneda a un campo de texto.
 * Escucha los eventos 'blur' y 'focus' para formatear y desformatear.
 * @param {HTMLInputElement} inputElement - El elemento del input al que se aplicará el formato.
 */
export function applyCurrencyFormatting(inputElement) {
    if (!inputElement) return;

    const formatValue = (value) => {
        if (!value) return '';

        const number = parseFloat(String(value).replace(/[^\d.-]/g, ''));

        if (isNaN(number)) return '';

        return number.toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const unformatValue = (value) => {
        if (!value) return '';

        return String(value).replace(/[^\d.-]/g, '');
    };

    inputElement.addEventListener('blur', () => {
        inputElement.value = formatValue(inputElement.value);
    });

    inputElement.addEventListener('focus', () => {
        inputElement.value = unformatValue(inputElement.value);
    });

    inputElement.value = formatValue(inputElement.value);
}

/**
 * Intenta convertir una fecha en formato 'dd/MM/yyyy' a 'yyyy-MM-dd'.
 * @param {string} dateString - La fecha del Excel, ej. "28/06/2025".
 * @returns {string|null} La fecha en formato 'yyyy-MM-dd' o null si el formato es inválido.
 */
export function parseDateForInput(dateString) {
    if (!dateString || typeof dateString !== 'string') {
        return null;
    }

    const parts = dateString.split('/');

    if (parts.length !== 3) {
        return null;
    }

    const [day, month, year] = parts;

    if (year.length !== 4 || month.length > 2 || day.length > 2) {
        return null;
    }

    // Se usa padStart para asegurar que el mes y día tengan dos dígitos (ej. '06' en lugar de '6')
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}