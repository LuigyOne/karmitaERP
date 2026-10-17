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
// Esto hace creer al núcleo de NotrinosERP que el usuario presionó el botón de login en el formulario
$_POST['company_login_name'] = $company;
$_POST['user_name_entry_field'] = $user;
$_POST['password'] = $pass;
$_POST['ui_mode'] = 0;

// 4. Cargar el entorno personalizado de sesión (que procesará el login automáticamente)
include_once($path_to_root . "/miniapi/session_luis.inc");

// 5. Validar si el usuario logró loguearse exitosamente en el sistema
if (!isset($_SESSION["wa_current_user"]) || !$_SESSION["wa_current_user"]->logged_in()) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Credenciales incorrectas o acceso denegado para el usuario: " . $user]);
    exit;
}

// ==========================================
// ENTORNO 100% LOGUEADO, CONECTADO Y CON TRAZABILIDAD
// ==========================================
set_global_connection($company);

$request_uri = $_SERVER['REQUEST_URI'];

if (strpos($request_uri, '/miniapi') !== false) {
    
    // Consulta SQL utilizando la función nativa del ERP con el prefijo correcto de tablas
    $sql = "SELECT trans_no, type, debtor_no, tran_date, due_date, reference, ov_amount, ov_gst 
            FROM " . TB_PREF . "debtor_trans 
            WHERE type = 10 
            ORDER BY trans_no DESC LIMIT 20";
            
    $result = db_query($sql, "No se pudieron consultar las ventas");
    
    $sales = [];
    if ($result) {
        while ($row = db_fetch($result)) {
            $clean_row = [];
            foreach ($row as $key => $value) {
                if (!is_int($key)) {
                    $clean_row[$key] = $value;
                }
            }
            $sales[] = $clean_row;
        }
    }

    // Limpiar cualquier residuo de búfer o número "1" del núcleo
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "company_id" => $company,
        "logged_user" => $_SESSION["wa_current_user"]->name ?? $user,
        "total_records" => count($sales),
        "data" => $sales
    ], JSON_PRETTY_PRINT);
    exit;
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode(["success" => false, "error" => "Endpoint no encontrado"]);
exit;