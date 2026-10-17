<?php
session_start();
require_once 'conexion.php';
require_once 'helpers.php'; // Contiene la función generarUuidV4()[cite: 29]

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: login.php");
    exit();
}

$nuevo_area_code = $_POST['area_code'] ?? null;
$latitud = (!empty($_POST['lat']) && $_POST['lat'] != 0) ? $_POST['lat'] : null;
$longitud = (!empty($_POST['lon']) && $_POST['lon'] != 0) ? $_POST['lon'] : null;
$usuario = $_SESSION['salesman_email'] ?? 'Desconocido';
$view = $_POST['view'] ?? 'ruta';

if ($nuevo_area_code) {
    try {
        $db = BaseDatos::obtenerConexion();

        // 1. Obtener la descripción del área seleccionada[cite: 29]
        $stmt_area = $db->prepare("SELECT description FROM areas WHERE area_code = :area_code");
        $stmt_area->execute(['area_code' => $nuevo_area_code]);
        $area_info = $stmt_area->fetch(PDO::FETCH_ASSOC);
        $nombre_area = $area_info['description'] ?? 'Zona Desconocida';

        // 2. Contar el número de clientes-sucursales registrados en esta zona[cite: 29]
        $stmt_count = $db->prepare("SELECT COUNT(*) as total 
            FROM cust_branch cb 
            JOIN debtors_master cm ON cb.debtor_no = cm.debtor_no 
            WHERE cb.area = :area_code 
            AND cm.name NOT LIKE '%CONSUMIDOR FINAL%' 
            AND cm.tax_id NOT IN ('9999999999999', '9999999999', '0000000000000')
            AND cb.inactive = 0 AND cm.inactive = 0");
        $stmt_count->execute(['area_code' => $nuevo_area_code]);
        $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $total_clientes_ruta = $count_result['total'] ?? 0;

        // 3. Registrar la auditoría del cambio de área (Acción AZ)[cite: 29]
        $uuid = generarUuidV4();
        $stmt_audit = $db->prepare("INSERT INTO pwa_auditar 
            (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, ruta_descripcion, n_cliente_ruta, latitud, longitud, sync_status, device_info) 
            VALUES (:uuid, 'AZ', 'Cambio de Área / Zona', :usuario, NOW(), :ruta_cod, :ruta_desc, :n_clientes, :lat, :lon, 'SINCRONIZADO', :device)");
        
        $stmt_audit->execute([
            'uuid' => $uuid,
            'usuario' => $usuario,
            'ruta_cod' => $nuevo_area_code,
            'ruta_desc' => $nombre_area,
            'n_clientes' => $total_clientes_ruta,
            'lat' => $latitud,
            'lon' => $longitud,
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        ]);

        // 4. Actualizar AMBAS variables de sesión para garantizar compatibilidad con los filtros
        $_SESSION['area_actual'] = $nuevo_area_code;
        $_SESSION['area_code'] = $nuevo_area_code; 
        $_SESSION['geo_disponible'] = (!empty($latitud) && $latitud != 0);

    } catch (Exception $e) {
        // En caso de error en BD, actualizamos las sesiones igualmente para no bloquear al usuario[cite: 29]
        $_SESSION['area_actual'] = $nuevo_area_code;
        $_SESSION['area_code'] = $nuevo_area_code;
    }
}

header("Location: ../index.php?view=" . $view);
exit();
?>
