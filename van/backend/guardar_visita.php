<?php
session_start();
require_once 'conexion.php';
require_once 'helpers.php'; // Contiene la función generarUuidV4()

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: login.php");
    exit();
}

$branch_code = $_POST['branch_code'] ?? null;
$debtor_no = $_POST['debtor_no'] ?? null;
$resultado = $_POST['resultado_visita'] ?? 'Sin especificar';
$latitud = (!empty($_POST['lat']) && $_POST['lat'] != 0) ? $_POST['lat'] : null;
$longitud = (!empty($_POST['lon']) && $_POST['lon'] != 0) ? $_POST['lon'] : null;
$usuario = $_SESSION['salesman_email'] ?? 'Desconocido';
$area_code = $_SESSION['area_actual'] ?? $_SESSION['area_code'] ?? '1';

if ($branch_code && $resultado) {
    try {
        $db = BaseDatos::obtenerConexion();

        // Obtener datos de la sucursal para la auditoría
        $stmt_suc = $db->prepare("SELECT branch_ref FROM cust_branch WHERE branch_code = :branch_code");
        $stmt_suc->execute(['branch_code' => $branch_code]);
        $suc_info = $stmt_suc->fetch(PDO::FETCH_ASSOC);
        $nombre_sucursal = $suc_info['branch_ref'] ?? 'Sucursal';

        // Registrar en pwa_auditar
        $uuid = generarUuidV4();
        $stmt_audit = $db->prepare("INSERT INTO pwa_auditar 
            (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, id_cliente, codigo_sucursal, observaciones, latitud, longitud, sync_status, device_info) 
            VALUES (:uuid, 'VS', 'Visita de Ruta', :usuario, NOW(), :ruta_cod, :id_cliente, :suc_cod, :obs, :lat, :lon, 'SINCRONIZADO', :device)");
        
        $stmt_audit->execute([
            'uuid' => $uuid,
            'usuario' => $usuario,
            'ruta_cod' => $area_code,
            'id_cliente' => $debtor_no,
            'suc_cod' => $branch_code,
            'obs' => "Resultado de visita: " . $resultado,
            'lat' => $latitud,
            'lon' => $longitud,
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        ]);

        $_SESSION['geo_disponible'] = (!empty($latitud) && $latitud != 0);

    } catch (Exception $e) {
        // En caso de excepción, permitimos continuar al listado para no bloquear al usuario
    }
}

// Redirigir de regreso al listado de ruta
header("Location: ../index.php?view=ruta");
exit();
?>