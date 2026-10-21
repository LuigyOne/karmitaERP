/**
 * assets/js/foto_cliente_helper.js
 * Helper reutilizable para capturar, comprimir y subir las fotos de la ficha de cliente.
 */
function abrirCapturaFotoCliente(branchCode, numeroFoto, onSuccessCallback) {
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = 'image/*';
    inputFile.capture = 'environment'; // Abre directamente la cámara trasera en dispositivos móviles

    inputFile.onchange = function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = function(e) {
            const img = new Image();
            img.src = e.target.result;

            img.onload = function() {
                // Dimensiones máximas referenciales para aligerar la subida (máx. 1024px)
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

                // Renderizar en un canvas HTML5 para redimensionar
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Exportar como Blob JPEG con compresión del 75% (calidad óptima y peso mínimo)
                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('branch_code', branchCode);
                    formData.append('numero_foto', numeroFoto);
                    formData.append('foto_local', blob, 'foto_cliente_' + numeroFoto + '.jpg');

                    console.log("Subiendo fotografía optimizada #" + numeroFoto + "...");

                    fetch('backend/actualizar_foto_cliente.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof onSuccessCallback === 'function') {
                                onSuccessCallback(data);
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert("Error al guardar la foto: " + data.message);
                        }
                    })
                    .catch(error => {
                        console.error("Error de red:", error);
                        alert("No se pudo completar la subida de la fotografía.");
                    });

                }, 'image/jpeg', 0.75);
            };
        };
    };

    inputFile.click();
}