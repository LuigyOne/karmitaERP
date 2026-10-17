<?php
// backend/sri_helper.php

function obtenerUrlPlataforma() {
    $cfg_path = __DIR__ . '/../../plataforma.cfg';
    if (file_exists($cfg_path)) {
        $contenido = file_get_contents($cfg_path);
        if (preg_match('/url_plataforma\s*=\s*["\']([^"\']+)["\']/i', $contenido, $matches)) {
            return trim($matches[1]);
        }
    }
    return defined('URL_PLATAFORMA') ? URL_PLATAFORMA : 'https://api.digitalneuron.pro';
}

function asegurarTokenApiPdo($db) {
    if (!empty($_SESSION['api_token'])) {
        return true;
    }

    $url_plataforma = obtenerUrlPlataforma();
    if (empty($url_plataforma)) {
        return false;
    }

    $stmt = $db->query("SELECT name, value FROM " . TB_PREF . "sys_prefs WHERE name IN ('api_usuario', 'api_password')");
    $credenciales = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $credenciales[$row['name']] = $row['value'];
    }

    $api_usuario = $credenciales['api_usuario'] ?? '';
    $api_password = $credenciales['api_password'] ?? '';

    if (empty($api_usuario) || empty($api_password)) {
        return false;
    }

    $endpoint = rtrim($url_plataforma, '/') . '/auth/login';
    $payload = json_encode(['email' => $api_usuario, 'password' => $api_password]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300 && $response) {
        $data = json_decode($response, true);
        if (isset($data['accessToken'])) {
            $_SESSION['api_token'] = $data['accessToken'];
            $_SESSION['api_token_type'] = $data['tokenType'] ?? 'Bearer';
            return true;
        }
    }
    return false;
}

function sincronizarFacturaSri($db, $invoice_no, $cod_clien, $cod_sucur, $bodega_movil, $vendedor_code, $items, $total_orden, $notas_pedido) {
    $log_file_sri = __DIR__ . '/sri_debug.log';
    
    if (!asegurarTokenApiPdo($db)) {
        $db->exec("UPDATE " . TB_PREF . "debtor_trans SET sri_estado = 0, sri_mensaje = 'Error: No se pudo autenticar con la API del SRI' WHERE type = 10 AND trans_no = " . intval($invoice_no));
        return [
            'sri_estado' => 0,
            'sri_mensaje' => 'Error: No se pudo autenticar con la API del SRI',
            'sri_secuencial' => '001-010-' . str_pad($invoice_no, 9, "0", STR_PAD_LEFT)
        ];
    }

    // 1. Obtener datos del cliente, dirección (cust_branch), teléfono y correo (crm_contacts / crm_persons)[cite: 44, 45, 46, 47]
    $stmt_cli = $db->prepare("SELECT d.name, d.tax_id, b.br_address, p.phone, p.email 
        FROM " . TB_PREF . "debtors_master d 
        LEFT JOIN " . TB_PREF . "cust_branch b ON d.debtor_no = b.debtor_no AND b.branch_code = ?
        LEFT JOIN " . TB_PREF . "crm_contacts c ON c.entity_id = d.debtor_no AND c.type = 'customer'
        LEFT JOIN " . TB_PREF . "crm_persons p ON c.person_id = p.id
        WHERE d.debtor_no = ? LIMIT 1");
    $stmt_cli->execute([$cod_sucur, $cod_clien]);
    $cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $stmt_cli = $db->prepare("SELECT name, tax_id, '' as br_address, '' as phone, '' as email FROM " . TB_PREF . "debtors_master WHERE debtor_no = ? LIMIT 1");
        $stmt_cli->execute([$cod_clien]);
        $cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'CONSUMIDOR FINAL', 'tax_id' => '9999999999999', 'br_address' => 'Ecuador', 'phone' => '', 'email' => ''];
    }

    $identificacion_comprador = trim($cliente['tax_id'] ?? '9999999999999');
    $tipo_identificacion = '07';
    if ($identificacion_comprador == '9999999999' || $identificacion_comprador == '9999999999999' || empty($identificacion_comprador)) {
        $identificacion_comprador = '9999999999999';
        $tipo_identificacion = '07';
    } elseif (strlen($identificacion_comprador) == 10) {
        $tipo_identificacion = '05'; // Cédula
    } elseif (strlen($identificacion_comprador) == 13) {
        $tipo_identificacion = '04'; // RUC
    } else {
        $identificacion_comprador = '9999999999999';
        $tipo_identificacion = '07';
    }

    // 2. Obtener datos de empresa / emisor desde sys_prefs
    $stmt_prefs = $db->query("SELECT name, value FROM " . TB_PREF . "sys_prefs WHERE name IN ('coy_name', 'coy_no', 'gst_no', 'postal_address', 'sri_ambiente_emision', 'sri_obligado_contabilidad')");
    $prefs = [];
    while ($row = $stmt_prefs->fetch(PDO::FETCH_ASSOC)) {
        $prefs[$row['name']] = $row['value'];
    }

    // 3. Obtener códigos de establecimiento y punto de emisión de la bodega/ubicación
    $establecimiento = '001';
    $punto_emision = '010';
    if (!empty($bodega_movil)) {
        $stmt_loc = $db->prepare("SELECT establishment_code, emission_point_code FROM " . TB_PREF . "locations WHERE loc_code = ? LIMIT 1");
        $stmt_loc->execute([$bodega_movil]);
        $loc = $stmt_loc->fetch(PDO::FETCH_ASSOC);
        if ($loc) {
            if (!empty($loc['establishment_code'])) {
                $establecimiento = str_pad(trim($loc['establishment_code']), 3, "0", STR_PAD_LEFT);
            }
            if (!empty($loc['emission_point_code'])) {
                $punto_emision = str_pad(trim($loc['emission_point_code']), 3, "0", STR_PAD_LEFT);
            }
        }
    }

    // 4. Construir detalles de la factura línea por línea con impuestos
    $detalles_sri = [];
    foreach ($items as $item) {
        $stock_id = $item['stock_id'] ?? '';
        $cantidad = floatval($item['cantidad'] ?? 0);
        $precio = floatval($item['precio'] ?? 0);

        if (empty($stock_id) || $cantidad <= 0) continue;

        $precio_total_sin_impuesto = round($precio * $cantidad, 2);
        $descuento = 0.00;
        $base_imponible = $precio_total_sin_impuesto - $descuento;

        $descripcion_prod = $stock_id;
        $tax_rate = 15; // Valor por defecto
        
        $stmt_prod = $db->prepare("SELECT s.description, t.rate FROM " . TB_PREF . "stock_master s LEFT JOIN " . TB_PREF . "item_tax_types i ON s.tax_type_id = i.id LEFT JOIN " . TB_PREF . "tax_types t ON i.id = t.id WHERE s.stock_id = ? LIMIT 1");
        $stmt_prod->execute([$stock_id]);
        $p_row = $stmt_prod->fetch(PDO::FETCH_ASSOC);
        if ($p_row) {
            if (!empty($p_row['description'])) $descripcion_prod = $p_row['description'];
            if (isset($p_row['rate'])) $tax_rate = (float)$p_row['rate'];
        }

        $codigo_porcentaje = ($tax_rate > 0) ? "4" : "0";
        $tarifa_sri = (int)$tax_rate;
        $valor_impuesto = round($base_imponible * ($tax_rate / 100), 2);

        $detalles_sri[] = [
            "codigoPrincipal" => $stock_id,
            "codigoAuxiliar" => $stock_id,
            "descripcion" => $descripcion_prod,
            "cantidad" => $cantidad,
            "precioUnitario" => round($precio, 6),
            "descuento" => $descuento,
            "impuestos" => [
                [
                    "codigo" => "2",
                    "codigoPorcentaje" => $codigo_porcentaje,
                    "tarifa" => $tarifa_sri,
                    "baseImponible" => $base_imponible,
                    "valor" => $valor_impuesto
                ]
            ]
        ];
    }

    $fecha_api = date('d/m/Y');
    $secuencial = str_pad($invoice_no, 9, "0", STR_PAD_LEFT);
    $url_plataforma_base = obtenerUrlPlataforma();

    // Construir infoAdicional de forma dinámica omitiendo valores vacíos
    $info_adicional = [
        ["nombre" => "Vendedor", "valor" => "Vendedor Móvil"]
    ];

    if (!empty($cliente['phone'])) {
        $info_adicional[] = ["nombre" => "Teléfono", "valor" => trim($cliente['phone'])];
    }
    if (!empty($cliente['email'])) {
        $info_adicional[] = ["nombre" => "Correo", "valor" => trim($cliente['email'])];
    }
    if (!empty($notas_pedido)) {
        $info_adicional[] = ["nombre" => "Observaciones", "valor" => trim($notas_pedido)];
    }

    // 5. Construir el Payload JSON exacto exigido por la API del SRI
    $payload = [
        "ambiente" => $prefs['sri_ambiente_emision'] ?? "1",
        "tipoEmision" => "1",
        "fechaEmision" => $fecha_api,
        "emisor" => [
            "ruc" => $prefs['gst_no'] ?? "0000000000001",
            "razonSocial" => $prefs['coy_name'] ?? "EMPRESA S.A.",
            "nombreComercial" => $prefs['coy_no'] ?? "COMERCIAL",
            "dirMatriz" => $prefs['postal_address'] ?? "Ecuador",
            "dirEstablecimiento" => $prefs['postal_address'] ?? "Ecuador",
            "establecimiento" => $establecimiento,
            "puntoEmision" => $punto_emision,
            "obligadoContabilidad" => (isset($prefs['sri_obligado_contabilidad']) && $prefs['sri_obligado_contabilidad'] == 1) ? "SI" : "NO"
        ],
        "comprador" => [
            "tipoIdentificacion" => $tipo_identificacion,
            "identificacion" => $identificacion_comprador,
            "razonSocial" => $cliente['name'] ?? 'CONSUMIDOR FINAL',
            "direccion" => !empty($cliente['br_address']) ? $cliente['br_address'] : 'Sin dirección'
        ],
        "detalles" => $detalles_sri,
        "pagos" => [
            [
                "formaPago" => "01",
                "total" => round($total_orden, 2)
            ]
        ],
        "infoAdicional" => $info_adicional
    ];

    $api_url = rtrim($url_plataforma_base, '/') . '/sri/emitir/factura';
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: ' . ($_SESSION['api_token_type'] ?? 'Bearer') . ' ' . $_SESSION['api_token']
    ];

    // 6. Ejecutar llamada cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 7. Registrar logs
    $log_data = "[" . date('Y-m-d H:i:s') . "] INVOICE #{$invoice_no}\n";
    $log_data .= "PAYLOAD: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";
    $log_data .= "RESPONSE: " . ($response ? $response : "CURL ERROR: " . $curl_error) . "\n";
    $log_data .= "---------------------------------------------------\n";
    @file_put_contents($log_file_sri, $log_data, FILE_APPEND);

    $sri_estado = 0;
    $sri_clave = '';
    $sri_fecha_aut = null;
    $sri_secuencial = $establecimiento . '-' . $punto_emision . '-' . $secuencial;
    $sri_mensaje = '';

    if ($http_code >= 200 && $http_code < 300 && $response) {
        $api_result = json_decode($response, true);
        if (isset($api_result['success']) && $api_result['success'] == true && isset($api_result['estado']) && $api_result['estado'] == 'AUTORIZADO') {
            $sri_estado = 1;
            $sri_clave = $api_result['claveAcceso'] ?? '';
            $sri_fecha_aut = date('Y-m-d H:i:s', strtotime($api_result['fechaAutorizacion'] ?? 'now'));
            $sri_mensaje = 'AUTORIZADO';

            if (!empty($api_result['xmlAutorizado'])) {
                $xml = @simplexml_load_string($api_result['xmlAutorizado']);
                if ($xml && isset($xml->infoTributaria)) {
                    $estab = (string)$xml->infoTributaria->estab;
                    $ptoEmi = (string)$xml->infoTributaria->ptoEmi;
                    $sec_xml = (string)$xml->infoTributaria->secuencial;
                    $sri_secuencial = $estab . '-' . $ptoEmi . '-' . $sec_xml;
                }
            }
        } else {
            $sri_estado = 2;
            $sri_clave = $api_result['claveAcceso'] ?? '';
            if (isset($api_result['mensajes']) && count($api_result['mensajes']) > 0) {
                $sri_mensaje = $api_result['mensajes'][0]['mensaje'] . ' - ' . $api_result['mensajes'][0]['informacionAdicional'];
            } else {
                $sri_mensaje = $api_result['message'] ?? substr('RAW: ' . $response, 0, 240);
            }
        }
    } else {
        $sri_estado = 2;
        $sri_mensaje = 'Error cURL HTTP ' . $http_code . ': ' . ($response ? $response : $curl_error);
    }

    // 8. Actualizar base de datos
    $sql_upd = "UPDATE " . TB_PREF . "debtor_trans SET
        sri_estado = :estado,
        sri_clave_acceso = :clave,
        sri_fecha_autorizacion = :f_aut,
        sri_secuencial = :sec,
        sri_mensaje = :msg
        WHERE type = 10 AND trans_no = :trans_no AND debtor_no = :debtor_no";

    $stmt_upd = $db->prepare($sql_upd);
    $stmt_upd->execute([
        'estado' => $sri_estado,
        'clave' => $sri_clave,
        'f_aut' => $sri_fecha_aut,
        'sec' => $sri_secuencial,
        'msg' => $sri_mensaje,
        'trans_no' => $invoice_no,
        'debtor_no' => $cod_clien
    ]);

    return [
        'sri_estado' => $sri_estado,
        'sri_mensaje' => $sri_mensaje,
        'sri_secuencial' => $sri_secuencial
    ];
}
