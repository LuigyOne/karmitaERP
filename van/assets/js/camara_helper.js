// assets/js/camara_helper.js

/**
 * Abre el selector de cámara / archivo, sube la imagen al servidor y devuelve la URL.
 * @param {Function} callbackURL - Función que recibe la URL de la imagen guardada.
 */
function abrirCapturaFotografica(callbackURL) {
    // Crear dinámicamente un input file oculto con atributos de captura móvil
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = 'image/*';
    inputFile.capture = 'environment'; // Abre directamente la cámara trasera en móviles

    inputFile.onchange = function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('foto_capturada', file);

        // Mostrar indicador visual opcional de carga
        console.log("Subiendo fotografía al servidor...");

        fetch('backend/subir_foto.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Devolver únicamente la URL a la aplicación solicitante
                if (typeof callbackURL === 'function') {
                    callbackURL(data.url);
                }
            } else {
                alert("Error al subir la foto: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error de red:", error);
            alert("No se pudo completar la subida de la fotografía.");
        });
    };

    // Disparar el diálogo de cámara
    inputFile.click();
}