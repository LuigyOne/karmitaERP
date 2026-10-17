<?php
session_start();
require_once 'conexion.php';
require_once 'helpers.php'; // Cargar funciones globales

$usuario = $_SESSION['salesman_email'] ?? 'Desconocido';
$latitud = (!empty($_POST['lat']) && $_POST['lat'] != 0) ? $_POST['lat'] : null;
$longitud = (!empty($_POST['lon']) && $_POST['lon'] != 0) ? $_POST['lon'] : null;

try {
    $db = BaseDatos::obtenerConexion();
    
    $uuid = generarUuidV4(); // Llamada limpia a la función

    $stmt_audit = $db->prepare("INSERT INTO pwa_auditar 
        (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, latitud, longitud, sync_status, device_info) 
        VALUES (:uuid, 'LO', 'Cierre de Sesión', :usuario, NOW(), :lat, :lon, 'SINCRONIZADO', :device)");
    
    $stmt_audit->execute([
        'uuid' => $uuid,
        'usuario' => $usuario,
        'lat' => $latitud,
        'lon' => $longitud,
        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
    ]);
} catch (Exception $e) {
    // Permitir el cierre de sesión aunque falle la auditoría
}

session_unset();
session_destroy();

header("Location: ../index.php"); 
exit();
?>
