<?php
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

// Inicializar variables globales de ruta y vendedor (Sincronizando area_actual y area_code)
$GLOBAL_VENDEDOR_NOMBRE = $_SESSION['user_real_name'] ?? $_SESSION['salesman_name'] ?? 'Vendedor';
$GLOBAL_VENDEDOR_CODIGO = $_SESSION['user_id'] ?? $_SESSION['salesman_code'] ?? '001';

// Sincronizar sesiones de área
if (!isset($_SESSION['area_actual']) && isset($_SESSION['area_code'])) {
    $_SESSION['area_actual'] = $_SESSION['area_code'];
}
$GLOBAL_AREA_ACTUAL = $_SESSION['area_actual'] ?? $_SESSION['area_code'] ?? '1';

$GLOBAL_NOMBRE_AREA = 'Zona Principal';
$GLOBAL_BODEGA_NOMBRE = 'Bodega Central';
$GLOBAL_DIA_ACTUAL = isset($_GET['dia']) ? $_GET['dia'] : 'TODOS';

// Datos de la empresa almacenados en sesión
$GLOBAL_COMPANY_COY_NAME = $_SESSION['company_coy_name'] ?? 'SANCHEZ ESCOBAR RONALD';
$GLOBAL_COMPANY_COY_NO   = $_SESSION['company_coy_no'] ?? 'SANCHEZ VERA DISTRIBUCION';
$GLOBAL_COMPANY_RUC      = $_SESSION['company_ruc'] ?? '1759318114001';
$GLOBAL_COMPANY_DOMICILE = $_SESSION['company_domicile'] ?? '';
$GLOBAL_COMPANY_POSTAL   = $_SESSION['company_postal'] ?? '';
$GLOBAL_COMPANY_EMAIL    = $_SESSION['company_email'] ?? '';
$GLOBAL_COMPANY_PHONE    = $_SESSION['company_phone'] ?? '';

$GLOBAL_AREAS_VENTA = [];
$GLOBAL_SUCURSALES = [];

try {
    $db = BaseDatos::obtenerConexion();

    // 1. Obtener áreas de venta disponibles
    $stmt_areas = $db->prepare("SELECT area_code, description FROM " . TB_PREF . "areas ORDER BY description");
    $stmt_areas->execute();
    while ($row = $stmt_areas->fetch(PDO::FETCH_ASSOC)) {
        array_walk($row, function(&$val) {
            if (is_string($val)) $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        });
        $GLOBAL_AREAS_VENTA[] = $row;
        if ($row['area_code'] == $GLOBAL_AREA_ACTUAL) {
            $GLOBAL_NOMBRE_AREA = $row['description'];
        }
    }

    // 2. Consulta principal de sucursales filtrada por zona (Área) y día
    $sql = "SELECT cb.branch_code, cb.branch_ref, cb.br_name, cb.br_address, cb.dias_visita, cb.frecuencia_visita,
                   cp.phone, c.debtor_no, c.name as customer_name
            FROM " . TB_PREF . "cust_branch cb 
            INNER JOIN " . TB_PREF . "debtors_master c ON cb.debtor_no = c.debtor_no 
            LEFT JOIN " . TB_PREF . "crm_contacts cc ON cc.type = 'cust_branch' AND cc.entity_id = cb.branch_code
            LEFT JOIN " . TB_PREF . "crm_persons cp ON cc.person_id = cp.id
            WHERE c.inactive = 0 AND cb.inactive = 0 AND cb.area = :area_code";

    $params = ['area_code' => $GLOBAL_AREA_ACTUAL];

    // Si se selecciona un día específico (L, M, X, J, V, S, D), filtramos por la columna correspondiente
    if ($GLOBAL_DIA_ACTUAL !== 'TODOS') {
        $sql .= " AND cb.dias_visita LIKE :dia";
        $params['dia'] = '%' . $GLOBAL_DIA_ACTUAL . '%';
    }

    $sql .= " ORDER BY cb.branch_ref ASC, c.name ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resultadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decodificar entidades HTML globalmente en todas las sucursales
    foreach ($resultadosRaw as $row) {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        $GLOBAL_SUCURSALES[] = $row;
    }

} catch (Exception $e) {
    $GLOBAL_SUCURSALES = [];
}