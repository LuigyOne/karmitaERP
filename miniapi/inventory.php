<?php
// miniapi/inventory.php
require_once __DIR__ . '/auth.php';

// Capturar el parámetro opcional de bodega (?loc=DEF)
$loc_code = isset($_GET['loc']) ? trim($_GET['loc']) : '';

// Construir la consulta base
$sql = "SELECT sm.stock_id, ic.description, sm.loc_code, SUM(sm.qty) AS stock_calculado 
        FROM " . TB_PREF . "stock_moves sm
        LEFT JOIN " . TB_PREF . "item_codes ic ON sm.stock_id = ic.stock_id";

// Si se envía el parámetro 'loc', aplicamos el filtro de manera segura
if (!empty($loc_code)) {
    $sql .= " WHERE sm.loc_code = " . db_escape($loc_code);
}

$sql .= " GROUP BY sm.stock_id, sm.loc_code
          LIMIT 50";
        
$result = db_query($sql, "No se pudo calcular el inventario");
$inventory = [];

if ($result) {
    while ($row = db_fetch($result)) {
        $clean_row = [];
        foreach ($row as $key => $value) {
            if (!is_int($key)) { $clean_row[$key] = $value; }
        }
        $inventory[] = $clean_row;
    }
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "success" => true,
    "company_id" => $company,
    "logged_user" => $_SESSION["wa_current_user"]->name ?? $user,
    "filtro_bodega" => empty($loc_code) ? "TODAS" : $loc_code,
    "total_records" => count($inventory),
    "data" => $inventory
], JSON_PRETTY_PRINT);
exit;
?>