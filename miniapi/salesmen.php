<?php
// miniapi/salesmen.php
require_once __DIR__ . '/auth.php';

// Cargar la entidad de vendedores del ERP
include_once($path_to_root . "/sales/includes/db/salesman_entity.inc");

$method = $_SERVER['REQUEST_METHOD'];

// Capturar el ID opcional mediante parámetro GET (ej: /miniapi/salesmen?id=3)
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

// Obtener el cuerpo de la petición en formato JSON (para POST y PUT)
$input_data = json_decode(file_get_contents('php://input'), true);

$response = ["success" => false];

switch ($method) {
    // ==========================================
    // 1. LISTAR O CONSULTAR (GET)
    // ==========================================
    case 'GET':
        if ($id) {
            $myrow = salesman_entity::find($id);
            if ($myrow) {
                // Limpiar claves numéricas si las hubiera
                $clean_row = [];
                foreach ($myrow as $key => $value) {
                    if (!is_int($key)) { $clean_row[$key] = $value; }
                }
                $response = ["success" => true, "data" => $clean_row];
            } else {
                http_response_code(404);
                $response = ["success" => false, "error" => "Vendedor no encontrado"];
            }
        } else {
            $result = salesman_entity::all_db_resource('!inactive');
            $salesmen = [];
            if ($result) {
                while ($row = db_fetch($result)) {
                    $clean_row = [];
                    foreach ($row as $key => $value) {
                        if (!is_int($key)) { $clean_row[$key] = $value; }
                    }
                    $salesmen[] = $clean_row;
                }
            }
            $response = [
                "success" => true,
                "total_records" => count($salesmen),
                "data" => $salesmen
            ];
        }
        break;

    // ==========================================
    // 2. CREAR NUEVO VENDEDOR (POST)
    // ==========================================
    case 'POST':
        $name = isset($input_data['salesman_name']) ? trim($input_data['salesman_name']) : '';
        
        if (empty($name)) {
            http_response_code(400);
            $response = ["success" => false, "error" => "El nombre del vendedor (salesman_name) es obligatorio"];
            break;
        }

        $sdata = array(
            'salesman_name'  => $name,
            'salesman_phone' => isset($input_data['salesman_phone']) ? $input_data['salesman_phone'] : '',
            'salesman_fax'   => isset($input_data['salesman_fax']) ? $input_data['salesman_fax'] : '',
            'salesman_email' => isset($input_data['salesman_email']) ? $input_data['salesman_email'] : '',
            'provision'      => isset($input_data['provision']) ? floatval($input_data['provision']) : 0,
            'break_pt'       => isset($input_data['break_pt']) ? floatval($input_data['break_pt']) : 0,
            'provision2'     => isset($input_data['provision2']) ? floatval($input_data['provision2']) : 0,
        );

        $new_id = salesman_entity::create($sdata);
        if ($new_id) {
            http_response_code(201);
            $response = ["success" => true, "message" => "Vendedor creado exitosamente", "salesman_code" => $new_id];
        } else {
            http_response_code(500);
            $response = ["success" => false, "error" => "No se pudo crear el vendedor"];
        }
        break;

    // ==========================================
    // 3. EDITAR / ACTUALIZAR VENDEDOR (PUT)
    // ==========================================
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            $response = ["success" => false, "error" => "Debe especificar el ID en la URL (ej: /miniapi/salesmen?id=3)"];
            break;
        }

        $existing = salesman_entity::find($id);
        if (!$existing) {
            http_response_code(404);
            $response = ["success" => false, "error" => "El vendedor con ID $id no existe"];
            break;
        }

        $sdata = array(
            'salesman_name'  => isset($input_data['salesman_name']) ? $input_data['salesman_name'] : $existing['salesman_name'],
            'salesman_phone' => isset($input_data['salesman_phone']) ? $input_data['salesman_phone'] : $existing['salesman_phone'],
            'salesman_fax'   => isset($input_data['salesman_fax']) ? $input_data['salesman_fax'] : $existing['salesman_fax'],
            'salesman_email' => isset($input_data['salesman_email']) ? $input_data['salesman_email'] : $existing['salesman_email'],
            'provision'      => isset($input_data['provision']) ? floatval($input_data['provision']) : $existing['provision'],
            'break_pt'       => isset($input_data['break_pt']) ? floatval($input_data['break_pt']) : $existing['break_pt'],
            'provision2'     => isset($input_data['provision2']) ? floatval($input_data['provision2']) : $existing['provision2'],
        );

        salesman_entity::modify($id, $sdata);
        $response = ["success" => true, "message" => "Vendedor $id actualizado exitosamente"];
        break;

    // ==========================================
    // 4. ELIMINAR VENDEDOR (DELETE)
    // ==========================================
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            $response = ["success" => false, "error" => "Debe especificar el ID en la URL (ej: /miniapi/salesmen?id=3)"];
            break;
        }

        if (key_in_foreign_table($id, 'cust_branch', 'salesman')) {
            http_response_code(409);
            $response = ["success" => false, "error" => "No se puede eliminar este vendedor porque existen sucursales de clientes asociadas a él."];
            break;
        }

        salesman_entity::remove($id);
        $response = ["success" => true, "message" => "Vendedor $id eliminado exitosamente"];
        break;

    default:
        http_response_code(405);
        $response = ["success" => false, "error" => "Método HTTP no permitido"];
        break;
}

while (ob_get_level() > 0) { ob_end_clean(); }
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>