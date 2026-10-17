<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conexion.php';

$timeout_segundos = 3600; 

if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true) {
    
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $timeout_segundos) {
        session_unset();
        session_destroy();
        header("Location: backend/login.php?error=expirado");
        exit();
    }
    
    $_SESSION['ultimo_acceso'] = time();

    $GLOBAL_VENDEDOR_NOMBRE = $_SESSION['salesman_name'] ?? 'Vendedor Ruta';
    $GLOBAL_VENDEDOR_CODIGO = $_SESSION['salesman_code'] ?? 'VEND-000';
    $GLOBAL_VENDEDOR_EMAIL  = $_SESSION['salesman_email'] ?? '';

    // Reemplazar o asegurar que $GLOBAL_BODEGA_NOMBRE tome el valor de la sesión
    $GLOBAL_BODEGA_MÓVIL = $_SESSION['bodega_movil'] ?? 'VAN01';
    $GLOBAL_BODEGA_NOMBRE = $_SESSION['bodega_nombre'] ?? 'Bodega Rodante';    

    // Cargar las áreas de venta
    try {
        $db = BaseDatos::obtenerConexion();
        $stmt = $db->query("SELECT area_code, description FROM areas WHERE inactive = 0 ORDER BY description ASC");
        $GLOBAL_AREAS_VENTA = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $GLOBAL_AREAS_VENTA = [];
    }

    if (!isset($_SESSION['area_actual']) && !empty($GLOBAL_AREAS_VENTA)) {
        $_SESSION['area_actual'] = $GLOBAL_AREAS_VENTA[0]['area_code'];
    }
    $GLOBAL_AREA_ACTUAL = $_SESSION['area_actual'] ?? '';

    $GLOBAL_NOMBRE_AREA = 'GENERAL';
    foreach ($GLOBAL_AREAS_VENTA as $ar) {
        if ($ar['area_code'] == $GLOBAL_AREA_ACTUAL) {
            $GLOBAL_NOMBRE_AREA = $ar['description'];
            break;
        }
    }

    $GLOBAL_BODEGA_NOMBRE = 'Bodega Matriz';

    // Manejo del filtro por día de visita
    if (isset($_GET['dia'])) {
        $_SESSION['dia_actual'] = $_GET['dia'];
    }
    if (!isset($_SESSION['dia_actual'])) {
        $_SESSION['dia_actual'] = 'TODOS';
    }
    $GLOBAL_DIA_ACTUAL = $_SESSION['dia_actual'];

    // Consulta de sucursales filtradas por Área, Día de Visita y excluyendo Consumidor Final
    try {
        $sql = "SELECT cb.*, cm.name as customer_name 
                FROM cust_branch cb 
                JOIN debtors_master cm ON cb.debtor_no = cm.debtor_no 
                WHERE cb.area = :area_code 
                AND cm.name NOT LIKE '%CONSUMIDOR FINAL%' 
                AND cm.tax_id NOT IN ('9999999999999', '9999999999', '0000000000000')";
        
        $params = ['area_code' => $GLOBAL_AREA_ACTUAL];

        if ($GLOBAL_DIA_ACTUAL !== 'TODOS') {
            $sql .= " AND FIND_IN_SET(:dia, cb.dias_visita) > 0";
            $params['dia'] = $GLOBAL_DIA_ACTUAL;
        }

        $sql .= " ORDER BY cb.branch_code ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $GLOBAL_SUCURSALES = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $GLOBAL_SUCURSALES = [];
    }

} else {
    header("Location: backend/login.php");
    exit();
}
?>