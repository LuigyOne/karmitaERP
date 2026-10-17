<?php
// backend/validar.php
session_start();
require_once 'conexion.php';

$email = $_POST['username'] ?? '';
$pin = $_POST['password'] ?? '';
$bodega_movil = $_POST['bodega_movil'] ?? '';

$latitud = (!empty($_POST['lat']) && $_POST['lat'] != 0) ? $_POST['lat'] : null;
$longitud = (!empty($_POST['lon']) && $_POST['lon'] != 0) ? $_POST['lon'] : null;

if (empty($email) || empty($pin) || empty($bodega_movil) || $bodega_movil === 'DEF') {
    header("Location: login.php?error=bodega");
    exit();
}

try {
    $db = BaseDatos::obtenerConexion();
    
    // 1. Validar credenciales del vendedor
    $stmt = $db->prepare("SELECT salesman_code, salesman_name, salesman_email, salesman_fax, inactive FROM salesman WHERE salesman_email = :email");
    $stmt->execute(['email' => $email]);
    $vendedor = $stmt->fetch();

    if ($vendedor && $vendedor['inactive'] == 0 && $vendedor['salesman_fax'] === $pin) {
        
        // 2. Validar y obtener nombre de la bodega móvil seleccionada
        $stmt_bod = $db->prepare("SELECT location_name FROM locations WHERE loc_code = :loc AND loc_code != 'DEF' AND inactive = 0");
        $stmt_bod->execute(['loc' => $bodega_movil]);
        $info_bodega = $stmt_bod->fetch();

        if (!$info_bodega) {
            header("Location: login.php?error=bodega");
            exit();
        }

        // 3. Consultar datos de la empresa
        $stmt_prefs = $db->query("SELECT name, value FROM sys_prefs WHERE name IN ('coy_name', 'coy_no', 'gst_no', 'domicile', 'postal_address', 'email', 'phone')");
        $prefs = [];
        while ($row = $stmt_prefs->fetch(PDO::FETCH_ASSOC)) {
            $prefs[$row['name']] = $row['value'];
        }

        // Registrar variables de sesión completas para Auto-Venta
        $_SESSION['usuario_logueado'] = true;
        $_SESSION['salesman_code'] = $vendedor['salesman_code'];
        $_SESSION['salesman_name'] = $vendedor['salesman_name'];
        $_SESSION['salesman_email'] = $vendedor['salesman_email'];
        
        // Variables clave de la Bodega Móvil
        $_SESSION['bodega_movil'] = $bodega_movil;
        $_SESSION['bodega_nombre'] = $info_bodega['location_name'];

        $_SESSION['company_coy_name'] = $prefs['coy_name'] ?? '';
        $_SESSION['company_coy_no'] = $prefs['coy_no'] ?? '';
        $_SESSION['company_ruc'] = $prefs['gst_no'] ?? '';
        $_SESSION['company_domicile'] = $prefs['domicile'] ?? '';
        $_SESSION['company_postal'] = $prefs['postal_address'] ?? '';
        $_SESSION['company_email'] = $prefs['email'] ?? '';
        $_SESSION['company_phone'] = $prefs['phone'] ?? '';
        
        $_SESSION['ultimo_acceso'] = time();

        // 4. Registrar auditoría de Login en Ruta (Acción LG)
        require_once 'helpers.php';
        try {
            $uuid = generarUuidV4();
            $stmt_audit = $db->prepare("INSERT INTO pwa_auditar 
                (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, observaciones, latitud, longitud, sync_status, device_info) 
                VALUES (:uuid, 'LG', 'Login Auto-Venta', :usuario, NOW(), :obs, :lat, :lon, 'SINCRONIZADO', :device)");
            
            $stmt_audit->execute([
                'uuid' => $uuid,
                'usuario' => $email,
                'obs' => "Ingreso con Bodega Móvil: " . $bodega_movil . " (" . $info_bodega['location_name'] . ")",
                'lat' => $latitud,
                'lon' => $longitud,
                'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
            ]);

            $_SESSION['geo_disponible'] = (!empty($latitud) && $latitud != 0);            
        } catch (Exception $ex_audit) {
            // Ignorar error de auditoría para no bloquear el acceso
        }

        header("Location: ../index.php?view=ruta");
        exit();
    } else {
        header("Location: login.php?error=credenciales");
        exit();
    }

} catch (Exception $e) {
    header("Location: login.php?error=credenciales");
    exit();
}