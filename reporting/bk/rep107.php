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
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ? 'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
$path_to_root = '..';

include_once($path_to_root.'/includes/session.inc');
include_once($path_to_root.'/includes/date_functions.inc');
include_once($path_to_root.'/includes/data_checks.inc');
include_once($path_to_root.'/sales/includes/sales_db.inc');

//----------------------------------------------------------------------------------------------------

function get_invoice_range($from, $to, $currency=false) {
	global $SysPrefs;

	$ref = ($SysPrefs->print_invoice_no() == 1 ? 'trans_no' : 'reference');

	$sql = "SELECT trans.trans_no, trans.reference FROM ".TB_PREF."debtor_trans trans LEFT JOIN ".TB_PREF."voided voided ON trans.type=voided.type AND trans.trans_no=voided.id";

	if ($currency !== false)
		$sql .= " LEFT JOIN ".TB_PREF."debtors_master cust ON trans.debtor_no=cust.debtor_no";

	$sql .= " WHERE trans.type=".ST_SALESINVOICE
		." AND ISNULL(voided.id)"
		." AND trans.trans_no BETWEEN ".db_escape($from)." AND ".db_escape($to);			

	if ($currency !== false)
		$sql .= " AND cust.curr_code=".db_escape($currency);

	$sql .= " ORDER BY trans.tran_date, trans.$ref";

	return db_query($sql, 'Could not retrieve invoice range');
}

print_invoices();

//----------------------------------------------------------------------------------------------------

// Fetch bank account details by bank name (used for XRPL overrides)
function get_bank_account_by_name($name) {
    $sql = "SELECT * FROM ".TB_PREF."bank_accounts WHERE bank_name = ".db_escape($name);
    $result = db_query($sql, "could not retrieve bank account");
    $row = db_fetch($result);
    return $row ? $row : false;
}

//----------------------------------------------------------------------------------------------------

function print_invoices() {
	global $path_to_root, $SysPrefs;

	include_once($path_to_root.'/reporting/includes/pdf_report.inc');

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	$pay_service = $_POST['PARAM_4'];
	$comments = $_POST['PARAM_5'];
	$customer = $_POST['PARAM_6'];
	$orientation = $_POST['PARAM_7'];

	if (!$from || !$to)
		return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = 2;
	// $dec = user_price_dec();

	$fno = explode('-', $from);
	$tno = explode('-', $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);

	//-------------code-Descr-Qty--uom--tax--prc--Disc-Tot--//
	$cols = array(4, 60, 225, 300, 325, 385, 450, 515);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'right', 'center', 'right', 'right', 'right');

	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	if ($email == 0)
		$rep = new FrontReport(_('INVOICE'), 'InvoiceBulk', user_pagesize(), 9, $orientation);
	if ($orientation == 'L')
		recalculate_cols($cols);

	$range = Array();
	$range = $currency == ALL_TEXT ? get_invoice_range($from, $to) : get_invoice_range($from, $to, $currency);;

	while($row = db_fetch($range)) {
		if (!exists_customer_trans(ST_SALESINVOICE, $row['trans_no']))
			continue;
		$sign = 1;
		$myrow = get_customer_trans($row['trans_no'], ST_SALESINVOICE);

		if ($customer && $myrow['debtor_no'] != $customer)
			continue;
			
		if (substr($pay_service, 0, 4) === 'XRPL') {
			// Override: use XUMM XRP bank account for display
   			 $baccount = get_bank_account_by_name('XUMM XRP');
		}
		else {
			// Default logic
			$baccount = get_default_bank_account($myrow['curr_code']);
		}
		$params['bankaccount'] = $baccount['id'];

		$branch = get_branch($myrow['branch_code']);
		$sales_order = get_sales_order_header($myrow['order_'], ST_SALESORDER);
		if ($email == 1) {
			$rep = new FrontReport('', '', user_pagesize(), 9, $orientation);
			$rep->title = _('INVOICE');
			$rep->filename = 'Invoice' . $myrow['reference'] . '.pdf';
		}	
		$rep->currency = $cur;
		$rep->Font();
		$rep->Info($params, $cols, null, $aligns);

		$contacts = get_branch_contacts($branch['branch_code'], 'invoice', $branch['debtor_no'], true);
		$baccount['payment_service'] = $pay_service;
		$rep->SetCommonData($myrow, $branch, $sales_order, $baccount, ST_SALESINVOICE, $contacts);
		$rep->SetHeaderType('Header2');
		$rep->NewPage();
		// calculate summary start row for later use
		$summary_start_row = $rep->bottomMargin + (15 * $rep->lineHeight);

		$show_this_payment = $rep->formData['prepaid'] == 'partial'; // include payments invoiced here in summary

		if ($rep->formData['prepaid']) {
			$result = get_sales_order_invoices($myrow['order_']);
			$prepayments = array();
			while($inv = db_fetch($result)) {
				$prepayments[] = $inv;
				if ($inv['trans_no'] == $row['trans_no'])
				break;
			}

			if (count($prepayments) > ($show_this_payment ? 0 : 1))
				$summary_start_row += (count($prepayments)) * $rep->lineHeight;
			else
				unset($prepayments);
		}

		$result = get_customer_trans_details(ST_SALESINVOICE, $row['trans_no']);
		$SubTotal = 0;
		$TotalDescuento = 0; // Agregado para acumular descuentos

		while ($myrow2=db_fetch($result)) {

			if ($myrow2['quantity'] == 0)
				continue;

			// Extraer el descuento específico de esta línea
			$LineBruto = $sign * $myrow2['unit_price'] * $myrow2['quantity'];
			$LineDesc = $LineBruto * $myrow2['discount_percent'];
			$TotalDescuento += $LineDesc;

			$Net = round2($sign * ((1 - $myrow2['discount_percent']) * $myrow2['unit_price'] * $myrow2['quantity']), user_price_dec());
			$SubTotal += $Net;

			$DisplayPrice = number_format2($myrow2['unit_price'],$dec);
			$DisplayQty = number_format2($sign*$myrow2['quantity'],get_qty_dec($myrow2['stock_id']));
			$DisplayNet = number_format2($Net,$dec);

			if ($myrow2['discount_percent'] == 0)
				$DisplayDiscount = '';
			else
				$DisplayDiscount = number_format2($myrow2['discount_percent']*100,user_percent_dec()) . '%';
			$c=0;
			$rep->TextCol($c++, $c,	$myrow2['stock_id'], -2);
			$oldrow = $rep->row;
			$rep->TextColLines($c++, $c, $myrow2['StockDescription'], -2);
			if (!empty($SysPrefs->prefs['long_description_invoice']) && !empty($myrow2['StockLongDescription'])) {
				$c--;
				$rep->TextColLines($c++, $c, $myrow2['StockLongDescription'], -2);
			}
			$newrow = $rep->row;
			$rep->row = $oldrow;
			if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !$SysPrefs->no_zero_lines_amount()) {
				$rep->TextCol($c++, $c,	$DisplayQty, -2);
				$rep->TextCol($c++, $c,	$myrow2['units'], -2);
				$rep->TextCol($c++, $c,	$DisplayPrice, -2);
				$rep->TextCol($c++, $c,	$DisplayDiscount, -2);
				$rep->TextCol($c++, $c,	$DisplayNet, -2);
			}
			$rep->row = $newrow;
			
			if ($rep->row < $summary_start_row)
				$rep->NewPage();
		}

		$memo = get_comments_string(ST_SALESINVOICE, $row['trans_no']);
		if ($memo != '') {
			$rep->NewLine();
			$rep->TextColLines(1, 3, $memo, -2);
		}

		$DisplaySubTot = number_format2($SubTotal,$dec);

		// set to start of summary line:
		$rep->row = $summary_start_row;
		if (isset($prepayments)) {
			// Partial invoices table
			$rep->TextCol(0, 3,_('Prepayments invoiced to this order up to day:'));
			$rep->TextCol(0, 3,	str_pad('', 150, '_'));
			$rep->cols[2] -= 20;
			$rep->aligns[2] = 'right';
			$rep->NewLine(); $c = 0; $tot_pym=0;
			$rep->TextCol(0, 3,	str_pad('', 150, '_'));
			$rep->TextCol($c++, $c, _('Date'));
			$rep->TextCol($c++, $c,	_('Invoice reference'));
			$rep->TextCol($c++, $c,	_('Amount'));

			foreach ($prepayments as $invoice) {
				if ($show_this_payment || ($invoice['reference'] != $myrow['reference'])) {
					$rep->NewLine();
					$c = 0; $tot_pym += $invoice['prep_amount'];
					$rep->TextCol($c++, $c,	sql2date($invoice['tran_date']));
					$rep->TextCol($c++, $c,	$invoice['reference']);
					$rep->TextCol($c++, $c, number_format2($invoice['prep_amount'], $dec));
				}
				if ($invoice['reference']==$myrow['reference']) break;
			}
			$rep->TextCol(0, 3,	str_pad('', 150, '_'));
			$rep->NewLine();
			$rep->TextCol(1, 2,	_('Total payments:'));
			$rep->TextCol(2, 3,	number_format2($tot_pym, $dec));
		}
		$doctype = ST_SALESINVOICE;

		// === INICIO DE REEMPLAZO DE TOTALES SRI ===
		$rep->row = $summary_start_row; // Anclamos al fondo de la cuadrícula de detalles

		// 1. Extraer los valores maestros directos de la cabecera de la BD
		// Esto ignora cualquier duplicación de tablas secundarias o funciones defectuosas
		$SubTotalNeto = round2($myrow['ov_amount'] * $sign, 2);
		$total_iva = round2($myrow['ov_gst'] * $sign, 2);
		
		// 2. Ingeniería inversa para separar bases imponibles
		// Si el IVA real cobrado es $9.00, la base al 15% es matemáticamente exacta: 9.00 / 0.15 = 60.00
		$sub_15 = ($total_iva > 0) ? round2($total_iva / 0.15, 2) : 0.00;
		
		// El subtotal 0% es la diferencia exacta entre el Subtotal Maestro y la base gravada
		$sub_0 = round2($SubTotalNeto - $sub_15, 2);
		if ($sub_0 < 0) $sub_0 = 0.00;

		// VALOR TOTAL = Subtotal Maestro + IVA 
		// (El SubTotalNeto ya tiene los descuentos restados nativamente por el motor del ERP)
		$DisplayTotal = round2($SubTotalNeto + $total_iva, 2);

		// 3. Coordenadas de la Caja de Totales (Derecha)
		$box_w = 180;
		$box_x = $rep->pageWidth - $rep->rightMargin - $box_w;
		$y_line = $rep->row; 

		$rep->SetDrawColor(0, 0, 0);
		$rep->SetLineWidth(0.5);

		// Array de la ficha técnica SRI 
		$sri_totals = [
			"SUBTOTAL 15%" => number_format2($sub_15, 2),
			"SUBTOTAL 0%" => number_format2($sub_0, 2),
			"SUBTOTAL No objeto IVA" => "0.00",
			"SUBTOTAL Exento IVA" => "0.00",
			"SUBTOTAL SIN IMPUESTOS" => number_format2($SubTotalNeto, 2),
			"TOTAL Descuento" => number_format2($TotalDescuento, 2), 
			"ICE" => "0.00",
			"IVA 15%" => number_format2($total_iva, 2),
			"PROPINA" => "0.00",
			"VALOR TOTAL" => number_format2($DisplayTotal, 2)
		];

		// 3. Dibujamos la tabla de totales
		$row_h = 14;
		$rep->fontSize -= 1;
		foreach ($sri_totals as $label => $val) {
			$rep->rectangle($box_x, $y_line, $box_w, $row_h, "D"); 
			$rep->LineTo($box_x + 120, $y_line, $box_x + 120, $y_line - $row_h); 
			
			if ($label == "VALOR TOTAL") $rep->Font('bold');
			
			$rep->TextWrap($box_x + 5, $y_line - 10, 110, $label, 'L');
			$rep->TextWrap($box_x + 125, $y_line - 10, 50, $val, 'R');
			
			if ($label == "VALOR TOTAL") $rep->Font('');
			
			$y_line -= $row_h;
		}
		$rep->fontSize += 1;

		// 4. Caja de Información Adicional (Izquierda)
		$info_w = $rep->pageWidth - $rep->leftMargin - $rep->rightMargin - $box_w - 10;
		$info_x = $rep->leftMargin;
		$info_y = $rep->row;
		
		$rep->rectangle($info_x, $info_y, $info_w, 65, "D");
		$rep->Font('bold');
		$rep->TextWrap($info_x, $info_y - 12, $info_w, "Información Adicional", 'C');
		$rep->Font('');
		$rep->LineTo($info_x, $info_y - 15, $info_x + $info_w, $info_y - 15);

		// Obtener el direccion cliente (si no existe, muestra 'sucursal')		
        $dir_cliente = !empty($myrow['delivery_address']) ? $myrow['delivery_address'] : $branch['br_address'];
		
		// Obtener el Email (si no existe, muestra 'No registrado')
		$email_cliente = (isset($contacts[0]['email']) && !empty($contacts[0]['email'])) ? $contacts[0]['email'] : 'No registrado';

		// Obtener el Teléfono (si no existe, muestra 'No registrado')
		$telefono_cliente = (isset($contacts[0]['phone']) && !empty($contacts[0]['phone'])) ? $contacts[0]['phone'] : 'No registrado';


		// 1. Línea: Dirección (con título)
		$rep->TextWrap($info_x + 5, $info_y - 28, 60, "Dirección:", 'L');
		$rep->TextWrap($info_x + 65, $info_y - 28, $info_w - 70, $dir_cliente, 'L');
		
		// Extraer el Nombre Comercial / Alias del cliente
		$alias_cliente = !empty($myrow['debtor_ref']) ? $myrow['debtor_ref'] : '';
		
		// 2. Línea: Nombre Comercial (sin título, aprovechando todo el ancho)
		if (!empty($alias_cliente)) {
			$rep->TextWrap($info_x + 5, $info_y - 42, $info_w - 10, $alias_cliente, 'L');
		}
		
		// Construir texto de la 3. Línea: (Teléfono) Email
		$texto_contacto = "";
		// Omitimos el texto si viene con el default "No registrado" para mantener la limpieza
		if (!empty($telefono_cliente) && $telefono_cliente !== 'No registrado') {
			$texto_contacto .= "(" . $telefono_cliente . ") ";
		}
		if (!empty($email_cliente) && $email_cliente !== 'No registrado') {
			$texto_contacto .= $email_cliente;
		}
		
		// 3. Línea: Contactos (sin título)
		if (!empty(trim($texto_contacto))) {
			$rep->TextWrap($info_x + 5, $info_y - 56, $info_w - 10, trim($texto_contacto), 'L');
		}		

		// 5. Caja de Formas de Pago
		$pago_y = $info_y - 75;
		$rep->rectangle($info_x, $pago_y, $info_w, 30, "D");
		$rep->LineTo($info_x + 200, $pago_y, $info_x + 200, $pago_y - 30); 
		
		$rep->Font('bold');
		$rep->TextWrap($info_x, $pago_y - 12, 200, "Forma de Pago", 'C');
		$rep->TextWrap($info_x + 200, $pago_y - 12, $info_w - 200, "Valor", 'C');
		$rep->Font('');
		$rep->LineTo($info_x, $pago_y - 15, $info_x + $info_w, $pago_y - 15);
		
		$rep->TextWrap($info_x + 5, $pago_y - 26, 190, "20 - OTROS CON UTILIZACION DEL...", 'L');
		$rep->TextWrap($info_x + 200, $pago_y - 26, $info_w - 205, number_format2($DisplayTotal, $dec), 'R');
		// === FIN DE REEMPLAZO DE TOTALES SRI ===

    	if ($email == 1)
			$rep->End($email, sprintf(_("Invoice %s from %s"), $myrow['reference'], htmlspecialchars_decode(get_company_pref('coy_name'))));
	}
	if ($email == 0)
		$rep->End();
}
