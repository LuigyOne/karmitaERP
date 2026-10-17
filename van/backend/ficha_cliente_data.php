<?php
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

// Obtener el código de sucursal desde la URL
$branch_code = isset($_GET['branch_code']) ? intval($_GET['branch_code']) : 0;

$sucursal = [];
$total_compras = 0;
$total_pagos = 0;
$saldo_pendiente = 0;

try {
    $db = BaseDatos::obtenerConexion();
    
    // Consulta unificada de sucursal y datos maestros del cliente
    $sql = "SELECT cb.*, cm.name as customer_name, cm.tax_id, cm.address as customer_address 
            FROM " . TB_PREF . "cust_branch cb 
            JOIN " . TB_PREF . "debtors_master cm ON cb.debtor_no = cm.debtor_no 
            WHERE cb.branch_code = :branch_code";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['branch_code' => $branch_code]);
    $raw_sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($raw_sucursal) {
        // Limpiar entidades HTML de todo el arreglo de la sucursal/cliente
        foreach ($raw_sucursal as $key => $value) {
            if (is_string($value)) {
                $sucursal[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                $sucursal[$key] = $value;
            }
        }

        // Cálculo del balance comercial desde debtor_trans (Facturas tipo 10)
        $sql_trans = "SELECT SUM(ov_amount + ov_gst + ov_freight + ov_discount) as total_compras,
                             SUM(alloc) as total_pagos 
                      FROM " . TB_PREF . "debtor_trans 
                      WHERE debtor_no = :debtor_no AND branch_code = :branch_code AND type = 10";
        $stmt_trans = $db->prepare($sql_trans);
        $stmt_trans->execute(['debtor_no' => $sucursal['debtor_no'], 'branch_code' => $branch_code]);
        $balance = $stmt_trans->fetch(PDO::FETCH_ASSOC);
        
        $total_compras = floatval($balance['total_compras'] ?? 0);
        $total_pagos = floatval($balance['total_pagos'] ?? 0);
        $saldo_pendiente = $total_compras - $total_pagos;
    }
} catch (Exception $e) {
    $sucursal = [];
}

// Valores por defecto utilizando branch_ref como nombre comercial / fantasía
$nombre_comercial = $sucursal['branch_ref'] ?? ($sucursal['br_name'] ?? 'Sucursal No Encontrada');
$nombre_cliente = $sucursal['customer_name'] ?? 'Cliente General';
$direccion = $sucursal['br_address'] ?? 'Sin dirección registrada';
$ruc = $sucursal['tax_id'] ?? '9999999999001';
$lat = $sucursal['latitud'] ?? '';
$lon = $sucursal['longitud'] ?? '';
$dias_visita = $sucursal['dias_visita'] ?? 'No asignado';
$frecuencia = $sucursal['frecuencia_visita'] ?? 'Semanal';