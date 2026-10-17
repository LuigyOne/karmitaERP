<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

$clientes_sucursales = [];
$productos_catalogo = [];

// Obtener la bodega móvil fija de la sesión actual
$bodega_movil = $_SESSION['bodega_movil'] ?? 'VAN01';

// Función auxiliar para resolver la imagen física del producto
function obtenerImagenStock($stock_id) {
    $exts = ['jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF'];
    foreach ($exts as $ext) {
        $nombre_archivo = strtolower($stock_id) . '.' . $ext;
        $ruta_fisica = __DIR__ . '/../../company/1/images/stock_items/' . $nombre_archivo;
        if (file_exists($ruta_fisica)) {
            return '../company/1/images/stock_items/' . $nombre_archivo;
        }
    }
    return null;
}

try {
    $db = BaseDatos::obtenerConexion();
    
    // -------------------------------------------------------------------------
    // 1. OBTENER CLIENTES Y SUCURSALES
    // -------------------------------------------------------------------------
    $sql_clientes = "SELECT cb.branch_code, cb.branch_ref, cb.br_name, cb.br_address, cb.foto_1,
                            cp.name as contact_name, cp.phone, cp.phone2, cp.email,
                            c.debtor_no, c.name as customer_name, c.tax_id as ruc_cedula
                     FROM " . TB_PREF . "debtors_master c
                     INNER JOIN " . TB_PREF . "cust_branch cb ON c.debtor_no = cb.debtor_no
                     LEFT JOIN " . TB_PREF . "crm_contacts cc ON cc.type = 'cust_branch' AND cc.entity_id = cb.branch_code
                     LEFT JOIN " . TB_PREF . "crm_persons cp ON cc.person_id = cp.id
                     WHERE c.inactive = 0 AND cb.inactive = 0
                     ORDER BY cb.branch_ref ASC, c.name ASC";
            
    $stmt = $db->prepare($sql_clientes);
    $stmt->execute();
    $resultadosRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultadosRaw as $row) {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        $clientes_sucursales[] = $row;
    }

    // -------------------------------------------------------------------------
    // 2. OBTENER CATÁLOGO DE PRODUCTOS FILTRADOS POR STOCK REAL EN BODEGA MÓVIL
    // -------------------------------------------------------------------------
    
    // A. Ítems Sueltos con cálculo de QOH basado en operaciones (stock_moves + voided)
    $sql_items = "SELECT sm.stock_id, sm.description, sm.mb_flag, sm.units,
                         COALESCE(SUM(CASE WHEN voided.id IS NULL THEN move.qty ELSE 0 END), 0) as stock_disponible,
                         COALESCE(p.price, 0) as precio_base,
                         COALESCE(t.rate, 0.00) as tasa_iva
                  FROM " . TB_PREF . "stock_master sm
                  INNER JOIN " . TB_PREF . "stock_moves move ON sm.stock_id = move.stock_id AND move.loc_code = :bodega
                  LEFT JOIN " . TB_PREF . "voided voided ON voided.type = move.type AND voided.id = move.trans_no
                  LEFT JOIN " . TB_PREF . "prices p ON sm.stock_id = p.stock_id AND p.sales_type_id = 1
                  LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
                  LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
                  LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
                  WHERE sm.inactive = 0
                  GROUP BY sm.stock_id, sm.description, sm.mb_flag, sm.units, p.price, t.rate
                  HAVING stock_disponible > 0
                  ORDER BY sm.description ASC";
    
    $stmt_items = $db->prepare($sql_items);
    $stmt_items->execute(['bodega' => $bodega_movil]);
    $items_raw = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items_raw as $it) {
        $precio_base = floatval($it['precio_base']);
        $tasa_iva = floatval($it['tasa_iva']);
        $precio_con_iva = round($precio_base * (1 + ($tasa_iva / 100)), 2);
        $es_kit = ($it['mb_flag'] === 'K' || $it['mb_flag'] === 'D');
        $foto = obtenerImagenStock($it['stock_id']);

        $productos_catalogo[] = [
            'id' => $it['stock_id'],
            'name' => html_entity_decode($it['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'tipo' => $es_kit ? 'kit' : 'item',
            'precio' => $precio_con_iva,
            'stock' => floatval($it['stock_disponible']),
            'foto' => $foto
        ];
    }

    // B. Kits de Venta adicionales (verificando que sus componentes tengan stock en la bodega)
    $sql_kits = "SELECT ic.item_code as kit_code, ic.description as kit_description
                 FROM " . TB_PREF . "item_codes ic
                 WHERE ic.is_foreign = 0 AND ic.inactive = 0
                 GROUP BY ic.item_code, ic.description
                 HAVING COUNT(ic.stock_id) > 1 OR ic.item_code NOT IN (SELECT stock_id FROM " . TB_PREF . "stock_master)";
    
    $stmt_kits = $db->prepare($sql_kits);
    $stmt_kits->execute();
    $kits_raw = $stmt_kits->fetchAll(PDO::FETCH_ASSOC);

    foreach ($kits_raw as $k) {
        $kit_code = $k['kit_code'];
        
        $sql_comps = "SELECT ic.quantity, ic.stock_id,
                             COALESCE(p.price, 0) as precio_base,
                             COALESCE(t.rate, 0.00) as tasa_iva,
                             (SELECT COALESCE(SUM(CASE WHEN v.id IS NULL THEN m.qty ELSE 0 END), 0) 
                              FROM " . TB_PREF . "stock_moves m 
                              LEFT JOIN " . TB_PREF . "voided v ON v.type = m.type AND v.id = m.trans_no 
                              WHERE m.stock_id = ic.stock_id AND m.loc_code = :bodega) as stock_comp
                      FROM " . TB_PREF . "item_codes ic
                      LEFT JOIN " . TB_PREF . "prices p ON ic.stock_id = p.stock_id AND p.sales_type_id = 1
                      LEFT JOIN " . TB_PREF . "stock_master sm ON ic.stock_id = sm.stock_id
                      LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
                      LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
                      LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
                      WHERE ic.item_code = :kit_code AND ic.is_foreign = 0";
        
        $stmt_comps = $db->prepare($sql_comps);
        $stmt_comps->execute(['kit_code' => $kit_code, 'bodega' => $bodega_movil]);
        $componentes = $stmt_comps->fetchAll(PDO::FETCH_ASSOC);

        $precio_total_kit = 0;
        $kit_disponible = true;
        $min_stock_kit = 999999;

        foreach ($componentes as $comp) {
            $p_base = floatval($comp['precio_base']);
            $t_iva = floatval($comp['tasa_iva']);
            $cant = floatval($comp['quantity']);
            $stock_dis_comp = floatval($comp['stock_comp']);

            $precio_total_kit += ($p_base * (1 + ($t_iva / 100)) * $cant);

            if ($cant > 0) {
                $posibles_kits = floor($stock_dis_comp / $cant);
                if ($posibles_kits < $min_stock_kit) {
                    $min_stock_kit = $posibles_kits;
                }
            } else {
                $min_stock_kit = 0;
            }
        }

        if ($min_stock_kit <= 0) {
            $kit_disponible = false;
        }

        // Evitar duplicados y verificar que tenga stock disponible
        $existe = array_filter($productos_catalogo, function($p) use ($kit_code) {
            return $p['id'] === $kit_code;
        });

        if (empty($existe) && $kit_disponible) {
            $productos_catalogo[] = [
                'id' => $kit_code,
                'name' => html_entity_decode($k['kit_description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'tipo' => 'kit',
                'precio' => round($precio_total_kit, 2),
                'stock' => $min_stock_kit,
                'foto' => null
            ];
        }
    }

} catch (Exception $e) {
    $clientes_sucursales = [];
    $productos_catalogo = [];
}
