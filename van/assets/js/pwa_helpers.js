/**
 * Utilidades globales PWA - SVDist POS
 */

// Función reutilizable para obtener coordenadas como una Promesa
function obtenerUbicacionGPS(timeoutMs = 5000) {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            console.warn("Geolocalización no soportada por el navegador.");
            resolve({ lat: null, lon: null });
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                resolve({
                    lat: pos.coords.latitude,
                    lon: pos.coords.longitude
                });
            },
            (err) => {
                console.warn("No se pudo obtener la geolocalización: " + err.message);
                resolve({ lat: null, lon: null }); // Retorna nulls si falla o deniega permiso
            },
            { timeout: timeoutMs, maximumAge: 0 }
        );
    });
}

// Función helper para rellenar campos ocultos y enviar un formulario con GPS
async function enviarFormularioConGPS(formId, latInputId, lonInputId, callbackAntesDeEnviar = null) {
    const form = document.getElementById(formId);
    if (!form) return;

    if (callbackAntesDeEnviar) callbackAntesDeEnviar();

    // Intentar obtener las coordenadas antes de hacer el submit
    const coords = await obtenerUbicacionGPS();
    
    if (latInputId && document.getElementById(latInputId)) {
        document.getElementById(latInputId).value = coords.lat !== null ? coords.lat : '';
    }
    if (lonInputId && document.getElementById(lonInputId)) {
        document.getElementById(lonInputId).value = coords.lon !== null ? coords.lon : '';
    }

    form.submit();
}