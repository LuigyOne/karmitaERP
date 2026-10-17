<?php
// backend/obtener_gastos_hoy.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'gastos' => []]);
    exit();
}

require_once 'conexion.php';

$usuario = $_SESSION['salesman_email'] ?? $_SESSION['user_real_name'] ?? '';
$hoy = date('Y-m-d');

try {
    $db = BaseDatos::obtenerConexion();
    
    // Filtrar estrictamente por el usuario actual y la fecha de hoy (DATE() igual a hoy)
    $sql = "SELECT * FROM pwa_auditar 
            WHERE usuario = :usuario 
              AND accion_codigo = 'CC' 
              AND DATE(fecha_hora_creacion) = :hoy 
            ORDER BY fecha_hora_creacion DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario,
        'hoy' => $hoy
    ]);
    
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'gastos' => $gastos
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'gastos' => []]);
}