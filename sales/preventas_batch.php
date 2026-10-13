<?php
/**********************************************************************
    Centro de Control de Preventas PWA - Facturación por Lotes
    Gestión principal, vista previa e integración con item_codes
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
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc"); // Incluido para cálculos de stock nativos

$js = '';
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

// ----------------------------------------------------------------------
// MÓDULO DE VISTA PREVIA (MODAL / PANTALLA DE DETALLE)
// ----------------------------------------------------------------------
if (isset($_GET['ViewPreventa']) && is_numeric($_GET['ViewPreventa'])) {
    $id_orden = (int)$_GET['ViewPreventa'];
    page(_("Vista Previa de Preventa Móvil"), true);

    $sql = "SELECT p.*, c.name AS cliente_nombre, c.tax_id, cb.br_name, cb.br_address, s.salesman_name 
            FROM pwa_preventas_cabecera p 
            LEFT JOIN " . TB_PREF . "debtors_master c ON p.cod_clien = c.debtor_no 
            LEFT JOIN " . TB_PREF . "cust_branch cb ON p.cod_sucur = cb.branch_code 
            LEFT JOIN " . TB_PREF . "salesman s ON p.vendedor = s.salesman_code 
            WHERE p.id_orden = " . db_escape($id_orden);
    $res = db_query($sql, "No se pudo obtener la cabecera de la preventa");
    $cabecera = db_fetch($res);

    if ($cabecera) {
        display_heading(_("Detalle de Preventa: ") . $cabecera['num_orden']);
        start_table(TABLESTYLE2, "width='80%'");
        start_row();
        label_cell("<strong>" . _("Cliente:") . "</strong>", "class='label'");
        label_cell($cabecera['cod_clien'] . ' - ' . $cabecera['cliente_nombre']);
        label_cell("<strong>" . _("RUC / Cédula:") . "</strong>", "class='label'");
        label_cell($cabecera['tax_id']);
        end_row();
        start_row();
        label_cell("<strong>" . _("Sucursal / Dirección:") . "</strong>", "class='label'");
        label_cell($cabecera['br_address']);
        label_cell("<strong>" . _("Fecha Registro:") . "</strong>", "class='label'");
        label_cell(sql2date($cabecera['fec_reg_ord']));
        end_row();
        start_row();
        label_cell("<strong>" . _("Vendedor:") . "</strong>", "class='label'");
        label_cell($cabecera['salesman_name']);
        label_cell("<strong>" . _("Forma de Pago:") . "</strong>", "class='label'");
        label_cell($cabecera['forma_pago']);
        end_row();
        start_row();
        label_cell("<strong>" . _("Bodega Origen:") . "</strong>", "class='label'");
        label_cell($cabecera['bodega_origen']);
        label_cell("<strong>" . _("Estado:") . "</strong>", "class='label'");
        $estado_txt = ($cabecera['estado'] == 1) ? _('Pendiente') : (($cabecera['estado'] == 2) ? _('Procesado') : _('Cancelado'));
        label_cell($estado_txt);
        end_row();
        end_table(1);

        display_heading(_("Ítems de la Preventa"), 2);
        start_table(TABLESTYLE, "width='80%'");
        table_header(array(_("Código Item"), _("Descripción"), _("Cantidad"), _("Precio Unitario"), _("Descuento %"), _("Total Línea")));

        $sql_det = "SELECT d.*, ic.description AS desc_oficial 
                    FROM pwa_preventas_detalle d 
                    LEFT JOIN " . TB_PREF . "item_codes ic ON d.cod_item = ic.item_code 
                    WHERE d.id_orden = " . db_escape($id_orden);
        $res_det = db_query($sql_det, "No se pudo obtener el detalle de ítems");
        
        $gran_total = 0;
        $k = 0;
        while ($det = db_fetch($res_det)) {
            alt_table_row_color($k);
            $total_linea = $det['cantidad'] * $det['precio_por_menor'] * (1 - ($det['descuento_porcentaje'] / 100));
            $gran_total += $total_linea;
            $nombre_producto = !empty($det['desc_oficial']) ? $det['desc_oficial'] : $det['descripcion_item'];

            label_cell($det['cod_item'], "align='center'");
            label_cell($nombre_producto);
            qty_cell($det['cantidad'], false, user_qty_dec());
            amount_cell($det['precio_por_menor']);
            percent_cell($det['descuento_porcentaje']);
            amount_cell($total_linea);
            end_row();
        }

        start_row();
        label_cell("<strong>" . _("Total General") . "</strong>", "colspan=5 align=right");
        amount_cell($gran_total, true);
        end_row();
        end_table(1);

        echo "<center><a href='javascript:window.close();' class='button'>" . _("Cerrar Ventana") . "</a></center>";
    } else {
        display_error(_("La preventa solicitada no existe."));
    }
    echo "<style> .footer-button, a[href*='javascript:history'] { display: none !important; } </style>";
    end_page();
    exit;
}

page(_("Procesamiento de Preventas Móviles"), false, false, '', $js);

// ----------------------------------------------------------------------
// FUNCIÓN CENTRALIZADA DE FACTURACIÓN (USADA PARA INDIVIDUAL Y LOTE)
// ----------------------------------------------------------------------
function procesar_preventa_a_factura($id_orden) {
    $id_orden = (int)$id_orden;
    $sql = "SELECT * FROM pwa_preventas_cabecera WHERE id_orden = " . db_escape($id_orden) . " AND estado = 1";
    $result = db_query($sql, "No se pudo obtener la preventa");
    $cabecera = db_fetch($result);
    
    if (!$cabecera) return false;
    
    $cart = new Cart(ST_SALESINVOICE, 0);
    $cart->document_date = Today();
    
    $error = get_customer_details_to_order($cart, $cabecera['cod_clien'], $cabecera['cod_sucur']);
    if ($error != '') {
        return false;
    }
    
    if (!empty($cabecera['bodega_origen'])) {
        $cart->set_location($cabecera['bodega_origen'], '');
    }
    
    $sql_det = "SELECT * FROM pwa_preventas_detalle WHERE id_orden = " . db_escape($id_orden);
    $res_det = db_query($sql_det, "No se pudo obtener el detalle");
    
    while ($detalle = db_fetch($res_det)) {
        add_to_order(
            $cart, 
            $detalle['cod_item'], 
            $detalle['cantidad'], 
            $detalle['precio_por_menor'], 
            $detalle['descuento_porcentaje']
        );
    }
    
    if (count($cart->line_items) > 0) {
        $factura_no = $cart->write(1); 
        if ($factura_no) {
            $sql_update = "UPDATE pwa_preventas_cabecera SET estado = 2, trans_no_erp = " . db_escape($factura_no) . " WHERE id_orden = " . db_escape($id_orden);
            db_query($sql_update, "No se pudo actualizar el estado");
            return $factura_no;
        }
    }
    return false;
}

// ----------------------------------------------------------------------
// 1. ACCIÓN: FACTURA INDIVIDUAL (DESDE ICONO)
// ----------------------------------------------------------------------
if (isset($_GET['InvoicePreventa']) && is_numeric($_GET['InvoicePreventa'])) {
    $factura_no = procesar_preventa_a_factura($_GET['InvoicePreventa']);
    if ($factura_no) {
        meta_forward($path_to_root . "/sales/sales_order_entry.php", "AddedDI=" . $factura_no);
    } else {
        display_error(_("No se pudo procesar la factura individual. Verifique el stock o los datos del cliente."));
    }
}

// ----------------------------------------------------------------------
// 2. ACCIÓN: VALIDAR LOTE SELECCIONADO (INFORMATIVO OK / NO)
// ----------------------------------------------------------------------
if (isset($_POST['ValidateBatch'])) {
    if (isset($_POST['procesar']) && is_array($_POST['procesar'])) {
        $selected_ids = array();
        foreach ($_POST['procesar'] as $id_orden => $value) {
            if ($value == 1) {
                $selected_ids[] = (int)$id_orden;
            }
        }
        
        if (count($selected_ids) > 0) {
            $ids_str = implode(',', $selected_ids);
            
            // Validar que pertenezcan a la misma bodega
            $res_bodegas = db_query("SELECT DISTINCT bodega_origen FROM pwa_preventas_cabecera WHERE id_orden IN ($ids_str)", "Error al verificar bodegas");
            $bodegas = array();
            while ($b = db_fetch($res_bodegas)) {
                $bodegas[] = $b['bodega_origen'];
            }
            
            if (count($bodegas) > 1) {
                display_error(_("No: Las preventas seleccionadas pertenecen a bodegas distintas. Todas las órdenes del lote deben compartir la misma bodega de origen."));
            } else {
                $bodega_comun = $bodegas[0];
                
                // Sumar totales por ítem y consultar stock usando la función nativa get_qoh_on_date()
                $sql_sum = "SELECT d.cod_item, SUM(d.cantidad) as total_qty, ic.description as desc_oficial 
                            FROM pwa_preventas_detalle d 
                            LEFT JOIN " . TB_PREF . "item_codes ic ON d.cod_item = ic.item_code 
                            WHERE d.id_orden IN ($ids_str) 
                            GROUP BY d.cod_item";
                $res_sum = db_query($sql_sum, "Error al sumar ítems del lote");
                
                $faltantes = array();
                while ($item = db_fetch($res_sum)) {
                    $cod_item = $item['cod_item'];
                    $req_qty = floatval($item['total_qty']);
                    $nombre_prod = !empty($item['desc_oficial']) ? $item['desc_oficial'] : $cod_item;
                    
                    // Cálculo de stock oficial del ERP por ubicación
                    $stock_disponible = get_qoh_on_date($cod_item, $bodega_comun);
                    
                    if ($req_qty > $stock_disponible) {
                        $faltantes[] = "<strong>{$cod_item}</strong> ({$nombre_prod}): Requerido = {$req_qty}, Stock Disp. [{$bodega_comun}] = {$stock_disponible}";
                    }
                }
                
                if (count($faltantes) > 0) {
                    $msg = _("No: Stock insuficiente en la bodega <strong>{$bodega_comun}</strong> para los siguientes ítems:") . "<br><ul style='margin-top:5px;'>";
                    foreach ($faltantes as $f) {
                        $msg .= "<li>{$f}</li>";
                    }
                    $msg .= "</ul>";
                    display_error($msg);
                } else {
                    display_notification(_("OK: El lote seleccionado cumple con los requisitos. Todas las órdenes pertenecen a la bodega [{$bodega_comun}] y cuentan con stock suficiente. Ya puede proceder a facturar si lo desea."));
                }
            }
        } else {
            display_warning(_("No se seleccionó ninguna orden para validar."));
        }
    } else {
        display_warning(_("No se seleccionó ninguna orden para validar."));
    }
    $Ajax->activate('_page_body');
}

// ----------------------------------------------------------------------
// 3. ACCIONES: PROCESAMIENTO POR LOTES Y CANCELACIÓN
// ----------------------------------------------------------------------
if (isset($_POST['ProcessBatch'])) {
    $facturas_generadas = 0;
    if (isset($_POST['procesar']) && is_array($_POST['procesar'])) {
        foreach ($_POST['procesar'] as $id_orden => $value) {
            if ($value == 1) {
                if (procesar_preventa_a_factura($id_orden)) {
                    $facturas_generadas++;
                }
            }
        }
    }
    if ($facturas_generadas > 0) {
        display_notification(sprintf(_('Se han generado %d facturas directas exitosamente.'), $facturas_generadas));
    } else {
        display_warning(_("No se seleccionó ninguna orden válida para procesar en lote o hubo un error."));
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
// 4. FORMULARIO MAESTRO Y FILTROS
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
// 5. FUNCIONES FORMATEADORAS PARA DB_PAGER
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

function fmt_fecha($row) {
    return sql2date($row['fec_reg_ord']);
}

function preventa_actions($row) {
    $view_link = viewer_link(_("Ver Detalle"), "/sales/preventas_batch.php?ViewPreventa=" . $row['id_orden'], "", "", ICON_VIEW);
    
    $output = '<div style="display:inline-flex; align-items:center; gap:6px;">';
    $output .= $view_link;

    if ($row['estado'] == 1) {
        $edit_link = viewer_link(_("Editar Pedido"), "/sales/preventas_editar.php?EditPreventa=" . $row['id_orden'], "", "", ICON_EDIT);
        $link = pager_link(_('Facturar'), "/sales/preventas_batch.php?InvoicePreventa=" . $row['id_orden'], ICON_DOC);
        $chk = "<input type='checkbox' name='procesar[" . $row['id_orden'] . "]' value='1'>";
        $output .= '&nbsp;&nbsp;' . $edit_link . '&nbsp;&nbsp;' . $link . '&nbsp;&nbsp;' . $chk;
    } else {
        $output .= '&nbsp;&nbsp;<span style="color:#999;">' . _('Procesado') . '</span>';
    }
    
    $output .= '</div>';
    return $output;
}

// ----------------------------------------------------------------------
// 6. TABLA DB_PAGER CON ORDENAMIENTO FIJO Y FORMATO DE FECHA
// ----------------------------------------------------------------------
$date_from = date2sql($_POST['TransAfterDate']);
$date_to = date2sql($_POST['TransToDate']);

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

// Ordenamiento fijo: Del más reciente al más antiguo
$sql .= " ORDER BY p.fec_reg_ord DESC, p.id_orden DESC";

$cols = array(
    _("Orden PWA")     => array('name' => 'p.id_orden', 'align' => 'center'),
    _("Fecha")         => array('insert' => true, 'fun' => 'fmt_fecha', 'align' => 'center'),
    _("Cliente")       => array('insert' => true, 'fun' => 'fmt_cliente'),
    _("Bodega")        => array('name' => 'p.bodega_origen', 'align' => 'center'),
    _("Vendedor")      => array('insert' => true, 'fun' => 'fmt_vendedor'),
    _("Ruta")          => array('name' => 'ruta_nombre'),
    _("Forma Pago")    => array('name' => 'p.forma_pago', 'align' => 'center'),
    _("No. ERP")       => array('name' => 'p.trans_no_erp', 'insert' => true, 'fun' => 'fmt_erp', 'align' => 'center'),
    _("Doc Elec")      => array('name' => 'p.doc_elec', 'align' => 'center'),
    array('insert' => true, 'fun' => 'preventa_actions', 'align' => 'center')
);

$table =& new_db_pager('preventas_tbl', $sql, $cols);
$table->width = '95%';

display_db_pager($table);

br();
start_table(TABLESTYLE2);
start_row();
submit_cells('ValidateBatch', _("Validar Lote"), "colspan=1", _('Comprueba bodega y stock disponible del lote marcado'), false);
submit_cells('ProcessBatch', _("Facturar Seleccionados"), "colspan=1", _('Genera Factura Directa en Lote'), true);
submit_cells('CancelOrders', _("Cancelar Seleccionados"), "colspan=1", _('Rechaza órdenes marcadas'), true);
end_row();
end_table();

end_form();
end_page();
?>