<?php
// pos/backend/catalogo_data.php
require_once 'conexion.php';
require_once 'imagen_helper.php';

function obtenerDatosCatologo() {
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    $categoria_filtro = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    $catalogo_unificado = [];
    $categorias = [];

    try {
        $db = BaseDatos::obtenerConexion();
        
        // Obtener categorías
        $stmt_cat = $db->query("SELECT category_id, description FROM stock_category WHERE inactive = 0 ORDER BY description ASC");
        $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
        
        // 1. Obtener Productos Regulares con precio, IVA y stock
        $sql_art = "SELECT sm.stock_id, sm.description, sm.long_description, sm.mb_flag, sm.category_id,
                           COALESCE(p.price, 0) as precio_base, 
                           COALESCE(t.rate, 15.00) as tasa_iva, 'regular' as tipo_item
                    FROM stock_master sm
                    LEFT JOIN prices p ON sm.stock_id = p.stock_id AND p.sales_type_id = 1
                    LEFT JOIN item_tax_types itt ON sm.tax_type_id = itt.id
                    LEFT JOIN tax_type_weights ttw ON itt.id = ttw.item_tax_type_id
                    LEFT JOIN tax_types t ON ttw.tax_type_id = t.id
                    WHERE sm.inactive = 0 AND sm.mb_flag != 'F'";
        
        $params = [];
        if ($busqueda !== '') {
            $sql_art .= " AND (sm.description LIKE :busq OR sm.long_description LIKE :busq OR sm.stock_id LIKE :busq)";
            $params['busq'] = "%$busqueda%";
        }
        if ($categoria_filtro > 0) {
            $sql_art .= " AND sm.category_id = :cat_id";
            $params['cat_id'] = $categoria_filtro;
        }
        
        $stmt_art = $db->prepare($sql_art);
        $stmt_art->execute($params);
        $articulos = $stmt_art->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($articulos as &$art) {
            $precio_neto = floatval($art['precio_base']);
            $tasa = floatval($art['tasa_iva']);
            $art['precio_venta'] = round($precio_neto * (1 + ($tasa / 100)), 2);
            $art['stock'] = obtenerStockItem($db, $art['stock_id']);
            $art['foto_url'] = obtenerImagenStockUnificada($art['stock_id']);
        }
        unset($art);

        // 2. Obtener Kits Reales con precio totalizado e IVA
        $sql_kits = "SELECT ic.item_code as stock_id, ic.description, ic.category_id, 
                            SUM(ic.quantity * COALESCE(p.price, 0)) as precio_base, 'kit' as tipo_item
                     FROM item_codes ic
                     LEFT JOIN prices p ON ic.stock_id = p.stock_id AND p.sales_type_id = 1
                     WHERE ic.is_foreign = 0 
                     AND ic.item_code NOT IN (SELECT stock_id FROM stock_master)";
        
        $params_kits = [];
        if ($busqueda !== '') {
            $sql_kits .= " AND (ic.item_code LIKE :busq_k OR ic.description LIKE :busq_k)";
            $params_kits['busq_k'] = "%$busqueda%";
        }
        if ($categoria_filtro > 0) {
            $sql_kits .= " AND ic.category_id = :cat_id_k";
            $params_kits['cat_id_k'] = $categoria_filtro;
        }
        
        $sql_kits .= " GROUP BY ic.item_code, ic.description, ic.category_id";
        
        $stmt_kits = $db->prepare($sql_kits);
        $stmt_kits->execute($params_kits);
        $kits = $stmt_kits->fetchAll(PDO::FETCH_ASSOC);

        foreach ($kits as &$kit) {
            $precio_neto_kit = floatval($kit['precio_base']);
            $kit['precio_venta'] = round($precio_neto_kit * 1.15, 2); // 15% IVA Ecuador por defecto
            $kit['stock'] = ['matriz' => 0, 'otras' => 0];
            $kit['foto_url'] = null;
        }
        unset($kit);
        
        $catalogo_unificado = array_merge($articulos, $kits);
        usort($catalogo_unificado, function($a, $b) {
            return strcmp($a['description'], $b['description']);
        });

    } catch (Exception $e) {
        $catalogo_unificado = [];
        $categorias = [];
    }

    return ['productos' => $catalogo_unificado, 'categorias' => $categorias];
}

function obtenerStockItem($db, $stock_id) {
    try {
        $stmt = $db->prepare("SELECT loc_code, quantity FROM loc_stock WHERE stock_id = :stock_id");
        $stmt->execute(['stock_id' => $stock_id]);
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $matriz = 0;
        $otras = 0;
        foreach ($stocks as $s) {
            if ($s['loc_code'] == 'DEF' || $s['loc_code'] == '001' || $s['loc_code'] == '001-010 MATRIZ STO DGO') {
                $matriz += floatval($s['quantity']);
            } else {
                $otras += floatval($s['quantity']);
            }
        }
        return ['matriz' => $matriz, 'otras' => $otras];
    } catch (Exception $e) {
        return ['matriz' => 0, 'otras' => 0];
    }
}


?>