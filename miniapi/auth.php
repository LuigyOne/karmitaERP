<?php
// miniapi/auth.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Limpiar búferes iniciales
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

$path_to_root = "..";

// Capturar cabeceras HTTP
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

// Inyectar credenciales en $_POST para simular el login del ERP
$_POST['company_login_name'] = $company;
$_POST['user_name_entry_field'] = $user;
$_POST['password'] = $pass;
$_POST['ui_mode'] = 0;

// Cargar entorno de sesión personalizado
include_once($path_to_root . "/miniapi/session_luis.inc");

// Validar login exitoso
if (!isset($_SESSION["wa_current_user"]) || !$_SESSION["wa_current_user"]->logged_in()) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Credenciales incorrectas o acceso denegado para el usuario: " . $user]);
    exit;
}

// Establecer conexión global a la compañía seleccionada
set_global_connection($company);
?>