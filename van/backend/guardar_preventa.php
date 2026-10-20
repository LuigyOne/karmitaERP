<?php
// backend/guardar_preventa.php

// 1. SUPRIMIR ERRORES VISUALES PARA PROTEGER EL JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (!defined('TB_PREF')) {
    define('TB_PREF', ''); 
}

// 2. RECUPERAR DATOS DE LA SESIÓN DE LA PWA (SIN destructirla)
session_start();
$bodega_movil = $_SESSION['bodega_movil'] ?? 'VAN01';
$vendedor_code = intval($_SESSION['salesman_code'] ?? $_SESSION['user_id'] ?? 1);
$usuario_pwa = $_SESSION['salesman_email'] ?? $_SESSION['user_real_name'] ?? 'Vendedor Móvil';
$ruta_codigo_pwa = $_SESSION['area_actual'] ?? '1';
session_write_close(); 

// Limpiar búfer y fijar cabecera JSON
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/json; charset=UTF-8');

$log_file = __DIR__ . '/guardar_preventa_debug.log';
function registrarLog($mensaje) {
    global $log_file;
    @file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $mensaje . "\n", FILE_APPEND);
}

registrarLog("INICIO de Venta Directa Móvil (Consumiendo Mini API).");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'No hay ítems en la venta']);
    exit;
}

try {
    require_once 'conexion.php';
    require_once 'helpers.php'; 
    
    $db = BaseDatos::obtenerConexion();

    $cod_clien = intval($input['cod_clien'] ?? 0);
    $cod_sucur = intval($input['cod_sucur'] ?? 0);
    $forma_pago = $input['forma_pago'] ?? 'CONTADO';
    $contacto = $input['contacto_entrega'] ?? '';
    $notas = $input['notas_pedido'] ?? '';
    if (!empty($contacto)) {
        $notas = "Contacto: " . $contacto . " | " . $notas;
    }

    // 3. MAPEAR LOS ÍTEMS AL FORMATO QUE EXIGE LA MINI API (create_invoice.php)
    $items_miniapi = [];
    $valor_total = 0;

    foreach ($input['items'] as $item) {
        $stock_id = $item['id'] ?? $item['cod_item'] ?? '';
        $cantidad = floatval($item['quantity'] ?? $item['cantidad'] ?? 0);
        $precio = floatval($item['price'] ?? $item['precio'] ?? 0);
        $descuento = floatval($item['discount'] ?? $item['descuento'] ?? 0);

        if (empty($stock_id) || $cantidad <= 0) continue;

        $items_miniapi[] = [
            "cod_item" => $stock_id,
            "quantity" => $cantidad,
            "price" => $precio,
            "discount" => $descuento
        ];

        $valor_total += ($precio * $cantidad) * (1 - ($descuento / 100));
    }

    if (empty($items_miniapi)) {
        throw new Exception("El carrito está vacío o los ítems no son válidos.");
    }

    // Estructura JSON para la Mini API
    $payload_miniapi = [
        "debtor_no" => $cod_clien,
        "branch_code" => $cod_sucur,
        "loc_code" => !empty($bodega_movil) ? $bodega_movil : 'DEF',
        "items" => $items_miniapi
    ];

    // 4. CREDENCIALES FIJAS PARA LA MINI API
    $api_user = 'admin';
    $api_pass = '1712666864'; // Credenciales fijas definidas

    // 5. CONSUMIR LA MINI API MEDIANTE cURL INTERNO
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $miniapi_url = "$protocolo://$host/iquilqa/miniapi/create_invoice.php";

    $ch = curl_init($miniapi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_miniapi));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-COMPANY: 1',
        'X-USER: ' . $api_user,
        'X-PASSWORD: ' . $api_pass
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    registrarLog("Respuesta Mini API HTTP $http_code: " . $response);

    if ($http_code < 200 || $http_code >= 300 || !$response) {
        throw new Exception("Error al comunicarse con la Mini API del ERP (HTTP $http_code): " . ($curl_error ?: $response));
    }

    $resultado_api = json_decode($response, true);
    if (!isset($resultado_api['success']) || $resultado_api['success'] !== true) {
        $errMsg = $resultado_api['error'] ?? $resultado_api['message'] ?? 'Error desconocido en Mini API';
        throw new Exception($errMsg);
    }

    $invoice_no = intval($resultado_api['trans_no_erp']);

    // 6. EXTRAER DATOS OFICIALES DEL SRI O APLICAR RESPALDO SI HAY TIMEOUT
    $sri_data = $resultado_api['sri'] ?? [];
    
    // Usar empty() en lugar de ?? para capturar cadenas vacías ("") provocadas por el timeout del SRI
    $secuencial_sri = !empty($sri_data['sri_secuencial']) 
        ? $sri_data['sri_secuencial'] 
        : ('001-010-' . str_pad($invoice_no, 9, "0", STR_PAD_LEFT));
        
    $sri_estado = intval($sri_data['sri_estado'] ?? 0);
    $sri_clave_acceso = $sri_data['sri_clave_acceso'] ?? '';
    $sri_fecha_autorizacion = $sri_data['sri_fecha_autorizacion'] ?? date('Y-m-d H:i:s');
    $sri_mensaje = $sri_data['sri_mensaje'] ?? 'Procesado localmente';

    // 7. GRABAR LA PISTA DE AUDITORÍA LOCAL EN LA PWA
    $sql_audit = "INSERT INTO " . TB_PREF . "pwa_auditar 
        (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, ruta_descripcion, id_cliente, codigo_sucursal, pedido_factura_ref, valor, observaciones, sync_status, device_info) 
        VALUES (:uuid, 'VD', 'Venta Directa ERP', :usuario, NOW(), :ruta_cod, 'Zona Móvil Ruta', :id_cliente, :suc_cod, :pedido_ref, :valor, :obs, 'SINCRONIZADO', :device)";

    $stmt_audit = $db->prepare($sql_audit);
    $stmt_audit->execute([
        'uuid' => generarUuidV4(),
        'usuario' => $usuario_pwa,
        'ruta_cod' => $ruta_codigo_pwa,
        'id_cliente' => $cod_clien,
        'suc_cod' => $cod_sucur,
        'pedido_ref' => $secuencial_sri,
        'valor' => $valor_total,
        'obs' => "Venta Directa Bodega [$bodega_movil] - Pago:$forma_pago | $notas",
        'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
    ]);

    registrarLog("Venta completada con éxito. ERP Invoice: #$invoice_no | Secuencial SRI: $secuencial_sri | Clave: $sri_clave_acceso");

    // 8. RESPUESTA EXITOSA CON LOS DATOS FISCALES OBLIGATORIOS PARA EL TICKET
    echo json_encode([
        'success' => true,
        'sri_autorizado' => ($sri_estado == 1),
        'sri_secuencial' => $secuencial_sri,
        'sri_clave_acceso' => $sri_clave_acceso,
        'sri_fecha_autorizacion' => $sri_fecha_autorizacion,
        'trans_no_erp' => $invoice_no,
        'sri_estado' => $sri_estado,
        'sri_mensaje' => $sri_mensaje,
        'message' => 'Venta generada, integrada y auditoría registrada con éxito.'
    ]);

} catch (Throwable $e) { 
    $error_msg = $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine();
    registrarLog("ERROR CRÍTICO: " . $error_msg);
    echo json_encode([
        'success' => false, 
        'message' => 'Error procesando la venta: ' . $e->getMessage(),
        'debug' => $error_msg
    ]);
}
?>