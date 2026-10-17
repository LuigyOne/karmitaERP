<?php
// miniapi/customers.php
require_once __DIR__ . '/auth.php';

$sql = "SELECT debtor_no, debtor_ref, name, tax_id, address, curr_code, credit_limit, inactive 
        FROM " . TB_PREF . "debtors_master 
        ORDER BY name ASC LIMIT 50";
        
$result = db_query($sql, "No se pudieron consultar los clientes");
$customers = [];

if ($result) {
    while ($row = db_fetch($result)) {
        $clean_row = [];
        foreach ($row as $key => $value) {
            if (!is_int($key)) { $clean_row[$key] = $value; }
        }
        $customers[] = $clean_row;
    }
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "success" => true,
    "company_id" => $company,
    "logged_user" => $_SESSION["wa_current_user"]->name ?? $user,
    "total_records" => count($customers),
    "data" => $customers
], JSON_PRETTY_PRINT);
exit;
?>