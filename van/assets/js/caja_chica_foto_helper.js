/**
 * assets/js/caja_chica_foto_helper.js
 * Helper para capturar, redimensionar y comprimir la foto del comprobante de caja chica en el cliente.
 */
let blobComprobanteComprimido = null;

function procesarFotoComprobante(event, callbackPreview) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.readAsDataURL(file);

    reader.onload = function(e) {
        const img = new Image();
        img.src = e.target.result;

        img.onload = function() {
            // Dimensiones máximas referenciales para el comprobante (máx. 1024px)
            const MAX_WIDTH = 1024;
            const MAX_HEIGHT = 1024;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }

            // Dibujar en un canvas HTML5 para reducir peso
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // Exportar a Blob con calidad del 75%
            canvas.toBlob(function(blob) {
                // Guardar el blob comprimido en una variable global o controlada
                blobComprobanteComprimido = blob;

                // Ejecutar callback para mostrar la previsualización visual en la interfaz
                if (typeof callbackPreview === 'function') {
                    callbackPreview(canvas.toDataURL('image/jpeg', 0.75));
                }
            }, 'image/jpeg', 0.75);
        };
    };
}