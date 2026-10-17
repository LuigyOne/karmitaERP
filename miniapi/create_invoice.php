<?php
// miniapi/create_invoice.php

// 1. Forzar captura absoluta de cualquier salida previa para proteger el JSON
ob_start();

// Manejador de apagado para asegurar que NUNCA se devuelva HTML si ocurre un error fatal
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "success" => false,
            "error" => "Error crítico en el servidor ERP: " . $error['message'] . " en " . basename($error['file']) . " línea " . $error['line']
        ], JSON_PRETTY_PRINT);
    }
});

require_once __DIR__ . '/auth.php';

// 2. Simular entorno del ERP para prevenir bloqueos de origen y vistas HTML
$_POST['ui_mode'] = 0;
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
if (!isset($SysPrefs)) {
    global $SysPrefs;
}

// 3. Cargar dependencias nativas del ERP
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

// Limpiar cualquier HTML que las inclusiones hayan podido inyectar
while (ob_get_level() > 0) { ob_end_clean(); }
ob_start();

// Solo permitimos peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["success" => false, "error" => "Método HTTP no permitido. Use POST."], JSON_PRETTY_PRINT);
    exit;
}

// Obtener los datos JSON enviados desde la PWA / Postman
$raw_input = file_get_contents('php://input');
$input_data = json_decode($raw_input, true);

$debtor_no   = isset($input_data['debtor_no']) ? intval($input_data['debtor_no']) : 0;
$branch_code = isset($input_data['branch_code']) ? intval($input_data['branch_code']) : 0;
$loc_code    = isset($input_data['loc_code']) ? trim($input_data['loc_code']) : 'DEF';
$items       = isset($input_data['items']) && is_array($input_data['items']) ? $input_data['items'] : [];

// 4. Validaciones básicas
if ($debtor_no <= 0 || $branch_code <= 0 || empty($items)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false, 
        "error" => "Faltan datos obligatorios: debtor_no, branch_code o el arreglo de items."
    ], JSON_PRETTY_PRINT);
    exit;
}

// 5. Inicializar procesamiento de sesión y carrito
processing_start();

$cart = new Cart(ST_SALESINVOICE, 0);
$cart->document_date = Today();
$_SESSION['Items'] = $cart;

// 6. Cargar datos del cliente y sucursal
$error = get_customer_details_to_order($cart, $debtor_no, $branch_code);
if ($error != '') {
    processing_end();
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false, 
        "error" => "Error validando cliente/sucursal en el ERP: " . strip_tags($error)
    ], JSON_PRETTY_PRINT);
    exit;
}

// 7. Asignar bodega de origen
if (!empty($loc_code)) {
    $cart->set_location($loc_code, '');
}

// 8. Resolver códigos mediante item_codes y agregar al carrito
foreach ($items as $item) {
    $input_code = isset($item['cod_item']) ? trim($item['cod_item']) : '';
    $quantity   = isset($item['quantity']) ? floatval($item['quantity']) : 0;
    $price      = isset($item['price']) ? floatval($item['price']) : 0;
    $discount   = isset($item['discount']) ? floatval($item['discount']) : 0;

    if (!empty($input_code) && $quantity > 0) {
        $sql_code = "SELECT stock_id, quantity FROM " . TB_PREF . "item_codes 
                     WHERE item_code = " . db_escape($input_code) . " 
                        OR stock_id = " . db_escape($input_code) . " 
                     LIMIT 1";
        $res_code = db_query($sql_code, "Error al resolver código de ítem");
        
        if ($row_code = db_fetch($res_code)) {
            $stock_id = $row_code['stock_id'];
            $factor = floatval($row_code['quantity']);
            $final_qty = $quantity * ($factor > 0 ? $factor : 1);

            if ($price <= 0) {
                $price = get_kit_price($stock_id, $cart->customer_currency, $cart->sales_type, $cart->price_factor, $cart->document_date);
            }

            add_to_order($_SESSION['Items'], $stock_id, $final_qty, $price, $discount);
        } else {
            add_to_order($_SESSION['Items'], $input_code, $quantity, $price, $discount);
        }
    }
}

// 9. Validar que el carrito tenga líneas cargadas
if (count($_SESSION['Items']->line_items) == 0) {
    processing_end();
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false, 
        "error" => "El carrito de factura está vacío o los códigos de ítems enviados no se encuentran en la tabla item_codes o stock_master."
    ], JSON_PRETTY_PRINT);
    exit;
}

// 10. Escribir la transacción en la base de datos (Ejecuta write_sales_invoice, contabilidad y llamada SRI)
$factura_no = $_SESSION['Items']->write(1);

processing_end();

// Consultar datos del SRI para el ticket
$sri_data = [
    "sri_estado" => 0,
    "sri_secuencial" => "",
    "sri_clave_acceso" => "",
    "sri_fecha_autorizacion" => "",
    "sri_mensaje" => ""
];

if ($factura_no) {
    $sql_sri = "SELECT sri_estado, sri_secuencial, sri_clave_acceso, sri_fecha_autorizacion, sri_mensaje 
                FROM " . TB_PREF . "debtor_trans 
                WHERE type = " . ST_SALESINVOICE . " AND trans_no = " . db_escape($factura_no) . " LIMIT 1";
    $res_sri = db_query($sql_sri, "No se pudo consultar el estado SRI de la factura");
    if ($row_sri = db_fetch($res_sri)) {
        $sri_data = [
            "sri_estado" => intval($row_sri['sri_estado']),
            "sri_secuencial" => trim($row_sri['sri_secuencial']),
            "sri_clave_acceso" => trim($row_sri['sri_clave_acceso']),
            "sri_fecha_autorizacion" => trim($row_sri['sri_fecha_autorizacion']),
            "sri_mensaje" => trim($row_sri['sri_mensaje'])
        ];
    }
}

// Limpiar cualquier búfer acumulado antes de emitir la respuesta JSON final
while (ob_get_level() > 0) { ob_end_clean(); }

header('Content-Type: application/json; charset=utf-8');

if ($factura_no) {
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Factura generada exitosamente en el ERP con sus asientos contables e integración SRI enlazados",
        "trans_no_erp" => $factura_no,
        "company_id" => $company,
        "sri" => $sri_data
    ], JSON_PRETTY_PRINT);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "No se pudo registrar la factura en el núcleo del ERP. Revise el archivo logs/sri_debug.log o el stock en bodega."
    ], JSON_PRETTY_PRINT);
}
exit;
?>