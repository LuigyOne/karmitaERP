<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once 'helpers.php'; // Contiene la función generarUuidV4()

$directorio_destino = __DIR__ . '/../assets/img/capturas/';

// Crear la carpeta si no existe
if (!file_exists($directorio_destino)) {
    mkdir($directorio_destino, 0755, true);
}

if (isset($_FILES['foto_recibo']) && $_FILES['foto_recibo']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['foto_recibo'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
        exit();
    }

    // Nombrar la foto utilizando UUID v4
    $nombre_uuid = generarUuidV4() . '.' . $extension;
    $ruta_absoluta = $directorio_destino . $nombre_uuid;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
        $url_relativa = 'assets/img/capturas/' . $nombre_uuid;
        
        echo json_encode([
            'success' => true,
            'url' => $url_relativa,
            'message' => 'Imagen guardada exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al servidor']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se adjuntó ningún archivo']);
}
