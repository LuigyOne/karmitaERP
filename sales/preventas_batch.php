<?php
/**********************************************************************
    Centro de Control de Preventas PWA - Facturación por Lotes
    Mapeo estricto de columnas y ordenamiento descendente nativo.
***********************************************************************/
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc"); 

$js = '';
if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}
page(_("Procesamiento de Preventas Móviles"), false, false, '', $js);

// ----------------------------------------------------------------------
// 1. ACCIÓN: FACTURA INDIVIDUAL (DESDE ICONO)
// ----------------------------------------------------------------------
if (isset($_GET['InvoicePreventa']) && is_numeric($_GET['InvoicePreventa'])) {
    $id_orden = (int)$_GET['InvoicePreventa'];
    
    $sql = "SELECT * FROM pwa_preventas_cabecera WHERE id_orden = " . db_escape($id_orden) . " AND estado = 1";
    $result = db_query($sql, "No se pudo obtener la preventa");
    $cabecera = db_fetch($result);
    
    if ($cabecera) {
        $cart = new Cart(ST_SALESINVOICE, 0);
        $cart->customer_id = $cabecera['cod_clien'];
        $cart->Branch = $cabecera['cod_sucur'];
        $cart->Location = $cabecera['bodega_origen'];
        $cart->document_date = Today();
        $cart->sales_type = get_customer_sales_type($cabecera['cod_clien']);
        $cart->payment = get_customer_payment_terms($cabecera['cod_clien']);
        
        $sql_det = "SELECT * FROM pwa_preventas_detalle WHERE id_orden = " . db_escape($id_orden);
        $res_det = db_query($sql_det, "No se pudo obtener el detalle");
        
        while ($detalle = db_fetch($res_det)) {
            add_to_order($cart, $detalle['cod_item'], $detalle['cantidad'], $detalle['precio_por_menor'], $detalle['descuento_porcentaje']);
        }
        
        if (count($cart->line_items) > 0) {
            $factura_no = $cart->write(1); 
            $sql_update = "UPDATE pwa_preventas_cabecera SET estado = 2, trans_no_erp = " . db_escape($factura_no) . " WHERE id_orden = " . db_escape($id_orden);
            db_query($sql_update, "No se pudo actualizar el estado");
            
            meta_forward($path_to_root . "/sales/sales_order_entry.php", "AddedDI=" . $factura_no);
        }
    }
}

// ----------------------------------------------------------------------
// 2. ACCIONES: PROCESAMIENTO POR LOTES Y CANCELACIÓN
// ----------------------------------------------------------------------
if (isset($_POST['ProcessBatch'])) {
    $facturas_generadas = 0;
    if (isset($_POST['procesar']) && is_array($_POST['procesar'])) {
        foreach ($_POST['procesar'] as $id_orden => $value) {
            if ($value == 1) {
                $sql = "SELECT * FROM pwa_preventas_cabecera WHERE id_orden = " . db_escape($id_orden) . " AND estado = 1";
                $result = db_query($sql, "No se pudo obtener la cabecera");
                $cabecera = db_fetch($result);
                
                if ($cabecera) {
                    $cart = new Cart(ST_SALESINVOICE, 0);
                    $cart->customer_id = $cabecera['cod_clien'];
                    $cart->Branch = $cabecera['cod_sucur'];
                    $cart->Location = $cabecera['bodega_origen'];
                    $cart->document_date = Today();
                    $cart->sales_type = get_customer_sales_type($cabecera['cod_clien']);
                    $cart->payment = get_customer_payment_terms($cabecera['cod_clien']);
                    
                    $sql_det = "SELECT * FROM pwa_preventas_detalle WHERE id_orden = " . db_escape($id_orden);
                    $res_det = db_query($sql_det, "No se pudo obtener el detalle");
                    
                    while ($detalle = db_fetch($res_det)) {
                        add_to_order($cart, $detalle['cod_item'], $detalle['cantidad'], $detalle['precio_por_menor'], $detalle['descuento_porcentaje']);
                    }
                    
                    if (count($cart->line_items) > 0) {
                        $factura_no = $cart->write(1); 
                        $sql_update = "UPDATE pwa_preventas_cabecera SET estado = 2, trans_no_erp = " . db_escape($factura_no) . " WHERE id_orden = " . db_escape($id_orden);
                        db_query($sql_update, "No se pudo actualizar el estado");
                        $facturas_generadas++;
                    }
                }
            }
        }
    }
    if ($facturas_generadas > 0) {
        display_notification(sprintf(_('Se han generado %d facturas directas exitosamente.'), $facturas_generadas));
    } else {
        display_warning(_("No se seleccionó ninguna orden válida para procesar en lote."));
    }
    $Ajax->activate('_page_body');
}

if (isset($_POST['CancelOrders'])) {
    $canceladas = 0;
    if (isset($_POST['procesar']) && is_array($_POST['procesar'])) {
        foreach ($_POST['procesar'] as $id_orden => $value) {
            if ($value == 1) {
                db_query("UPDATE pwa_preventas_cabecera SET estado = 0 WHERE id_orden = " . db_escape($id_orden), "Error cancelando");
                $canceladas++;
            }
        }
        display_notification(sprintf(_('%d órdenes seleccionadas han sido canceladas.'), $canceladas));
    }
    $Ajax->activate('_page_body');
}

// ----------------------------------------------------------------------
// 3. FORMULARIO MAESTRO Y FILTROS
// ----------------------------------------------------------------------
start_form();

if (!isset($_POST['TransAfterDate'])) $_POST['TransAfterDate'] = begin_month(Today());
if (!isset($_POST['TransToDate'])) $_POST['TransToDate'] = end_month(Today());

$vendedor_id = get_post('vendedor_id', '-1');
$area_id = get_post('area_id', '-1');
$bodega_id = get_post('bodega_id', ALL_TEXT);
$estado_filtro = get_post('estado_filtro', '-1');

start_table(TABLESTYLE_NOBORDER);
start_row();

date_cells(_("Desde:"), 'TransAfterDate', '', null, 0, 0, 0);
date_cells(_("Hasta:"), 'TransToDate', '', null, 0, 0, 0);

$vendedores = array('-1' => _('Todos'));
$res_v = db_query("SELECT salesman_code, salesman_name FROM " . TB_PREF . "salesman");
while ($v = db_fetch($res_v)) { $vendedores[$v['salesman_code']] = $v['salesman_name']; }

$rutas = array('-1' => _('Todas'));
$res_r = db_query("SELECT area_code, description FROM " . TB_PREF . "areas ORDER BY description");
while ($r = db_fetch($res_r)) { $rutas[$r['area_code']] = $r['description']; }

echo "<td>" . _("Vendedor:") . "</td><td>" . array_selector('vendedor_id', $vendedor_id, $vendedores, array('select_submit'=> false)) . "</td>";
echo "<td>" . _("Ruta/Zona:") . "</td><td>" . array_selector('area_id', $area_id, $rutas, array('select_submit'=> false)) . "</td>";

locations_list_cells(_('Bodega:'), 'bodega_id', null, true, true);

echo "<td>" . _("Estado:") . "</td><td>" . array_selector('estado_filtro', $estado_filtro, array('-1' => _('Todos'), '1' => _('Pendientes'), '2' => _('Procesados'), '0' => _('Cancelados')), array('select_submit'=> false)) . "</td>";

submit_cells('Search', _("Aplicar Filtro"), '', _('Filtrar pedidos'), 'default');
end_row();
end_table();
br();

if (get_post('Search')) {
    $Ajax->activate('_page_body');
}

// ----------------------------------------------------------------------
// 4. FUNCIONES FORMATEADORAS PARA DB_PAGER
// ----------------------------------------------------------------------
function fmt_cliente($row) {
    return $row['cod_clien'] . ' - ' . $row['cliente_nombre'];
}

function fmt_vendedor($row) {
    return !empty($row['salesman_name']) ? $row['salesman_name'] : $row['vendedor'];
}

function fmt_erp($row) {
    return $row['trans_no_erp'] > 0 ? "Fact. #" . $row['trans_no_erp'] : '-';
}

function preventa_actions($row) {
    if ($row['estado'] == 1) {
        $link = pager_link(_('Facturar'), "/sales/preventas_batch.php?InvoicePreventa=" . $row['id_orden'], ICON_DOC);
        $chk = "<input type='checkbox' name='procesar[" . $row['id_orden'] . "]' value='1'>";
        return '<div style="display:inline-flex; align-items:center;">' . $link . '&nbsp;&nbsp;' . $chk . '</div>';
    }
    return '<span style="color:#999;">' . _('Procesado') . '</span>';
}

// ----------------------------------------------------------------------
// 5. TABLA DB_PAGER CON MAPEO ESTRICTO Y ORDEN DESCENDENTE
// ----------------------------------------------------------------------
$date_from = date2sql($_POST['TransAfterDate']);
$date_to = date2sql($_POST['TransToDate']);

// CRÍTICO: Los primeros 6 campos del SELECT deben emparejar 1:1 
// con las primeras 6 columnas NO-VIRTUALES del array $cols.
$sql = "SELECT 
            p.num_orden, 
            p.fec_reg_ord, 
            p.bodega_origen, 
            a.description AS ruta_nombre, 
            p.forma_pago, 
            p.doc_elec, 
            p.id_orden, 
            p.cod_clien, 
            c.name AS cliente_nombre, 
            p.vendedor, 
            s.salesman_name, 
            p.trans_no_erp, 
            p.estado 
        FROM pwa_preventas_cabecera p 
        LEFT JOIN " . TB_PREF . "debtors_master c ON p.cod_clien = c.debtor_no 
        LEFT JOIN " . TB_PREF . "cust_branch cb ON p.cod_sucur = cb.branch_code 
        LEFT JOIN " . TB_PREF . "areas a ON cb.area = a.area_code 
        LEFT JOIN " . TB_PREF . "salesman s ON p.vendedor = s.salesman_code 
        WHERE DATE(p.fec_reg_ord) >= '$date_from' 
        AND DATE(p.fec_reg_ord) <= '$date_to'";

if ($vendedor_id != '-1') $sql .= " AND p.vendedor = " . db_escape($vendedor_id);
if ($area_id != '-1') $sql .= " AND cb.area = " . db_escape($area_id);
if ($bodega_id != '' && $bodega_id != ALL_TEXT) $sql .= " AND p.bodega_origen = " . db_escape($bodega_id);
if ($estado_filtro != '-1') $sql .= " AND p.estado = " . db_escape($estado_filtro);

// ELIMINAMOS el ORDER BY manual. db_pager se encarga de inyectarlo mediante el atributo 'ord' => 'desc'.

$cols = array(
    // Agregamos 'ord' => 'desc' a la primera columna. 'name' indicará por qué campo SQL se debe ordenar.
    _("Orden PWA")     => array('name' => 'p.id_orden', 'align' => 'center', 'ord' => 'desc'),
    _("Fecha")         => array('name' => 'p.fec_reg_ord', 'type' => 'date', 'align' => 'center'),
    _("Cliente")       => array('insert' => true, 'fun' => 'fmt_cliente'),
    _("Bodega")        => array('name' => 'p.bodega_origen', 'align' => 'center'),
    _("Vendedor")      => array('insert' => true, 'fun' => 'fmt_vendedor'),
    _("Ruta")          => array('name' => 'ruta_nombre'),
    _("Forma Pago")    => array('name' => 'p.forma_pago', 'align' => 'center'),
    _("No. ERP")       => array('insert' => true, 'fun' => 'fmt_erp', 'align' => 'center'),
    _("Doc Elec")      => array('name' => 'p.doc_elec', 'align' => 'center'),
    array('insert' => true, 'fun' => 'preventa_actions', 'align' => 'center')
);

$table =& new_db_pager('preventas_tbl', $sql, $cols);
$table->width = '95%';

display_db_pager($table);

br();
start_table(TABLESTYLE2);
start_row();
submit_cells('ProcessBatch', _("Facturar Seleccionados"), "colspan=2", _('Genera Factura Directa en Lote'), true);
submit_cells('CancelOrders', _("Cancelar Seleccionados"), "colspan=2", _('Rechaza órdenes marcadas'), true);
end_row();
end_table();

end_form();
end_page();
?>