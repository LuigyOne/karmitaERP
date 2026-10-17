<?php
// backend/guardar_cliente_sucursal.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once 'conexion.php';
require_once 'helpers.php'; // Contiene generarUuidV4()

$tipo_accion = $_POST['tipo_accion'] ?? ''; // 'cliente_nuevo' o 'sucursal_nueva'

try {
    $db = BaseDatos::obtenerConexion();
    $db->beginTransaction();

    // Obtener variables de sesión del vendedor y zona actual
    $usuario = $_SESSION['salesman_email'] ?? 'Desconocido';
    $vendedor_id = $_SESSION['salesman_code'] ?? 0;
    $area_code = $_SESSION['area_actual'] ?? 1;
    
    // Obtener descripción de la zona/área
    $stmt_area = $db->prepare("SELECT description FROM areas WHERE area_code = :area_code");
    $stmt_area->execute(['area_code' => $area_code]);
    $area_info = $stmt_area->fetch(PDO::FETCH_ASSOC);
    $ruta_descripcion = $area_info['description'] ?? 'Zona Principal';

    $lat = (!empty($_POST['lat']) && $_POST['lat'] != 0) ? $_POST['lat'] : null;
    $lon = (!empty($_POST['lon']) && $_POST['lon'] != 0) ? $_POST['lon'] : null;

    // A. GUARDAR

if ($tipo_accion === 'cliente_nuevo') {
        $razon_social = trim($_POST['razon_social'] ?? '');
        $nombre_comercial = trim($_POST['nombre_comercial'] ?? '');
        $ruc = trim($_POST['ruc'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $localidad = trim($_POST['localidad'] ?? '');
        $contacto_persona = trim($_POST['contacto_persona'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dias_visita = isset($_POST['dias_visita']) ? implode(',', $_POST['dias_visita']) : '';
        $frecuencia = $_POST['frecuencia'] ?? 'Semanal';
        $sales_type = intval($_POST['sales_type'] ?? 1);
        $payment_terms = intval($_POST['payment_terms'] ?? 3);

        if (empty($razon_social) || empty($ruc)) {
            throw new Exception("La Razón Social y el RUC/Cédula son obligatorios.");
        }

        // 1. Insertar en debtors_master (Incluyendo credit_status = 1)[cite: 49]
        $sql_debtor = "INSERT INTO debtors_master 
            (name, debtor_ref, address, tax_id, curr_code, sales_type, payment_terms, credit_status, credit_limit, notes, inactive) 
            VALUES (:name, :ref, :address, :tax_id, 'USD', :sales_type, :payment_terms, 1, 1000, '', 0)";
        $stmt_d = $db->prepare($sql_debtor);
        $stmt_d->execute([
            'name' => $razon_social,
            'ref' => $nombre_comercial,
            'address' => $direccion,
            'tax_id' => $ruc,
            'sales_type' => $sales_type,
            'payment_terms' => $payment_terms
        ]);
        $debtor_no = $db->lastInsertId();

        // 2. Insertar en crm_persons
        $sql_person = "INSERT INTO crm_persons (ref, name, address, phone, email, inactive) VALUES (:ref, :name, :address, :phone, :email, 0)";
        $stmt_p = $db->prepare($sql_person);
        $stmt_p->execute([
            'ref' => $nombre_comercial,
            'name' => $contacto_persona ?: $razon_social,
            'address' => $direccion,
            'phone' => $telefono,
            'email' => $email
        ]);
        $person_id = $db->lastInsertId();

        // 3. Insertar relaciones en crm_contacts
        $sql_contact = "INSERT INTO crm_contacts (person_id, type, action, entity_id) VALUES (:person_id, :type, 'general', :entity_id)";
        $stmt_c = $db->prepare($sql_contact);
        $stmt_c->execute(['person_id' => $person_id, 'type' => 'customer', 'entity_id' => $debtor_no]);

        // 4. Insertar la primera sucursal en cust_branch (Añadiendo cuentas contables predeterminadas)[cite: 50]
        $sql_branch = "INSERT INTO cust_branch 
            (debtor_no, br_name, branch_ref, br_address, latitud, longitud, area, salesman, default_location, tax_group_id, sales_account, sales_discount_account, receivables_account, payment_discount_account, default_ship_via, br_post_address, dias_visita, frecuencia_visita) 
            VALUES (:debtor_no, :br_name, :branch_ref, :br_address, :lat, :lon, :area, :salesman, 'DEF', 1, '', '5020106', '1010201', '5020106', 1, :br_post_address, :dias_visita, :frecuencia)";
        $stmt_b = $db->prepare($sql_branch);
        $stmt_b->execute([
            'debtor_no' => $debtor_no,
            'br_name' => $razon_social,
            'branch_ref' => $nombre_comercial,
            'br_address' => $direccion,
            'lat' => $lat,
            'lon' => $lon,
            'area' => $area_code,
            'salesman' => $vendedor_id,
            'br_post_address' => $direccion,
            'dias_visita' => $dias_visita,
            'frecuencia' => $frecuencia
        ]);
        $branch_code = $db->lastInsertId();

        // Actualizar el entity_id de cust_branch en crm_contacts
        $stmt_c->execute(['person_id' => $person_id, 'type' => 'cust_branch', 'entity_id' => $branch_code]);

        // (Resto de tu código de auditoría se mantiene igual)        
        // 5. Registrar en pwa_auditar (Acción NC - Cliente Nuevo)
        $uuid = generarUuidV4();
        $sql_audit = "INSERT INTO pwa_auditar 
            (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, ruta_descripcion, id_cliente, codigo_sucursal, observaciones, latitud, longitud, sync_status, device_info) 
            VALUES (:uuid, 'NC', 'Cliente Nuevo', :usuario, NOW(), :ruta_cod, :ruta_desc, :id_cliente, :codigo_sucursal, :obs, :lat, :lon, 'SINCRONIZADO', :device)";
        
        $stmt_a = $db->prepare($sql_audit);
        $stmt_a->execute([
            'uuid' => $uuid,
            'usuario' => $usuario,
            'ruta_cod' => $area_code,
            'ruta_desc' => $ruta_descripcion,
            'id_cliente' => $debtor_no,
            'codigo_sucursal' => $branch_code,
            'obs' => "Cliente Nuevo: " . $razon_social . " (" . $nombre_comercial . ")",
            'lat' => $lat,
            'lon' => $lon,
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Cliente y sucursal creados exitosamente.']);

    } elseif ($tipo_accion === 'sucursal_nueva') {
        $debtor_no = intval($_POST['debtor_no'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $localidad = trim($_POST['localidad'] ?? '');
        $contacto_persona = trim($_POST['contacto_persona'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dias_visita = isset($_POST['dias_visita']) ? implode(',', $_POST['dias_visita']) : '';
        $frecuencia = $_POST['frecuencia'] ?? 'Semanal';

        if ($debtor_no <= 0) {
            throw new Exception("Debe seleccionar un cliente válido.");
        }

        // Obtener datos maestros del cliente para poblar la sucursal
        $stmt_cli = $db->prepare("SELECT name, debtor_ref FROM debtors_master WHERE debtor_no = :debtor_no");
        $stmt_cli->execute(['debtor_no' => $debtor_no]);
        $cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            throw new Exception("El cliente seleccionado no existe.");
        }

        // 1. Insertar sucursal en cust_branch
        $sql_branch = "INSERT INTO cust_branch 
            (debtor_no, br_name, branch_ref, br_address, latitud, longitud, area, salesman, default_location, tax_group_id, br_post_address, dias_visita, frecuencia_visita) 
            VALUES (:debtor_no, :br_name, :branch_ref, :br_address, :lat, :lon, :area, :salesman, 'DEF', 1, :br_post_address, :dias_visita, :frecuencia)";
        $stmt_b = $db->prepare($sql_branch);
        $stmt_b->execute([
            'debtor_no' => $debtor_no,
            'br_name' => $cliente['name'],
            'branch_ref' => $cliente['debtor_ref'],
            'br_address' => $direccion,
            'lat' => $lat,
            'lon' => $lon,
            'area' => $area_code,
            'salesman' => $vendedor_id,
            'br_post_address' => $direccion,
            'dias_visita' => $dias_visita,
            'frecuencia' => $frecuencia
        ]);
        $branch_code = $db->lastInsertId();

        // 2. Insertar en crm_persons y crm_contacts para la nueva sucursal
        $sql_person = "INSERT INTO crm_persons (ref, name, address, phone, email, inactive) VALUES (:ref, :name, :address, :phone, :email, 0)";
        $stmt_p = $db->prepare($sql_person);
        $stmt_p->execute([
            'ref' => $cliente['debtor_ref'],
            'name' => $contacto_persona ?: $cliente['name'],
            'address' => $direccion,
            'phone' => $telefono,
            'email' => $email
        ]);
        $person_id = $db->lastInsertId();

        $sql_contact = "INSERT INTO crm_contacts (person_id, type, action, entity_id) VALUES (:person_id, 'cust_branch', 'general', :entity_id)";
        $stmt_c = $db->prepare($sql_contact);
        $stmt_c->execute(['person_id' => $person_id, 'entity_id' => $branch_code]);

        // 3. Registrar en pwa_auditar (Acción NS - Nueva Sucursal)
        $uuid = generarUuidV4();
        $sql_audit = "INSERT INTO pwa_auditar 
            (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, ruta_codigo, ruta_descripcion, id_cliente, codigo_sucursal, observaciones, latitud, longitud, sync_status, device_info) 
            VALUES (:uuid, 'NS', 'Nueva Sucursal', :usuario, NOW(), :ruta_cod, :ruta_desc, :id_cliente, :codigo_sucursal, :obs, :lat, :lon, 'SINCRONIZADO', :device)";
        
        $stmt_a = $db->prepare($sql_audit);
        $stmt_a->execute([
            'uuid' => $uuid,
            'usuario' => $usuario,
            'ruta_cod' => $area_code,
            'ruta_desc' => $ruta_descripcion,
            'id_cliente' => $debtor_no,
            'codigo_sucursal' => $branch_code,
            'obs' => "Nueva Sucursal para Cliente ID: " . $debtor_no,
            'lat' => $lat,
            'lon' => $lon,
            'device' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Nueva sucursal creada exitosamente.']);
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
