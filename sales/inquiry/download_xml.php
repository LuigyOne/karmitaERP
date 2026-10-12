<?php
/**********************************************************************
    Script de Descarga Segura de XML (Con Autenticación Automática)
***********************************************************************/
$path_to_root = '../..';
$page_security = 'SA_SALESINVOICE';

include_once($path_to_root . '/includes/session.inc');
include_once($path_to_root . '/sales/includes/sales_db.inc');

if (!isset($_GET['clave']) || empty($_GET['clave'])) {
    display_error(_('Clave de acceso no especificada.'));
    end_page();
    exit;
}

$clave_acceso = trim($_GET['clave']);
$access_token = '';

// 1. Verificamos si ya tenemos un token válido en la sesión de PHP
if (isset($_SESSION['sri_token']) && isset($_SESSION['sri_token_expires']) && $_SESSION['sri_token_expires'] > time()) {
    $access_token = $_SESSION['sri_token'];
} else {
    // 2. Si no existe o expiró, nos autenticamos automáticamente de forma transparente
    $auth_url = 'https://api.digitalneuron.pro/auth/login';
    $auth_payload = json_encode(array(
        "email" => "superadmin@openapi-sri.com",
        "password" => "Admin123!" 
    ));

    $ch_auth = curl_init($auth_url);
    curl_setopt($ch_auth, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_auth, CURLOPT_POST, true);
    curl_setopt($ch_auth, CURLOPT_POSTFIELDS, $auth_payload);
    curl_setopt($ch_auth, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch_auth, CURLOPT_TIMEOUT, 10);
    $auth_response = curl_exec($ch_auth);
    curl_close($ch_auth);

    if ($auth_response) {
        $auth_data = json_decode($auth_response, true);
        if (isset($auth_data['accessToken'])) {
            $access_token = $auth_data['accessToken'];
            $_SESSION['sri_token'] = $access_token;
            $_SESSION['sri_token_expires'] = time() + 80000; 
        }
    }
}

// Si a pesar del intento automático no hay token, mostramos error
if (empty($access_token)) {
    display_error(_('Error de autenticación automática con la API de Digital Neuron. Verifique las credenciales.'));
    end_page();
    exit;
}

// 3. Ejecutar llamada cURL al endpoint de descarga de XML usando el token Bearer
$api_url = 'https://api.digitalneuron.pro/sri/comprobantes/' . $clave_acceso . '/xml';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token
));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200 && !empty($response)) {
    // Forzar la descarga limpia del archivo XML en el navegador
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $clave_acceso . '.xml"');
    echo $response;
    exit;
} else {
    display_error(sprintf(_('No se pudo descargar el XML desde la API (Código HTTP: %d). Verifique si el comprobante existe y está autorizado.'), $http_code));
    end_page();
}
?>
