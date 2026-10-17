<?php
// miniapi/sales.php
require_once __DIR__ . '/auth.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$sql = "SELECT trans_no, type, debtor_no, tran_date, due_date, reference, ov_amount, ov_gst 
        FROM " . TB_PREF . "debtor_trans 
        WHERE type = 10 
        ORDER BY trans_no DESC LIMIT " . $limit;
        
$result = db_query($sql, "No se pudieron consultar las ventas");
$sales = [];

if ($result) {
    while ($row = db_fetch($result)) {
        $clean_row = [];
        foreach ($row as $key => $value) {
            if (!is_int($key)) { $clean_row[$key] = $value; }
        }
        $sales[] = $clean_row;
    }
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "success" => true,
    "company_id" => $company,
    "logged_user" => $_SESSION["wa_current_user"]->name ?? $user,
    "total_records" => count($sales),
    "data" => $sales
], JSON_PRETTY_PRINT);
exit;
?>