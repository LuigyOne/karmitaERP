<?php
/**********************************************************************
    NotrinosERP - Endpoint Unificado de Geolocalización (Fijos, Móviles y Clientes)
***********************************************************************/
$page_security = 'SA_OPEN';
$path_to_root  = '..';

include_once($path_to_root . '/includes/session.inc');

header('Content-Type: application/json; charset=utf-8');

function get_crm_geo_pref($name, $default = '') {
    $sql = "SELECT value FROM " . TB_PREF . "sys_prefs 
            WHERE name = " . db_escape($name) . " AND category = 'crm_geo'";
    $res = db_query($sql, "Error leyendo preferencia geo");
    if ($res && db_num_rows($res) > 0) {
        $row = db_fetch($res);
        return trim($row['value']);
    }
    return $default;
}

function normalize_traccar_url($url) {
    $url = trim($url);
    if ($url === '') {
        return 'https://gps.digitalneuron.pro';
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }
    $url = rtrim($url, '/');
    // Evitar duplicación si el usuario escribió .../api o .../api/
    $url = preg_replace('#/api$#i', '', $url);
    return $url;
}

function traccar_api_request($endpoint, $base_url, $user, $pass) {
    $url = $base_url . $endpoint;
    $ch  = curl_init($url);

    $headers = ['Accept: application/json'];
    if ($pass !== '') {
        curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $pass);
    } elseif ($user !== '') {
        $headers[] = 'Authorization: Bearer ' . $user;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || $httpCode !== 200 || !$response) {
        $detail = $error ?: ($httpCode === 401 ? 'HTTP 401 (Revisar usuario/clave API)' : "HTTP $httpCode en $url");
        return ['ok' => false, 'error' => $detail, 'data' => []];
    }

    $decoded = json_decode($response, true);
    return ['ok' => is_array($decoded), 'error' => '', 'data' => is_array($decoded) ? $decoded : []];
}

function format_ecuador_time($iso_date) {
    if (empty($iso_date)) return 'Sin reporte';
    try {
        $dt = new DateTime($iso_date);
        $dt->setTimezone(new DateTimeZone('America/Guayaquil'));
        return $dt->format('d/m/Y H:i:s');
    } catch (Exception $e) {
        return $iso_date;
    }
}

try {
    $elementos      = [];
    $traccar_status = 'not_needed';

    // 1. Consultar elementos activos en pwa_dispositivos_bodegas
    $sql_dev = "SELECT d.*, s.salesman_name
                FROM " . TB_PREF . "pwa_dispositivos_bodegas d
                LEFT JOIN " . TB_PREF . "salesman s ON d.salesman_code = s.salesman_code
                WHERE d.estado = 'activo'
                ORDER BY d.id ASC";
    $res_dev = db_query($sql_dev, "Error al consultar elementos de rastreo");

    $registros     = [];
    $tiene_moviles = false;
    while ($row = db_fetch_assoc($res_dev)) {
        if ((int)$row['es_movil'] === 1 && !empty(trim($row['device_id']))) {
            $tiene_moviles = true;
        }
        $registros[] = $row;
    }

    // 2. Consultar última ubicación en Traccar para elementos móviles
    $traccar_por_unique_id = [];
    if ($tiene_moviles) {
        $raw_url      = get_crm_geo_pref('traccar_url', 'https://gps.digitalneuron.pro');
        $traccar_url  = normalize_traccar_url($raw_url);
        $traccar_user = get_crm_geo_pref('traccar_user', '');
        $traccar_pass = get_crm_geo_pref('traccar_pass', '');

        $dev_resp = traccar_api_request('/api/devices', $traccar_url, $traccar_user, $traccar_pass);
        $pos_resp = traccar_api_request('/api/positions', $traccar_url, $traccar_user, $traccar_pass);

        if ($dev_resp['ok'] && $pos_resp['ok']) {
            $traccar_status = 'connected';
            $pos_by_id = [];
            foreach ($pos_resp['data'] as $p) {
                if (isset($p['deviceId'])) {
                    $pos_by_id[$p['deviceId']] = $p;
                }
            }
            foreach ($dev_resp['data'] as $d) {
                $uid = trim((string)($d['uniqueId'] ?? ''));
                $did = $d['id'] ?? null;
                if ($uid !== '' && $did !== null && isset($pos_by_id[$did])) {
                    $traccar_por_unique_id[$uid] = [
                        'device'   => $d,
                        'position' => $pos_by_id[$did]
                    ];
                }
            }
        } else {
            $traccar_status = 'error: ' . ($dev_resp['error'] ?: $pos_resp['error']);
        }
    }

    // 3. Procesar pines fijos y móviles
    $moviles_pintados = 0;
    foreach ($registros as $row) {
        $es_movil = ((int)$row['es_movil'] === 1);
        $uid      = trim((string)$row['device_id']);
        $titulo   = !empty(trim($row['descripcion'])) 
                    ? $row['descripcion'] 
                    : (!empty($row['salesman_name']) ? $row['salesman_name'] : $row['elemento_tipo'] . ' #' . $row['salesman_code']);

        if ($es_movil) {
            if ($uid !== '' && isset($traccar_por_unique_id[$uid])) {
                $t_dev = $traccar_por_unique_id[$uid]['device'];
                $t_pos = $traccar_por_unique_id[$uid]['position'];

                $lat = (float)($t_pos['latitude'] ?? 0);
                $lng = (float)($t_pos['longitude'] ?? 0);

                if ($lat != 0.0 || $lng != 0.0) {
                    $velocidad_kmh = round(((float)($t_pos['speed'] ?? 0)) * 1.852, 1);
                    $bateria       = $t_pos['attributes']['batteryLevel'] ?? null;
                    $moviles_pintados++;

                    $elementos[] = [
                        'id'               => 'pwa_' . $row['id'],
                        'tipo'             => $row['elemento_tipo'],
                        'es_movil'         => true,
                        'lat'              => $lat,
                        'lng'              => $lng,
                        'nombre'           => $titulo,
                        'codigo'           => $row['salesman_code'],
                        'responsable'      => $row['salesman_name'] ?? '',
                        'device_id'        => $uid,
                        'estado_gps'       => $t_dev['status'] ?? 'unknown',
                        'velocidad'        => $velocidad_kmh . ' km/h',
                        'bateria'          => $bateria !== null ? $bateria . '%' : 'N/D',
                        'ultima_actividad' => format_ecuador_time($t_pos['fixTime'] ?? ($t_dev['lastUpdate'] ?? ''))
                    ];
                }
            }
        } else {
            $lat = (float)($row['latitud'] ?? 0);
            $lng = (float)($row['longitud'] ?? 0);

            if ($lat != 0.0 || $lng != 0.0) {
                $elementos[] = [
                    'id'               => 'pwa_' . $row['id'],
                    'tipo'             => $row['elemento_tipo'],
                    'es_movil'         => false,
                    'lat'              => $lat,
                    'lng'              => $lng,
                    'nombre'           => $titulo,
                    'codigo'           => $row['salesman_code'],
                    'responsable'      => $row['salesman_name'] ?? '',
                    'device_id'        => $uid !== '' ? $uid : 'Punto Fijo',
                    'estado_gps'       => 'fixed',
                    'velocidad'        => '0 km/h',
                    'bateria'          => 'N/A',
                    'ultima_actividad' => 'Coordenada Estática'
                ];
            }
        }
    }

    // 4. Consultar geocoordenadas de Clientes en NotrinosERP (cust_branch)
    $cols_res = db_query("SHOW COLUMNS FROM " . TB_PREF . "cust_branch", "Error verificando columnas de clientes");
    $cols = [];
    while ($c = db_fetch_assoc($cols_res)) {
        $cols[] = strtolower($c['Field']);
    }

    $col_lat = in_array('latitud', $cols) ? 'latitud' : (in_array('latitude', $cols) ? 'latitude' : null);
    $col_lng = in_array('longitud', $cols) ? 'longitud' : (in_array('longitude', $cols) ? 'longitude' : null);

    if ($col_lat && $col_lng) {
        $sql_cli = "SELECT b.branch_code, b.branch_ref, b.br_name, b.br_address,
                           b.$col_lat AS lat, b.$col_lng AS lng,
                           d.name AS customer_name, d.debtor_ref
                    FROM " . TB_PREF . "cust_branch b
                    INNER JOIN " . TB_PREF . "debtors_master d ON b.debtor_no = d.debtor_no
                    WHERE b.inactive = 0
                      AND b.$col_lat IS NOT NULL 
                      AND b.$col_lng IS NOT NULL
                      AND b.$col_lat != 0 
                      AND b.$col_lng != 0";

        $res_cli = db_query($sql_cli, "Error al consultar geocoordenadas de clientes");
        while ($cli = db_fetch_assoc($res_cli)) {
            $elementos[] = [
                'id'               => 'cli_' . $cli['branch_code'],
                'tipo'             => 'Cliente',
                'es_movil'         => false,
                'lat'              => (float)$cli['lat'],
                'lng'              => (float)$cli['lng'],
                'nombre'           => $cli['customer_name'],
                'codigo'           => $cli['debtor_ref'] ?: $cli['branch_code'],
                'responsable'      => $cli['br_name'],
                'device_id'        => 'Sucursal: ' . $cli['br_name'],
                'estado_gps'       => 'fixed',
                'velocidad'        => '0 km/h',
                'bateria'          => 'N/A',
                'ultima_actividad' => trim($cli['br_address']) ?: 'Ubicación de Cliente'
            ];
        }
    }

    echo json_encode([
        'status'           => 'success',
        'traccar_status'   => $traccar_status,
        'moviles_activos'  => $moviles_pintados,
        'total'            => count($elementos),
        'data'             => $elementos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}