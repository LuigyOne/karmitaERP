<?php
/**********************************************************************
	Copyright (C) NotrinosERP.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
***********************************************************************/
$page_security = 'SA_CRM_SETTINGS';
$path_to_root  = '../..';

include_once($path_to_root . '/includes/session.inc');
include_once($path_to_root . '/includes/ui.inc');

// Manejo del guardado de preferencias de la API
function get_geo_pref($name, $default = '') {
    $sql = "SELECT value FROM ".TB_PREF."sys_prefs WHERE name = ".db_escape($name)." AND category = 'crm_geo'";
    $result = db_query($sql, "Could not retrieve geo preference");
    if ($result && db_num_rows($result) > 0) {
        $row = db_fetch($result);
        return $row['value'];
    }
    return $default;
}

function set_geo_pref($name, $value) {
    $sql = "SELECT value FROM ".TB_PREF."sys_prefs WHERE name = ".db_escape($name)." AND category = 'crm_geo'";
    $result = db_query($sql, "Could not check geo preference");
    
    if ($result && db_num_rows($result) > 0) {
        $sql = "UPDATE ".TB_PREF."sys_prefs SET value = ".db_escape($value)." WHERE name = ".db_escape($name)." AND category = 'crm_geo'";
    } else {
        $sql = "INSERT INTO ".TB_PREF."sys_prefs (name, category, type, value) VALUES (".db_escape($name).", 'crm_geo', 'varchar', ".db_escape($value).")";
    }
    db_query($sql, "Could not update geo preference");
}

// Guardar configuración de la API
if (isset($_POST['UpdateSettings'])) {
    set_geo_pref('traccar_url', $_POST['traccar_url']);
    set_geo_pref('traccar_user', $_POST['traccar_user']);
    if (trim($_POST['traccar_pass']) != '') {
        set_geo_pref('traccar_pass', $_POST['traccar_pass']);
    }
    set_geo_pref('sync_interval', $_POST['sync_interval']);
    set_geo_pref('audit_mobile_enabled', isset($_POST['audit_mobile_enabled']) ? '1' : '0');
    display_notification(_('La configuración de geo rastreo ha sido actualizada exitosamente.'));
}

// Guardar cambios masivos de la hoja de cálculo (Grid View)
if (isset($_POST['SaveGridDevices'])) {
    if (isset($_POST['grid_rows']) && is_array($_POST['grid_rows'])) {
        foreach ($_POST['grid_rows'] as $row_id => $data) {
            $elemento_tipo = db_escape($data['elemento_tipo']);
            $salesman_code = db_escape($data['salesman_code']);
            $device_id = db_escape($data['device_id']);
            $es_movil = isset($data['es_movil']) ? 1 : 0;
            $latitud = trim($data['latitud']) !== '' ? db_escape($data['latitud']) : "NULL";
            $longitud = trim($data['longitud']) !== '' ? db_escape($data['longitud']) : "NULL";
            $descripcion = db_escape($data['descripcion']);
            $estado = db_escape($data['estado']);
            
            if ($row_id === 'new') {
                if (trim($data['device_id']) != '' || trim($data['descripcion']) != '') {
                    $sql = "INSERT INTO ".TB_PREF."pwa_dispositivos_bodegas (elemento_tipo, salesman_code, device_id, es_movil, latitud, longitud, descripcion, estado) 
                            VALUES ($elemento_tipo, $salesman_code, $device_id, $es_movil, $latitud, $longitud, $descripcion, $estado)";
                    db_query($sql, "No se pudo insertar el nuevo elemento de rastreo");
                }
            } else {
                if (isset($data['delete']) && $data['delete'] == '1') {
                    $sql = "DELETE FROM ".TB_PREF."pwa_dispositivos_bodegas WHERE id = " . db_escape($row_id);
                    db_query($sql, "No se pudo eliminar el registro");
                } else {
                    $sql = "UPDATE ".TB_PREF."pwa_dispositivos_bodegas SET 
                            elemento_tipo = $elemento_tipo,
                            salesman_code = $salesman_code, 
                            device_id = $device_id, 
                            es_movil = $es_movil, 
                            latitud = $latitud, 
                            longitud = $longitud, 
                            descripcion = $descripcion, 
                            estado = $estado 
                            WHERE id = " . db_escape($row_id);
                    db_query($sql, "No se pudo actualizar el registro");
                }
            }
        }
        display_notification(_('La cuadrícula de elementos de rastreo ha sido sincronizada correctamente.'));
    }
}

$traccar_url = get_geo_pref('traccar_url', '');
$traccar_user = get_geo_pref('traccar_user', '');
$sync_interval = get_geo_pref('sync_interval', '60');
$audit_mobile_enabled = get_geo_pref('audit_mobile_enabled', '0');

page(_($help_context = 'Configuración y Mapeo de Geo Rastreo'), false, false, '', '');

start_form();

// Script y estilos para el comportamiento Collapse y la Hoja de Calculo GridView
echo '<script>
function toggleCollapse(id) {
    var el = document.getElementById(id);
    if (el.style.display === "none") {
        el.style.display = "block";
    } else {
        el.style.display = "none";
    }
}
</script>';

echo '<style>
.card-box { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.card-header { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid #eee; padding-bottom: 10px; }
.spreadsheet-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.spreadsheet-table th, .spreadsheet-table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.spreadsheet-table th { background-color: #f5f5f5; font-weight: bold; color: #444; }
.spreadsheet-table input[type="text"], .spreadsheet-table select { width: 95%; padding: 4px; border: 1px solid #ccc; border-radius: 3px; }
.col-hidden { display: none; }
</style>';

// --- TARJETA 1: CONFIGURACIÓN API GPS (DIV COLLAPSE) ---
echo '<div class="card-box">';
echo '<div class="card-header" onclick="toggleCollapse(\'api_config_content\')">
        <span>' . _('1. Configuración de Credenciales API GPS') . '</span>
        <span style="font-size: 14px; color: #0073aa;">&#9660;</span>
      </div>';

echo '<div id="api_config_content" style="display: none;">';
start_table(TABLESTYLE2);
table_section_title(_('Credenciales del Servidor GPS'));
text_row(_('URL del Servidor API:'), 'traccar_url', $traccar_url, 40, 60);
text_row(_('Token o Usuario API:'), 'traccar_user', $traccar_user, 30, 40);
password_row(_('Contraseña API (dejar en blanco para mantener):'), 'traccar_pass', '');

table_section_title(_('Parámetros de Sincronización'));
text_row(_('Intervalo de consulta (segundos):'), 'sync_interval', $sync_interval, 10, 10);
check_row(_('Habilitar auditoría de preventas/ventas móviles:'), 'audit_mobile_enabled', $audit_mobile_enabled);
end_table(1);

echo "<br>";
submit_center('UpdateSettings', _('Guardar Configuración API'));
echo '</div>'; 
echo '</div>'; 

// --- TARJETA 2: GRID VIEW TIPO HOJA DE CÁLCULO (PWA_DISPOSITIVOS) ---
echo '<div class="card-box">';
echo '<div class="card-header"><span>' . _('2. Mapeo y Clasificación de Elementos a Rastreo (Hoja de Cálculo)') . '</span></div>';

echo '<p style="color: #666; font-size: 13px;">' . _('Defina el tipo de elemento (Persona, Vehículo, Bodega, Oficina, Cliente u Otros), su modalidad (Móvil para rastreo dinámico o Fijo con coordenadas estáticas), descripción detallada y vinculación en el ERP.') . '</p>';

echo '<table class="spreadsheet-table">';
echo '<thead>
        <tr>
            <th class="col-hidden">' . _('ID') . '</th>
            <th>' . _('Tipo de Elemento') . '</th>
            <th>' . _('Código') . '</th>
            <th>' . _('Device ID / IMEI') . '</th>
            <th>' . _('Es Móvil') . '</th>
            <th>' . _('Latitud (Fijo)') . '</th>
            <th>' . _('Longitud (Fijo)') . '</th>
            <th>' . _('Descripción / Razón') . '</th>
            <th>' . _('Estado') . '</th>
            <th>' . _('Eliminar') . '</th>
        </tr>
      </thead>';
echo '<tbody>';

$tipos_disponibles = ['Persona', 'Vehiculo', 'Bodega', 'Oficina', 'Cliente', 'Otros'];

// Consultar registros existentes
$result_dev = db_query("SELECT * FROM ".TB_PREF."pwa_dispositivos_bodegas ORDER BY id ASC", "No se pudieron cargar los elementos");

while ($dev = db_fetch($result_dev)) {
    $rid = $dev['id'];
    echo '<tr>';
    echo '<td class="col-hidden">' . $rid . '</td>';
    
    // Selector de Tipo de Elemento
    echo '<td>
            <select name="grid_rows['.$rid.'][elemento_tipo]">';
    foreach ($tipos_disponibles as $tipo) {
        $sel = (isset($dev['elemento_tipo']) && $dev['elemento_tipo'] == $tipo) ? 'selected' : '';
        echo "<option value=\"$tipo\" $sel>$tipo</option>";
    }
    echo '</select>
          </td>';

    echo '<td><input type="text" name="grid_rows['.$rid.'][salesman_code]" value="' . htmlspecialchars($dev['salesman_code']) . '"></td>';
    echo '<td><input type="text" name="grid_rows['.$rid.'][device_id]" value="' . htmlspecialchars($dev['device_id']) . '"></td>';
    
    $checked_movil = (isset($dev['es_movil']) ? $dev['es_movil'] : 1) == 1 ? 'checked' : '';
    echo '<td><input type="checkbox" name="grid_rows['.$rid.'][es_movil]" value="1" ' . $checked_movil . '></td>';
    
    echo '<td><input type="text" name="grid_rows['.$rid.'][latitud]" value="' . htmlspecialchars($dev['latitud']) . '" placeholder="0.000000"></td>';
    echo '<td><input type="text" name="grid_rows['.$rid.'][longitud]" value="' . htmlspecialchars($dev['longitud']) . '" placeholder="0.000000"></td>';
    echo '<td><input type="text" name="grid_rows['.$rid.'][descripcion]" value="' . htmlspecialchars($dev['descripcion']) . '" placeholder="Razón del pin..."></td>';
    
    echo '<td>
            <select name="grid_rows['.$rid.'][estado]">
                <option value="activo" ' . ($dev['estado'] == 'activo' ? 'selected' : '') . '>Activo</option>
                <option value="inactivo" ' . ($dev['estado'] == 'inactivo' ? 'selected' : '') . '>Inactivo</option>
                <option value="mantenimiento" ' . ($dev['estado'] == 'mantenimiento' ? 'selected' : '') . '>Mantenimiento</option>
            </select>
          </td>';
          
    echo '<td><input type="checkbox" name="grid_rows['.$rid.'][delete]" value="1" style="accent-color: red;"></td>';
    echo '</tr>';
}

// Fila adicional en blanco para AGREGAR un NUEVO registro fácilmente tipo Excel
echo '<tr style="background-color: #f9f9f9;">';
echo '<td class="col-hidden">new</td>';
echo '<td>
        <select name="grid_rows[new][elemento_tipo]">';
foreach ($tipos_disponibles as $tipo) {
    echo "<option value=\"$tipo\">$tipo</option>";
}
echo '</select>
      </td>';
echo '<td><input type="text" name="grid_rows[new][salesman_code]" placeholder="Ej. 1"></td>';
echo '<td><input type="text" name="grid_rows[new][device_id]" placeholder="IMEI / Token"></td>';
echo '<td><input type="checkbox" name="grid_rows[new][es_movil]" value="1" checked></td>';
echo '<td><input type="text" name="grid_rows[new][latitud]" placeholder="0.000000"></td>';
echo '<td><input type="text" name="grid_rows[new][longitud]" placeholder="0.000000"></td>';
echo '<td><input type="text" name="grid_rows[new][descripcion]" placeholder="Razón del pin..."></td>';
echo '<td>
        <select name="grid_rows[new][estado]">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
            <option value="mantenimiento">Mantenimiento</option>
        </select>
      </td>';
echo '<td>-</td>';
echo '</tr>';

echo '</tbody>';
echo '</table>';

echo '<br>';
submit_center('SaveGridDevices', _('Guardar Cambios en la Cuadrícula'));
echo '</div>'; 

end_form();
end_page();