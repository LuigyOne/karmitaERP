<?php
// backend/actualizar_foto_cliente.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once 'conexion.php';

$branch_code = isset($_POST['branch_code']) ? intval($_POST['branch_code']) : 0;
$numero_foto = isset($_POST['numero_foto']) ? intval($_POST['numero_foto']) : 0;

if ($branch_code <= 0 || $numero_foto < 1 || $numero_foto > 4) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit();
}

if (!isset($_FILES['foto_local']) || $_FILES['foto_local']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No se ha recibido ninguna imagen válida']);
    exit();
}

$archivo = $_FILES['foto_local'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $extensiones_permitidas)) {
    echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
    exit();
}

// Ruta compartida un nivel arriba en FrontAccounting / Notrinos ERP
$directorio_destino = __DIR__ . '/../../company/1/images/branches/';
if (!file_exists($directorio_destino)) {
    mkdir($directorio_destino, 0755, true);
}

// Generar nombre con el formato exacto de FrontAccounting: branch_{code}_foto_{num}_{timestamp}.jpg
$nombre_archivo = 'branch_' . $branch_code . '_foto_' . $numero_foto . '_' . time() . '.' . $extension;
$ruta_absoluta = $directorio_destino . $nombre_archivo;

if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
    $columna_foto = "foto_" . $numero_foto;

    try {
        $db = BaseDatos::obtenerConexion();
        
        // Guardar únicamente el nombre del archivo en la base de datos (como lo maneja FrontAccounting)
        $sql = "UPDATE cust_branch SET {$columna_foto} = :foto WHERE branch_code = :branch_code";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'foto' => $nombre_archivo,
            'branch_code' => $branch_code
        ]);

        echo json_encode([
            'success' => true,
            'filename' => $nombre_archivo,
            'message' => 'Fotografía actualizada correctamente'
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al mover el archivo al servidor']);
}