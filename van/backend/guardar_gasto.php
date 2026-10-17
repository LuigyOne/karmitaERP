<?php
// backend/guardar_gasto.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once 'conexion.php';
require_once 'helpers.php'; // Contiene generarUuidV4()

$monto = floatval($_POST['monto'] ?? 0);
$categoria = $_POST['categoria'] ?? 'Varios';
$detalle = $_POST['descripcion'] ?? 'Gasto en ruta';

if ($monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a cero']);
    exit();
}

$ruta_foto = null;

// Procesar fotografía opcional usando UUID v4
if (isset($_FILES['foto_recibo']) && $_FILES['foto_recibo']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['foto_recibo'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($extension, $extensiones_permitidas)) {
        $directorio_destino = __DIR__ . '/../assets/img/capturas/';
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
        
        $nombre_uuid = generarUuidV4() . '.' . $extension;
        $ruta_absoluta = $directorio_destino . $nombre_uuid;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_absoluta)) {
            $ruta_foto = 'assets/img/capturas/' . $nombre_uuid;
        }
    }
}

try {
    $db = BaseDatos::obtenerConexion();
    
    // Obtener variables globales de sesión
    $usuario = $_SESSION['salesman_email'] ?? $_SESSION['user_real_name'] ?? 'Desconocido';
    $ruta_codigo = $_SESSION['area_actual'] ?? $_SESSION['area_code'] ?? '1';
    
    // Obtener descripción de la zona/área
    $stmt_area = $db->prepare("SELECT description FROM " . (defined('TB_PREF') ? TB_PREF : '') . "areas WHERE area_code = :area_code");
    $stmt_area->execute(['area_code' => $ruta_codigo]);
    $area_info = $stmt_area->fetch(PDO::FETCH_ASSOC);
    $ruta_descripcion = $area_info['description'] ?? 'Zona Principal';

    $uuid = generarUuidV4();
    $observaciones = "Categoría: " . $categoria . " | " . $detalle;

    // Insertar movimiento en pwa_auditar con acción 'CC' (Caja Chica)
    $sql = "INSERT INTO pwa_auditar 
        (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, ruta_descripcion, valor, observaciones, foto_url, sync_status, device_info) 
        VALUES (:uuid, 'CC', 'Caja Chica - Gasto', :usuario, NOW(), :ruta_cod, :ruta_desc, :valor, :obs, :foto, 'SINCRONIZADO', :device)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'uuid' => $uuid,
        'usuario' => $usuario,
        'ruta_cod' => $ruta_codigo,
        'ruta_desc' => $ruta_descripcion,
        'valor' => $monto,
        'obs' => $observaciones,
        'foto' => $ruta_foto,
        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Gasto registrado correctamente en auditoría'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}