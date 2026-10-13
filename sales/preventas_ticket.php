<?php
/**********************************************************************
    Ventana Independiente - Ticket / Comprobante de Modificación PWA
***********************************************************************/
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

// Capturar de forma segura mediante $_GET estándar
$id_orden = 0;
if (isset($_GET['id_orden']) && is_numeric($_GET['id_orden'])) {
    $id_orden = (int)$_GET['id_orden'];
}

if ($id_orden <= 0) {
    display_error(_("ID de orden no válido para el ticket."));
    end_page();
    exit;
}

page(_("Comprobante de Modificación"), true);

$sql = "SELECT p.*, c.name AS cliente_nombre 
        FROM pwa_preventas_cabecera p 
        LEFT JOIN " . TB_PREF . "debtors_master c ON p.cod_clien = c.debtor_no 
        WHERE p.id_orden = " . db_escape($id_orden);
$res = db_query($sql, "No se pudo obtener la preventa");
$cabecera = db_fetch($res);

if ($cabecera) {
    display_notification(_("La preventa ha sido actualizada exitosamente y se registró la auditoría del cambio."));

    echo "<div style='max-width: 500px; margin: 10px auto; background: #fff; padding: 20px; border: 1px dashed #444; font-family: monospace; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>";
    echo "<div style='text-align: center;'>";
    echo "<strong>SV DISTRIBUCIONES</strong><br>";
    echo "<small>COMPROBANTE DE MODIFICACIÓN PWA</small><br>";
    echo "--------------------------------------------------<br>";
    echo "</div>";
    
    echo "<strong>Orden:</strong> " . $cabecera['num_orden'] . "<br>";
    echo "<strong>Cliente:</strong> " . $cabecera['cod_clien'] . ' - ' . $cabecera['cliente_nombre'] . "<br>";
    echo "<strong>Fecha Modificación:</strong> " . date('d/m/Y H:i:s') . "<br>";
    echo "<strong>Estado:</strong> ACTUALIZADO Y AUDITADO<br>";
    echo "--------------------------------------------------<br>";
    
    echo "<table style='width:100%; font-size: 12px; font-family: monospace;'>";
    echo "<tr><th>Ítem / Desc</th><th align='right'>Cant</th><th align='right'>P.Unit</th><th align='right'>Total</th></tr>";

    $sql_det = "SELECT d.*, ic.description AS desc_oficial 
                FROM pwa_preventas_detalle d 
                LEFT JOIN " . TB_PREF . "item_codes ic ON d.cod_item = ic.item_code 
                WHERE d.id_orden = " . db_escape($id_orden);
    $res_det = db_query($sql_det, "No se pudo obtener el detalle");
    
    $gran_total = 0;
    while ($det = db_fetch($res_det)) {
        $total_linea = $det['cantidad'] * $det['precio_por_menor'] * (1 - ($det['descuento_porcentaje'] / 100));
        $gran_total += $total_linea;
        $nombre_producto = !empty($det['desc_oficial']) ? $det['desc_oficial'] : $det['descripcion_item'];
        
        echo "<tr>";
        echo "<td colspan='4'><strong>" . $det['cod_item'] . "</strong> - " . $nombre_producto . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td></td>";
        echo "<td align='right'>" . number_format($det['cantidad'], 2) . "</td>";
        echo "<td align='right'>" . number_format($det['precio_por_menor'], 4) . "</td>";
        echo "<td align='right'>" . number_format($total_linea, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "--------------------------------------------------<br>";
    echo "<div style='text-align: right; font-size: 14px;'><strong>TOTAL GENERAL: $" . number_format($gran_total, 2) . "</strong></div>";
    echo "--------------------------------------------------<br>";
    echo "<div style='text-align: center;'><small>¡Cambios registrados y sincronizados con éxito!</small></div>";
    echo "</div>";

    echo "<br><center>";
    echo "<a href='javascript:window.close();' class='button' style='font-size: 14px; padding: 6px 20px;'>" . _("Cerrar Ventana") . "</a>";
    echo "</center>";
} else {
    display_error(_("La orden especificada no existe."));
    echo "<center><a href='javascript:window.close();' class='button'>" . _("Cerrar Ventana") . "</a></center>";
}

echo "<style> .footer-button, a[href*='javascript:history'] { display: none !important; } </style>";
end_page();
?>