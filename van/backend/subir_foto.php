<?php
// backend/subir_foto.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$directorio_destino = __DIR__ . '/../uploads/fotos_campo/';

// Crear el directorio si no existe
if (!file_exists($directorio_destino)) {
    mkdir($directorio_destino, 0755, true);
}

if (isset($_FILES['foto_capturada']) && $_FILES['foto_capturada']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['foto_capturada'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    // Validar extensiones permitidas
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
        exit();
    }

    // Generar un nombre único para evitar colisiones
    $nombre_unico = 'foto_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
    $ruta_absoluta = $directorio_destino . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
        // Construir la ruta relativa o URL que se almacenará y devolverá
        $url_relativa = 'uploads/fotos_campo/' . $nombre_unico;
        
        echo json_encode([
            'success' => true,
            'url' => $url_relativa,
            'message' => 'Imagen guardada exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al servidor']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se ha recibido ninguna imagen válida']);
}