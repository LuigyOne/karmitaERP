<?php
/**********************************************************************
    Módulo de Auditoría y Verificación SRI - NotrinosERP
***********************************************************************/
$path_to_root = '../..';
$page_security = 'SA_SALESINVOICE';

include_once($path_to_root . '/includes/db_pager.inc');
include_once($path_to_root . '/includes/session.inc');
include_once($path_to_root . '/includes/ui.inc');
include_once($path_to_root . '/sales/includes/sales_db.inc');
include_once($path_to_root . '/reporting/includes/reporting.inc');

page(_($help_context = 'Auditoría y Control de Facturación Electrónica SRI'));

// Procesar reintento manual si se solicita
if (isset($_GET['retry_invoice']) && is_numeric($_GET['retry_invoice'])) {
    $invoice_no = $_GET['retry_invoice'];
    display_notification(sprintf(_('Se solicitó reintento para la factura # %d. Verifique el log de la API.'), $invoice_no));
    meta_forward($_SERVER['PHP_SELF']);
}

// Filtros de búsqueda
start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

// Filtro de Estado SRI
$estados = array(
    '-1' => _('Todos los estados'),
    '0'  => _('Pendientes / Errores de Red'),
    '1'  => _('Autorizados'),
    '2'  => _('Rechazados / Devueltos')
);
label_cell(_('Estado SRI:'), "class='label'");
echo '<td>';
$selected_estado = isset($_POST['sri_estado_filter']) ? $_POST['sri_estado_filter'] : -1;
echo array_selector('sri_estado_filter', $selected_estado, $estados);
echo '</td>';

// Rango de fechas utilizando los nombres estándar de Notrinos
date_cells(_('Desde:'), 'TransAfterDate', '', null, -30);
date_cells(_('Hasta:'), 'TransToDate', '', null, 0);

submit_cells('Search', _('Buscar'), '', _('Actualizar lista'), 'default');
end_row();
end_table();

// Activar la recarga mediante AJAX al presionar Buscar o cambiar filtros
if (get_post('Search') || list_updated('sri_estado_filter')) {
    $Ajax->activate('_page_body');
}

// Obtener parámetros de filtrado con los nombres estándar
$estado_filtro = get_post('sri_estado_filter', -1);
$date_from = date2sql(get_post('TransAfterDate', Today()));
$date_to = date2sql(get_post('TransToDate', Today()));

// Funciones auxiliares de formato para el paginador
function fmt_trans_no($row) {
    return '<strong>#' . $row['trans_no'] . '</strong>';
}

function fmt_estado_sri($row) {
    $estado_val = $row['sri_estado'];
    if ($estado_val == 1) {
        return '<span style="color:green; font-weight:bold;">AUTORIZADO</span>';
    } elseif ($estado_val == 2) {
        return '<span style="color:red; font-weight:bold;">RECHAZADO</span>';
    } else {
        return '<span style="color:orange; font-weight:bold;">PENDIENTE</span>';
    }
}

function fmt_sri_secuencial($row) {
    if (!empty($row['sri_secuencial'])) {
        // Estilo con fuente monoespaciada, Negrita y color Azul Cian Claro (#00acc1)
        return '<span style="font-family: monospace; font-weight: bold; color: #00acc1;">' . htmlspecialchars($row['sri_secuencial']) . '</span>';
    }
    return '<span style="color: #999;">-</span>';
}

function fmt_detalle_sri($row) {
    if ($row['sri_estado'] == 1) {
        return '<small>Clave: ' . htmlspecialchars($row['sri_clave_acceso']) . '<br>Autorizado: ' . $row['sri_fecha_autorizacion'] . '</small>';
    } else {
        return '<small style="color:red;">' . htmlspecialchars($row['sri_mensaje']) . '</small>';
    }
}

function sri_actions($row) {
    $trans_no = $row['trans_no'];
    $type = ST_SALESINVOICE; 
    
    // 1. Icono de Impresión Nativo (RIDE / PDF)
    $print_link = print_document_link($trans_no . '-' . $type, _('Ver RIDE'), $type, ICON_PRINT, false);
    
    // 2. Icono SVG de XML optimizado y limpio
    $xml_icon = '';
    if ($row['sri_estado'] == 1 && !empty($row['sri_clave_acceso'])) {
        $xml_url = 'download_xml.php?clave=' . trim($row['sri_clave_acceso']);
        $svg_path = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="9 15 11 17 15 13"></polyline></svg>';
        $xml_icon = '<a href="' . $xml_url . '" target="_blank" title="' . _('Descargar XML autorizado') . '" style="margin-left: 6px; text-decoration: none;">' . $svg_path . '</a>';
    }
    
    // 3. Botón de reintento si no está autorizado
    $retry_link = '';
    if ($row['sri_estado'] != 1) {
        $retry_link = '<a href="' . $_SERVER['PHP_SELF'] . '?retry_invoice=' . $trans_no . '" title="' . _('Reintentar emisión') . '" style="color:orange; font-weight:bold; margin-left: 6px; text-decoration: none;">[↺]</a>';
    }
    
    // Contenedor unificado para forzar la alineación horizontal perfecta
    return '<div style="display: inline-flex; align-items: center; white-space: nowrap;">' . $print_link . $xml_icon . $retry_link . '</div>';
}

// Construcción de la consulta SQL (añadido trans.sri_secuencial)
$sql = "SELECT trans.trans_no, trans.tran_date, (trans.ov_amount + trans.ov_gst) AS TotalAmount, 
               debtor.name AS debtor_name, trans.sri_estado, trans.sri_clave_acceso, 
               trans.sri_fecha_autorizacion, trans.sri_mensaje, trans.sri_secuencial
        FROM " . TB_PREF . "debtor_trans trans
        LEFT JOIN " . TB_PREF . "debtors_master debtor ON trans.debtor_no = debtor.debtor_no
        WHERE trans.type = " . ST_SALESINVOICE . " 
          AND trans.tran_date >= '$date_from' 
          AND trans.tran_date <= '$date_to'";

if ($estado_filtro != -1) {
    $sql .= " AND trans.sri_estado = " . db_escape($estado_filtro);
}

$sql .= " ORDER BY trans.trans_no DESC";

// Configuración de columnas para el db_pager nativo
$cols = array(
    _('Factura #') => array('fun' => 'fmt_trans_no', 'align' => 'center'),
    _('Fecha') => array('name' => 'tran_date', 'type' => 'date', 'ord' => 'desc'),
    _('Cliente') => array('name' => 'debtor_name', 'ord' => ''),
    _('Total ($)') => array('align' => 'right', 'type' => 'amount'),
    _('Estado SRI') => array('fun' => 'fmt_estado_sri', 'align' => 'center'),
    _('SRI Ref') => array('fun' => 'fmt_sri_secuencial', 'align' => 'center'), // <-- Columna nueva insertada aquí
    _('Clave de Acceso / Mensaje') => array('fun' => 'fmt_detalle_sri'),
    _('Acciones') => array('insert' => true, 'fun' => 'sri_actions', 'align' => 'center')
);

$table =& new_db_pager('sri_trans_tbl', $sql, $cols);
$table->width = '95%'; // Ampliado ligeramente para acomodar la nueva columna

display_db_pager($table);

end_form();
end_page();
?>