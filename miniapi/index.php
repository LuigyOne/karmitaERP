<?php
// miniapi/index.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 1. Limpiar búferes iniciales para evitar salidas HTML o el "1" plano
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

$path_to_root = "..";

// 2. Capturar las cabeceras HTTP de autenticación
$headers = [];
if (function_exists('getallheaders')) {
    $headers = getallheaders();
}
$headers = array_change_key_case($headers, CASE_LOWER);

$company = isset($headers['x-company']) ? $headers['x-company'] : '0';
$user    = isset($headers['x-user']) ? trim($headers['x-user']) : '';
$pass    = isset($headers['x-password']) ? trim($headers['x-password']) : '';

if (empty($user) || empty($pass)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Faltan credenciales en cabeceras (X-USER, X-PASSWORD, X-COMPANY)"]);
    exit;
}

// 3. INYECTAR LAS CREDENCIALES EN EL $_POST ANTES DE INCLUIR LA SESIÓN
$_POST['company_login_name'] = $company;
$_POST['user_name_entry_field'] = $user;
$_POST['password'] = $pass;
$_POST['ui_mode'] = 0;

// 4. Cargar el entorno personalizado de sesión
include_once($path_to_root . "/miniapi/session_luis.inc");

// 5. Validar si el usuario logró loguearse exitosamente
if (!isset($_SESSION["wa_current_user"]) || !$_SESSION["wa_current_user"]->logged_in()) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Credenciales incorrectas o acceso denegado para el usuario: " . $user]);
    exit;
}

// ==========================================
// ENTORNO LOGUEADO Y CONECTADO
// ==========================================
set_global_connection($company);

// Obtener la ruta limpia después de /miniapi/
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_path = parse_url($request_uri, PHP_URL_PATH);
$route_part = strstr($parsed_path, 'miniapi');
$segments = $route_part ? explode('/', trim(str_replace('miniapi', '', $route_part), '/')) : [];

// $segments[0] será el recurso (ej: sales, customers, inventory)
// $segments[1] puede ser un parámetro opcional (ej: un ID o subrecurso)
$resource = isset($segments[0]) ? $segments[0] : '';
$param    = isset($segments[1]) ? $segments[1] : null;

$data = [];
$success = true;
$error_message = "";

switch ($resource) {
    
    // ----------------------------------------------------
    // MÓDULO: VENTAS (/miniapi/sales o /miniapi/sales/1042)
    // ----------------------------------------------------
    case 'sales':
        if ($param) {
            // Detalle de una venta específica por trans_no
            $sql = "SELECT * FROM " . TB_PREF . "debtor_trans WHERE type = 10 AND trans_no = " . db_escape($param);
        } else {
            // Listado general de ventas
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
            $sql = "SELECT trans_no, type, debtor_no, tran_date, due_date, reference, ov_amount, ov_gst 
                    FROM " . TB_PREF . "debtor_trans 
                    WHERE type = 10 
                    ORDER BY trans_no DESC LIMIT " . $limit;
        }
        
        $result = db_query($sql, "No se pudieron consultar las ventas");
        if ($result) {
            while ($row = db_fetch($result)) {
                $clean_row = [];
                foreach ($row as $key => $value) {
                    if (!is_int($key)) { $clean_row[$key] = $value; }
                }
                $data[] = $clean_row;
            }
        }
        break;

    // ----------------------------------------------------
    // MÓDULO: CLIENTES (/miniapi/customers)
    // ----------------------------------------------------
    case 'customers':
        $sql = "SELECT debtor_no, debtor_ref, name, tax_id, address, curr_code, credit_limit, inactive 
                FROM " . TB_PREF . "debtors_master 
                ORDER BY name ASC LIMIT 50";
                
        $result = db_query($sql, "No se pudieron consultar los clientes");
        if ($result) {
            while ($row = db_fetch($result)) {
                $clean_row = [];
                foreach ($row as $key => $value) {
                    if (!is_int($key)) { $clean_row[$key] = $value; }
                }
                $data[] = $clean_row;
            }
        }
        break;

    // ----------------------------------------------------
    // MÓDULO: INVENTARIO / STOCK (/miniapi/inventory)
    // ----------------------------------------------------
    case 'inventory':
        $sql = "SELECT sm.stock_id, ic.description, sm.loc_code, SUM(sm.qty) AS stock_calculado 
                FROM " . TB_PREF . "stock_moves sm
                LEFT JOIN " . TB_PREF . "item_codes ic ON sm.stock_id = ic.stock_id
                GROUP BY sm.stock_id, sm.loc_code
                LIMIT 50";
                
        $result = db_query($sql, "No se pudo calcular el inventario");
        if ($result) {
            while ($row = db_fetch($result)) {
                $clean_row = [];
                foreach ($row as $key => $value) {
                    if (!is_int($key)) { $clean_row[$key] = $value; }
                }
                $data[] = $clean_row;
            }
        }
        break;

    default:
        $success = false;
        $error_message = "Endpoint o recurso no encontrado: " . $resource;
        http_response_code(404);
        break;
}

// Limpiar cualquier residuo de búfer del núcleo antes de arrojar el JSON final
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

if (!$success) {
    echo json_encode(["success" => false, "error" => $error_message], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "success" => true,
        "company_id" => $company,
        "logged_user" => $_SESSION["wa_current_user"]->name ?? $user,
        "resource" => $resource,
        "total_records" => count($data),
        "data" => $data
    ], JSON_PRETTY_PRINT);
}
exit;
?>