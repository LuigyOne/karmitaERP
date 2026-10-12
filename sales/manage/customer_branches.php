<?php
/**********************************************************************
	Copyright (C) NotrinosERP.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
	See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_CUSTOMER';
$path_to_root='../..';

include($path_to_root . '/includes/db_pager.inc');
include($path_to_root . '/includes/session.inc');

$js = '';
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(_($help_context = 'Customer Branches'), @$_REQUEST['popup'], false, '', $js);

include($path_to_root . '/includes/ui.inc');
include($path_to_root . '/includes/ui/contacts_view.inc');

//-----------------------------------------------------------------------------------------------

check_db_has_customers(_('There are no customers defined in the system. Please define a customer to add customer branches.'));
check_db_has_sales_people(_('There are no sales people defined in the system. At least one sales person is required before proceeding.'));
check_db_has_sales_areas(_('There are no sales areas defined in the system. At least one sales area is required before proceeding.'));
check_db_has_shippers(_('There are no shipping companies defined in the system. At least one shipping company is required before proceeding.'));
check_db_has_tax_groups(_('There are no tax groups defined in the system. At least one tax group is required before proceeding.'));

simple_page_mode(true);

//-----------------------------------------------------------------------------------------------

if (isset($_GET['debtor_no']))
	$_POST['customer_id'] = strtoupper($_GET['debtor_no']);

$_POST['branch_code'] = $selected_id;

if (isset($_GET['SelectedBranch'])) {
	$br = get_branch($_GET['SelectedBranch']);
	$_POST['customer_id'] = $br['debtor_no'];
	$selected_id = $_POST['branch_code'] = $br['branch_code'];
	$Mode = 'Edit';
}

//-----------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') {

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['br_name']) == 0) {
		$input_error = 1;
		display_error(_('The Branch name cannot be empty.'));
		set_focus('br_name');
	}

	if (strlen($_POST['br_ref']) == 0) {
		$input_error = 1;
		display_error(_('The Branch short name cannot be empty.'));
		set_focus('br_ref');
	}

	if ($input_error != 1) {

		begin_transaction();
		if ($selected_id != -1) {
			cust_branch_entity::modify(
				array('branch_code' => $_POST['branch_code'], 'debtor_no' => $_POST['customer_id']),
				array(
					'br_name' => $_POST['br_name'],
					'branch_ref' => $_POST['br_ref'],
					'br_address' => $_POST['br_address'],
					'salesman' => $_POST['salesman'],
					'area' => $_POST['area'],
					'tax_group_id' => $_POST['tax_group_id'],
					'sales_account' => $_POST['sales_account'],
					'sales_discount_account' => $_POST['sales_discount_account'],
					'receivables_account' => $_POST['receivables_account'],
					'payment_discount_account' => $_POST['payment_discount_account'],
					'default_location' => $_POST['default_location'],
					'br_post_address' => $_POST['br_post_address'],
					'group_no' => $_POST['group_no'],
					'default_ship_via' => $_POST['default_ship_via'],
					'notes' => $_POST['notes'],
					'bank_account' => $_POST['bank_account']
				)
			);

			$note =_('Selected customer branch has been updated');
		}
		else {
			cust_branch_entity::create(array(
				'debtor_no' => $_POST['customer_id'],
				'br_name' => $_POST['br_name'],
				'branch_ref' => $_POST['br_ref'],
				'br_address' => $_POST['br_address'],
				'salesman' => $_POST['salesman'],
				'area' => $_POST['area'],
				'tax_group_id' => $_POST['tax_group_id'],
				'sales_account' => $_POST['sales_account'],
				'sales_discount_account' => $_POST['sales_discount_account'],
				'receivables_account' => $_POST['receivables_account'],
				'payment_discount_account' => $_POST['payment_discount_account'],
				'default_location' => $_POST['default_location'],
				'br_post_address' => $_POST['br_post_address'],
				'group_no' => $_POST['group_no'],
				'default_ship_via' => $_POST['default_ship_via'],
				'notes' => $_POST['notes'],
				'bank_account' => $_POST['bank_account']
			));
			$selected_id = db_insert_id();

			add_crm_person($_POST['contact_name'], $_POST['contact_name'], '', $_POST['br_post_address'], 
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], 
				$_POST['rep_lang'], '');

			add_crm_contact('cust_branch', 'general', $selected_id, db_insert_id());


			$note = _('New customer branch has been added');
		}
		commit_transaction();
		
		// --- INICIO GUARDADO GEOLOCALIZACION Y FOTOS ---
		$actual_branch_id = ($selected_id != -1) ? $_POST['branch_code'] : $selected_id;
		$lat = db_escape(isset($_POST['latitud']) && $_POST['latitud'] !== '' ? $_POST['latitud'] : 'NULL');
		$lon = db_escape(isset($_POST['longitud']) && $_POST['longitud'] !== '' ? $_POST['longitud'] : 'NULL');

		// --- CAPTURA DE LOGÍSTICA ---
		$dias_seleccionados = [];
		$dias_posibles = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
		foreach ($dias_posibles as $d) {
			if (isset($_POST["dia_$d"]) && $_POST["dia_$d"] == '1') {
				$dias_seleccionados[] = $d;
			}
		}
		$dias_str = db_escape(implode(',', $dias_seleccionados));
		$frecuencia = db_escape(isset($_POST['frecuencia_visita']) ? $_POST['frecuencia_visita'] : 'Semanal');
		// ----------------------------
		
		$sql_updates = array("latitud = $lat", "longitud = $lon", "dias_visita = $dias_str", "frecuencia_visita = $frecuencia");

		for ($i = 1; $i <= 4; $i++) {
			
			// Lógica para vaciar la foto si se marcó el checkbox "Eliminar"
			if (isset($_POST["del_foto_$i"]) && $_POST["del_foto_$i"] == 1) {
				$sql_updates[] = "foto_$i = NULL";
			}			
			
			$input_name = "foto_local_$i";
			if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $_FILES[$input_name]['tmp_name'];
				$nombre_archivo = 'branch_' . $actual_branch_id . '_foto_' . $i . '_' . time() . '.jpg';
				$ruta_destino = company_path() . '/images/branches/' . $nombre_archivo;
				
				$imagen_original = @imagecreatefromstring(file_get_contents($tmp_name));
				if ($imagen_original) {
					$ancho_orig = imagesx($imagen_original);
					$alto_orig = imagesy($imagen_original);
					$ancho_max = 800; // Limite optimizado
					
					if ($ancho_orig > $ancho_max) {
						$alto_nuevo = floor($alto_orig * ($ancho_max / $ancho_orig));
						$imagen_red = imagecreatetruecolor($ancho_max, $alto_nuevo);
						imagecopyresampled($imagen_red, $imagen_original, 0, 0, 0, 0, $ancho_max, $alto_nuevo, $ancho_orig, $alto_orig);
						imagejpeg($imagen_red, $ruta_destino, 80); // Compresión al 80%
						imagedestroy($imagen_red);
					} else {
						imagejpeg($imagen_original, $ruta_destino, 80);
					}
					imagedestroy($imagen_original);
					
					$sql_updates[] = "foto_$i = " . db_escape($nombre_archivo);
				}
			}
		}

		if (count($sql_updates) > 0) {
			$sql = "UPDATE " . TB_PREF . "cust_branch SET " . implode(', ', $sql_updates) . " WHERE branch_code = " . db_escape($actual_branch_id);
			db_query($sql, "No se pudieron actualizar las coordenadas o fotos de la sucursal");
		}
		// --- FIN GUARDADO GEOLOCALIZACION Y FOTOS ---
		
		display_notification($note);

		if (@$_REQUEST['popup'])
			set_focus('Select'.($_POST['branch_code'] == -1 ? $selected_id: $_POST['branch_code']));
	}
}
elseif ($Mode == 'Delete') {
	if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'debtor_trans'))
		display_error(_('Cannot delete this branch because customer transactions have been created to this branch.'));
	else {
		if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'sales_orders'))
			display_error(_('Cannot delete this branch because sales orders exist for it. Purge old sales orders first.'));
		else {
			cust_branch_entity::remove(array('branch_code' => $_POST['branch_code'], 'debtor_no' => $_POST['customer_id']));
			display_notification(_('Selected customer branch has been deleted'));
		}
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET' || get_post('_customer_id_update')) {
	$selected_id = -1;
	$cust_id = $_POST['customer_id'];
	$inact = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $inact;
	$_POST['customer_id'] = $cust_id;
	$Ajax->activate('_page_body');
}

function branch_email($row) {
	return	'<a href = "mailto:'.$row["email"].'">'.$row["email"].'</a>';
}

function edit_link($row) {
	return button('Edit'.$row['branch_code'],_('Edit'), '', ICON_EDIT);
}

function del_link($row) {
	return button('Delete'.$row['branch_code'],_('Delete'), '', ICON_DELETE);
}

function select_link($row) {
	return button('Select'.$row['branch_code'], $row['branch_code'], '', ICON_ADD, 'selector');
}

function branch_settings($selected_id, $num_branches) {
	global $Mode;

	start_outer_table(TABLESTYLE2);

	table_section(1);

	$_POST['email'] = '';
	if ($selected_id != -1) {
		if ($Mode == 'Edit' || !isset($_POST['br_name'])) {
			//editing an existing branch
			$myrow = get_cust_branch($_POST['customer_id'], $_POST['branch_code']);
			set_focus('br_name');
			$_POST['branch_code'] = $myrow['branch_code'];
			$_POST['br_name']  = $myrow['br_name'];
			$_POST['br_ref']  = $myrow['branch_ref'];
			$_POST['br_address']  = $myrow['br_address'];
			$_POST['br_post_address']  = $myrow['br_post_address'];
			// --- Añadir estas dos líneas ---
			$_POST['latitud'] = $myrow['latitud'];
			$_POST['longitud'] = $myrow['longitud'];
			// --- Cargar logística y fechas ---
			$_POST['dias_visita'] = isset($myrow['dias_visita']) ? explode(',', $myrow['dias_visita']) : [];
			$_POST['frecuencia_visita'] = isset($myrow['frecuencia_visita']) ? $myrow['frecuencia_visita'] : 'Semanal';
			$_POST['ultima_visita'] = isset($myrow['ultima_visita']) ? $myrow['ultima_visita'] : '';
			$_POST['ultima_venta'] = isset($myrow['ultima_venta']) ? $myrow['ultima_venta'] : '';
			// --- Cargar nombres de fotos ---
			for ($i = 1; $i <= 4; $i++) {
				$_POST["foto_$i"] = isset($myrow["foto_$i"]) ? $myrow["foto_$i"] : '';
			}
			// -------------------------------		
			$_POST['salesman'] =$myrow['salesman'];
			$_POST['area'] =$myrow['area'];
			$_POST['tax_group_id'] = $myrow['tax_group_id'];
			$_POST['default_location'] = $myrow['default_location'];
			$_POST['default_ship_via'] = $myrow['default_ship_via'];
			$_POST['sales_account'] = $myrow['sales_account'];
			$_POST['sales_discount_account'] = $myrow['sales_discount_account'];
			$_POST['receivables_account'] = $myrow['receivables_account'];
			$_POST['payment_discount_account'] = $myrow['payment_discount_account'];
			$_POST['group_no']  = $myrow['group_no'];
			$_POST['notes']  = $myrow['notes'];
			$_POST['bank_account']  = $myrow['bank_account'];
		}
	}
	elseif ($Mode != 'ADD_ITEM') {
		$myrow = get_default_info_for_branch($_POST['customer_id']);
		if($myrow && !$num_branches) {
			$_POST['br_name'] = $myrow['name'];
			$_POST['br_ref'] = $myrow['debtor_ref'];
			$_POST['contact_name'] = _('Main Branch');
			$_POST['br_address'] = $_POST['br_post_address'] = $myrow['address'];
		}
		$_POST['branch_code'] = '';
		if (!isset($_POST['sales_account']) || !isset($_POST['sales_discount_account'])) {
			$company_record = get_company_prefs();

			// We use the Item Sales Account as default!
			// $_POST['sales_account'] = $company_record['default_sales_act'];
			$_POST['sales_account'] = $_POST['notes']  = $_POST['bank_account'] = '';
			// --- Añadir esta línea ---
			$_POST['latitud'] = $_POST['longitud'] = '';
			// --- Añadir esta línea ---
			$_POST['latitud'] = $_POST['longitud'] = '';
			$_POST['dias_visita'] = [];
			$_POST['frecuencia_visita'] = 'Semanal';
			$_POST['ultima_visita'] = '';
			$_POST['ultima_venta'] = '';
			for ($i = 1; $i <= 4; $i++) {
				$_POST["foto_$i"] = '';
			}
			// -------------------------			
			$_POST['sales_discount_account'] = $company_record['default_sales_discount_act'];
			$_POST['receivables_account'] = $company_record['debtors_act'];
			$_POST['payment_discount_account'] = $company_record['default_prompt_payment_act'];
		}
	}
	hidden('popup', @$_REQUEST['popup']);

	table_section_title(_('Name and Contact'));
	text_row(_('Branch Name:'), 'br_name', null, 50, 60);
	text_row(_('Branch Short Name:'), 'br_ref', null, 30, 30);

	table_section_title(_('Sales'));
	sales_persons_list_row( _('Sales Person:'), 'salesman', null);
	sales_areas_list_row( _('Sales Area:'), 'area', null);
	sales_groups_list_row(_('Sales Group:'), 'group_no', null, true);
	locations_list_row(_('Default Inventory Location:'), 'default_location', null);
	shippers_list_row(_('Default Shipping Company:'), 'default_ship_via', null);
	tax_groups_list_row(_('Tax Group:'), 'tax_group_id', null);

	table_section_title(_('GL Accounts'));

	gl_all_accounts_list_row(_('Sales Account:'), 'sales_account', null, false, false, true);
	gl_all_accounts_list_row(_('Sales Discount Account:'), 'sales_discount_account');
	gl_all_accounts_list_row(_('Accounts Receivable Account:'), 'receivables_account', null, true);
	gl_all_accounts_list_row(_('Prompt Payment Discount Account:'), 'payment_discount_account');
	text_row(_('Bank Account Number:'), 'bank_account', null, 30, 60);

	table_section(2);

	if($selected_id==-1) {
		table_section_title(_('General contact data'));
		text_row(_('Contact Person:'), 'contact_name', null, 35, 40);
		text_row(_('Phone Number:'), 'phone', null, 32, 30);
		text_row(_('Secondary Phone Number:'), 'phone2', null, 32, 30);
		text_row(_('Fax Number:'), 'fax', null, 32, 30);
		email_row(_('E-mail:'), 'email', null, 35, 55);
		languages_list_row( _('Document Language:'), 'rep_lang', null, _('Customer default'));
	}
	table_section_title(_('Addresses'));
	textarea_row(_('Mailing Address:'), 'br_post_address', null, 35, 4);
	textarea_row(_('Billing Address:'), 'br_address', null, 35, 4);
	
	// --- INICIO INTERFAZ GEOLOCALIZACION Y FOTOS ---
	// text_row(_('Latitud:'), 'latitud', null, 20, 20);
	// text_row(_('Longitud:'), 'longitud', null, 20, 20);
	
	// --- INICIO INTERFAZ GEOLOCALIZACION Y MAPA ---
	$lat_val = isset($_POST['latitud']) ? $_POST['latitud'] : '';
	$lon_val = isset($_POST['longitud']) ? $_POST['longitud'] : '';
	
	// 1. Textboxes reubicados horizontalmente (Forzando CSS Inline)
	echo "<tr>";
	echo "<td class='label'>" . _("Coordenadas:") . "</td>";
	echo "<td>";
	echo "<div style='display: flex; align-items: center; gap: 20px;'>";
	echo "<div><b>" . _("Latitud:") . "</b> <input type='text' name='latitud' value='" . $lat_val . "' style='width: 150px; display: inline-block; margin-left: 5px;'></div>";
	echo "<div><b>" . _("Longitud:") . "</b> <input type='text' name='longitud' value='" . $lon_val . "' style='width: 150px; display: inline-block; margin-left: 5px;'></div>";
	echo "</div>";
	echo "</td>";
	echo "</tr>";

	// 2. Carga condicional del iFrame de Google Maps (VERSIÓN SIN API KEY)
	if ($lat_val != '' && $lon_val != '') {
		echo "<tr><td colspan='2' style='padding: 15px 0;'>";
		echo "<iframe width='100%' height='300' frameborder='0' style='border: 1px solid #ccc; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);' src='https://maps.google.com/maps?q=" . $lat_val . "," . $lon_val . "&hl=es&z=17&output=embed'></iframe>";
		echo "</td></tr>";
	}
	// --- FIN INTERFAZ GEOLOCALIZACION Y MAPA ---

	// --- INICIO INTERFAZ LOGÍSTICA DE VISITAS ---
	echo "<tr><td class='label'>" . _("Logística de Ruta:") . "</td><td style='padding: 10px 0;'>";
	echo "<div style='display:flex; gap:30px; align-items:center; background:#f9f9f9; padding:10px; border:1px solid #e5e5e5; border-radius:5px;'>";
	
	// Selector de Frecuencia
	echo "<div><b>Frecuencia:</b> <select name='frecuencia_visita' style='padding:3px;'>";
	$opciones = ['Semanal', 'Quincenal', 'Mensual'];
	$frec_actual = isset($_POST['frecuencia_visita']) ? $_POST['frecuencia_visita'] : 'Semanal';
	foreach($opciones as $op) {
		$sel = ($frec_actual == $op) ? "selected" : "";
		echo "<option value='$op' $sel>$op</option>";
	}
	echo "</select></div>";
	
	// Checkboxes de Días
	echo "<div><div style='margin-bottom: 5px;'><b>Días de Visita:</b></div>";
	$dias_map = ['L'=>'L', 'M'=>'M', 'X'=>'X', 'J'=>'J', 'V'=>'V', 'S'=>'S', 'D'=>'D'];
	$dias_guardados = (isset($_POST['dias_visita']) && is_array($_POST['dias_visita'])) ? $_POST['dias_visita'] : [];
	foreach($dias_map as $val => $label) {
		$chk = in_array($val, $dias_guardados) ? "checked" : "";
		echo "<label style='margin-right:8px; cursor:pointer;'><input type='checkbox' name='dia_$val' value='1' $chk> $label</label>";
	}
	echo "</div>";
	echo "</div>";
	echo "</td></tr>";

	// --- INICIO INTERFAZ HISTORIAL (SOLO LECTURA) ---
	$uv_val = (!empty($_POST['ultima_visita']) && $_POST['ultima_visita'] != '0000-00-00') ? date('d/m/Y', strtotime($_POST['ultima_visita'])) : 'Sin registro';
	$uven_val = (!empty($_POST['ultima_venta']) && $_POST['ultima_venta'] != '0000-00-00') ? date('d/m/Y', strtotime($_POST['ultima_venta'])) : 'Sin registro';
	
	echo "<tr><td class='label'>" . _("Historial Comercial:") . "</td><td style='padding: 5px 0 15px 0;'>";
	echo "<div style='display:flex; gap:20px;'>";
	echo "<div><b>Última Visita:</b> <input type='text' value='$uv_val' readonly style='background:#f0f0f0; border:1px solid #ccc; width:120px; text-align:center; color:#555;'></div>";
	echo "<div><b>Última Venta:</b> <input type='text' value='$uven_val' readonly style='background:#e8f4f8; border:1px solid #b3d7e5; width:120px; text-align:center; color:#0c5460;'></div>";
	echo "</div>";
	echo "</td></tr>";
	// --- FIN INTERFAZ HISTORIAL ---


	// --- INICIO INTERFAZ FOTOS CON MINIATURAS (ENLACE A NUEVA PESTAÑA) ---
	echo "<tr><td class='label'>" . _("Fotografías del Local (JPG/PNG):") . "</td><td style='padding: 10px 0;'>";
	echo "<div style='display:flex; gap:15px; flex-wrap:wrap;'>";
	
	for ($i = 1; $i <= 4; $i++) {
		echo "<div style='border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #fdfdfd; width: 230px;'>";
		echo "<div style='margin-bottom: 8px; color: #333;'><b>Foto $i:</b></div>";
		echo "<input type='file' name='foto_local_$i' accept='image/*' style='width: 100%; font-size: 11px;'><br>";
		
		$foto_name = isset($_POST["foto_$i"]) ? $_POST["foto_$i"] : '';
		if ($foto_name != '') {
			$ruta_img = company_path() . '/images/branches/' . $foto_name;
			
			// Caja blanca de miniatura con enlace a nueva pestaña
			echo "<div style='margin-top: 10px; padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; text-align: center;'>";
			echo "<a href='$ruta_img' target='_blank' title='Ver imagen a resolución completa'>";
			echo "<img src='$ruta_img' style='height: 80px; max-width: 100%; object-fit: contain; cursor: pointer; transition: transform 0.2s;' onmouseover=\"this.style.transform='scale(1.05)'\" onmouseout=\"this.style.transform='scale(1)'\" alt='Foto $i'>";
			echo "</a>";
			echo "</div>";
			
			// Checkbox de Eliminación
			echo "<div style='margin-top: 8px; font-size: 12px; padding: 5px; border: 1px solid #eee; background: #fff; border-radius: 3px;'>";
			echo "<input type='checkbox' name='del_foto_$i' value='1' id='del_foto_$i'> <label for='del_foto_$i' style='cursor:pointer;'>Eliminar Imagen</label>";
			echo "</div>";
		}
		echo "</div>";
	}
	echo "</div>";
	echo "</td></tr>";
	// --- FIN INTERFAZ FOTOS ---
	
	textarea_row(_('General Notes:'), 'notes', null, 35, 4);

	end_outer_table(1);
	submit_add_or_update_center($selected_id == -1, '', 'both');
}

start_form(true); // El 'true' activa enctype='multipart/form-data'

echo '<center>' . _('Select a customer: ') . '&nbsp;&nbsp;';
echo customer_list('customer_id', null, false, true);
echo '</center><br>';

$num_branches = db_customer_has_branches(get_post('customer_id'));

$sql = get_sql_for_customer_branches(get_post('customer_id'));

//------------------------------------------------------------------------------------------------

if ($num_branches) {
	$cols = array(
		'branch_code' => 'skip',
		_('Short Name'),
		_('Name'),
		_('Contact'),
		_('Sales Person'),
		_('Area'),
		_('Phone No'),
		_('Fax No'),
		_('E-mail') => 'email',
		_('Tax Group'),
		_('Inactive') => 'inactive',
		' '=> array('insert'=>true, 'fun'=>'select_link'),
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'del_link')
	);

	if (!@$_REQUEST['popup'])
		$cols[' '] = 'skip';
	
	$table =& new_db_pager('branch_tbl', $sql, $cols, 'cust_branch');
	$table->set_inactive_ctrl('cust_branch', 'branch_code');

	display_db_pager($table);
}
else
	display_note(_('The selected customer does not have any branches. Please create at least one branch.'));

tabbed_content_start('tabs', array(
		'settings' => array(_('&General settings'), $selected_id!=-1),
		'contacts' => array(_('&Contacts'), $selected_id!=-1),
		// 'orders' => array('S&ales orders', $selected_id!=-1) // not implemented
));
	
switch (get_post('_tabs_sel')) {
	default:
	case 'settings':
		branch_settings($selected_id, $num_branches); 
		break;
	case 'contacts':
		$contacts = new contacts('contacts', $selected_id, 'cust_branch');
		$contacts->show();
		break;
	case 'orders':
};
hidden('branch_code');
hidden('selected_id', $selected_id);
br();
tabbed_content_end();

end_form();

end_page(@$_REQUEST['popup']);
