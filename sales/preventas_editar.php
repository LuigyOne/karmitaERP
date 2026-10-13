<?php
/**********************************************************************
    Módulo de Edición de Preventas Móviles PWA (Patrón Nativo ERP)
***********************************************************************/
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

// Capturar ID de orden
$id_orden = 0;
if (isset($_GET['EditPreventa']) && is_numeric($_GET['EditPreventa'])) {
    $id_orden = (int)$_GET['EditPreventa'];
} elseif (isset($_POST['id_orden']) && is_numeric($_POST['id_orden'])) {
    $id_orden = (int)$_POST['id_orden'];
} elseif (isset($_GET['TicketID']) && is_numeric($_GET['TicketID'])) {
    $id_orden = (int)$_GET['TicketID'];
}

if ($id_orden <= 0) {
    display_error(_("ID de preventa no válido."));
    end_page();
    exit;
}

// 1. Procesar actualización al hacer clic en Guardar
if (isset($_POST['UpdatePreventaLines'])) {
    $usuario_actual = $_SESSION['wa_current_user']->name ?? 'Administrador';
    $items_modificados = $_POST['items'] ?? array();
    
    foreach ($items_modificados as $index => $data) {
        $cod_item = $data['cod_item'];
        $cant = floatval($data['qty']);
        $precio = floatval($data['price']);
        $desc = floatval($data['disc']);
        
        // Verificar si se marcó para eliminar este ítem
        if (isset($_POST['del_' . $index]) && $_POST['del_' . $index] == 1) {
            $sql_del = "DELETE FROM pwa_preventas_detalle WHERE id_orden = " . db_escape($id_orden) . " AND cod_item = " . db_escape($cod_item);
            db_query($sql_del, "No se pudo eliminar el ítem");
        } else {
            // Actualizar cantidades, precios y descuentos
            $sql_upd = "UPDATE pwa_preventas_detalle SET 
                        cantidad = " . db_escape($cant) . ", 
                        precio_por_menor = " . db_escape($precio) . ", 
                        descuento_porcentaje = " . db_escape($desc) . " 
                        WHERE id_orden = " . db_escape($id_orden) . " AND cod_item = " . db_escape($cod_item);
            db_query($sql_upd, "No se pudo actualizar el ítem de preventa");
        }
    }

    // Registrar en la tabla de auditoría (pwa_auditar)
    $uuid = function_exists('com_create_guid') ? trim(com_create_guid(), '{}') : sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    
    $sql_audit = "INSERT INTO pwa_auditar 
        (uuid, accion_codigo, accion_nombre, usuario, fecha_hora_creacion, observaciones, device_info, sync_status) 
        VALUES (" . db_escape($uuid) . ", 'MOD', 'Modificación de Preventa', " . db_escape($usuario_actual) . ", NOW(), " . db_escape("Modificación previa a facturación de la Orden ID: " . $id_orden) . ", " . db_escape($_SERVER['HTTP_USER_AGENT'] ?? 'ERP') . ", 'SINCRONIZADO')";
    db_query($sql_audit, "No se pudo registrar la auditoría de modificación");

    // Redirección limpia nativa del ERP idéntica a sales_order_entry.php
    meta_forward($_SERVER['PHP_SELF'], 'TicketID=' . $id_orden);
}

// 2. Si viene el parámetro TicketID, mostramos la pantalla de éxito con el ticket integrado y botón de cierre
if (isset($_GET['TicketID'])) {
    $id_orden = (int)$_GET['TicketID'];
    page(_("Comprobante de Modificación"), true);

    // Refrescar la ventana principal de la grilla de fondo automáticamente
    echo "<script>
            if (window.opener && !window.opener.closed) {
                window.opener.location.reload();
            }
          </script>";

    display_notification_centered(_("La preventa ha sido actualizada exitosamente y se registró la auditoría del cambio."));

    $sql = "SELECT p.*, c.name AS cliente_nombre 
            FROM pwa_preventas_cabecera p 
            LEFT JOIN " . TB_PREF . "debtors_master c ON p.cod_clien = c.debtor_no 
            WHERE p.id_orden = " . db_escape($id_orden);
    $res = db_query($sql, "No se pudo obtener la preventa");
    $cabecera = db_fetch($res);

    if ($cabecera) {
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
    }

    echo "<br><center>";
    echo "<a href='javascript:window.close();' class='button' style='font-size: 14px; padding: 6px 20px;'>" . _("Cerrar Ventana") . "</a>";
    echo "</center>";

    echo "<style> .footer-button, a[href*='javascript:history'] { display: none !important; } </style>";
    end_page();
    exit;
}

// 3. Modo Edición Normal (Carga inicial del formulario)
page(_("Modificar Preventa Móvil"), true);

$sql = "SELECT p.*, c.name AS cliente_nombre, cb.br_address, s.salesman_name 
        FROM pwa_preventas_cabecera p 
        LEFT JOIN " . TB_PREF . "debtors_master c ON p.cod_clien = c.debtor_no 
        LEFT JOIN " . TB_PREF . "cust_branch cb ON p.cod_sucur = cb.branch_code 
        LEFT JOIN " . TB_PREF . "salesman s ON p.vendedor = s.salesman_code 
        WHERE p.id_orden = " . db_escape($id_orden);
$res = db_query($sql, "No se pudo obtener la preventa");
$cabecera = db_fetch($res);

if ($cabecera && $cabecera['estado'] == 1) {
    
    start_form();
    hidden('id_orden', $id_orden);
    
    display_heading(_("Editando Preventa: ") . $cabecera['num_orden']);
    
    start_table(TABLESTYLE2, "width='80%'");
    start_row();
    label_cell("<strong>" . _("Cliente:") . "</strong>", "class='label'");
    label_cell($cabecera['cod_clien'] . ' - ' . $cabecera['cliente_nombre']);
    label_cell("<strong>" . _("Fecha:") . "</strong>", "class='label'");
    label_cell(sql2date($cabecera['fec_reg_ord']));
    end_row();
    end_table(1);

    display_heading(_("Detalle de Ítems (Modifique cantidades, precios o marque para eliminar)"), 2);
    start_table(TABLESTYLE, "width='90%'");
    table_header(array(_("Código"), _("Descripción"), _("Cantidad"), _("Precio Unit."), _("Desc %"), _("Eliminar")));

    $sql_det = "SELECT d.*, ic.description AS desc_oficial 
                FROM pwa_preventas_detalle d 
                LEFT JOIN " . TB_PREF . "item_codes ic ON d.cod_item = ic.item_code 
                WHERE d.id_orden = " . db_escape($id_orden);
    $res_det = db_query($sql_det, "No se pudo obtener el detalle");
    
    $k = 0;
    while ($det = db_fetch($res_det)) {
        alt_table_row_color($k);
        $nombre_producto = !empty($det['desc_oficial']) ? $det['desc_oficial'] : $det['descripcion_item'];
        
        label_cell($det['cod_item'], "align='center'");
        label_cell($nombre_producto);
        
        hidden("items[$k][cod_item]", $det['cod_item']);

        echo "<td align='center'>";
        echo "<input type='text' name='items[$k][qty]' value='" . $det['cantidad'] . "' size='6' style='text-align:right;'>";
        echo "</td>";
        
        echo "<td align='center'>";
        echo "<input type='text' name='items[$k][price]' value='" . $det['precio_por_menor'] . "' size='8' style='text-align:right;'>";
        echo "</td>";
        
        echo "<td align='center'>";
        echo "<input type='text' name='items[$k][disc]' value='" . $det['descuento_porcentaje'] . "' size='4' style='text-align:right;'>";
        echo "</td>";
        
        echo "<td align='center'>";
        echo "<input type='checkbox' name='del_$k' value='1'>";
        echo "</td>";
        
        end_row();
        $k++;
    }
    end_table(1);

    echo "<br><center>";
    submit('UpdatePreventaLines', _("Guardar Cambios y Auditoría"), true, _("Actualiza el pedido en el sistema"), 'default');
    echo "&nbsp;&nbsp;<a href='javascript:window.close();' class='button'>" . _("Cerrar Ventana") . "</a>";
    echo "</center>";

    end_form();

} else {
    display_error(_("La preventa no existe o ya ha sido procesada/cancelada, por lo que no puede ser modificada."));
    echo "<center><a href='javascript:window.close();' class='button'>" . _("Cerrar Ventana") . "</a></center>";
}

echo "<style> .footer-button, a[href*='javascript:history'] { display: none !important; } </style>";
end_page();
?>