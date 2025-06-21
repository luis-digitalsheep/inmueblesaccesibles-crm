/**
 * Función base para enviar datos (POST, PUT, DELETE) a la API.
 * Maneja la configuración de cabeceras y cuerpo, y la respuesta JSON.
 * @param {string} endpoint - La ruta del API a la que se va a llamar.
 * @param {string} [method='POST'] - El método HTTP a utilizar.
 * @param {object|null} [body=null] - El objeto de datos para enviar en el cuerpo de la petición.
 * @returns {Promise<any>} La respuesta completa en JSON del servidor.
 * @throws {Error} Lanza un error si la petición falla, con el mensaje del servidor si está disponible.
 */
async function sendApiRequest(endpoint, method = 'POST', body = null) {
    const config = {
        method: method.toUpperCase(),
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    };

    if (body) {
        config.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(endpoint, config);

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || `Error del servidor: ${response.status}`);
        }

        return result;
    } catch (error) {
        console.error(`Error en la petición ${method} a ${endpoint}:`, error);

        throw error;
    }
}

/**
 * Realiza una petición GET a un endpoint de la API.
 * @param {string} endpoint - La ruta del API.
 * @param {URLSearchParams} [params] - Parámetros opcionales para la URL.
 * @returns {Promise<Array>} Un array con la propiedad 'data' de la respuesta.
 */
export async function fetchData(endpoint, params = null) {
    const url = params ? `${endpoint}?${params.toString()}` : endpoint;

    try {
        const response = await fetch(url);

        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.message || `Error del servidor: ${response.status}`);
        }

        const result = await response.json();

        return result.data || [];
    } catch (error) {
        console.error(`Error de conexión o parseo al llamar a ${endpoint}:`, error);
        throw error;
    }
}

/**
 * Obtiene un catálogo específico de la API.
 * @param {string} catalogName - El nombre del catálogo (ej. 'sucursales').
 * @returns {Promise<Array>} Un array con los datos del catálogo.
 */
export async function fetchCatalog(catalogName) {
    try {
        return await fetchData(`/api/catalogos/${catalogName}`);
    } catch (error) {
        return [];
    }
}

// === EXPORTS PARA POST, PUT, DELETE ===
export const postData = (endpoint, body) => sendApiRequest(endpoint, 'POST', body);
export const putData = (endpoint, body) => sendApiRequest(endpoint, 'PUT', body);
export const deleteData = (endpoint) => sendApiRequest(endpoint, 'DELETE', null);