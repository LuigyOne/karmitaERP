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

function set_global_supplier($supplier_id)  {
	$_SESSION['wa_global_supplier_id'] = $supplier_id; 
}

function get_global_supplier($return_all=true)  {
	if (!isset($_SESSION['wa_global_supplier_id']) || 
		($return_all == false && $_SESSION['wa_global_supplier_id'] == ALL_TEXT))
		return '';	
	return $_SESSION['wa_global_supplier_id'];
}

function set_global_stock_item($stock_id) {
	$_SESSION['wa_global_stock_id'] = $stock_id; 
}

function get_global_stock_item($return_all=true) {
	if (!isset($_SESSION['wa_global_stock_id']) || 
		($return_all == false && $_SESSION['wa_global_stock_id'] == ALL_TEXT))
		return '';		
	return $_SESSION['wa_global_stock_id'];
}

function set_global_customer($customer_id) {
	$_SESSION['wa_global_customer_id'] = $customer_id; 
}

function get_global_customer($return_all=true) {
	if (!isset($_SESSION['wa_global_customer_id']) || 
		($return_all == false && $_SESSION['wa_global_customer_id'] == ALL_TEXT))
		return '';
	return $_SESSION['wa_global_customer_id'];
}

function set_global_employee($employee_id) {
	$_SESSION['wa_global_employee_id'] = $employee_id; 
}

function get_global_employee($return_all=true) {
	if (!isset($_SESSION['wa_global_employee_id']) || 
		($return_all == false && $_SESSION['wa_global_employee_id'] == ALL_TEXT))
		return '';
	return $_SESSION['wa_global_employee_id'];
}

function set_global_curr_code($curr_code) {
	$_SESSION['wa_global_curr_code'] = $curr_code;
}

function get_global_curr_code() {
	if (!isset($_SESSION['wa_global_curr_code']))
		return '';
	return $_SESSION['wa_global_curr_code'];
}

function count_array($array) {
	return (is_array($array)) ? count($array) : (($array === NULL) ? 0 : 1);
}

/**
 * Valida Cédula (10 dígitos) o RUC (13 dígitos) de Ecuador.
 * Soporta Personas Naturales, Sociedades Privadas y Públicas.
 *
 * @param string $documento Número de identificación ingresado.
 * @return array Retorna un array ['valido' => bool, 'mensaje' => string]
 */
function validar_identificacion_ecuador($documento) {
    // 1. Limpiar el string de espacios o guiones
    $documento = preg_replace('/[^0-9]/', '', trim($documento));
    $longitud = strlen($documento);

    // 2. Validar tamaño inicial
    if ($longitud !== 10 && $longitud !== 13) {
        return [
            'valido' => false, 
            'mensaje' => "El tamaño de la identificación no es válido. Debe tener 10 caracteres para Cédula o 13 para RUC."
        ];
    }

    // 3. Validar código de provincia (01 a 24, o 30 para residentes en el exterior)
    $provincia = (int)substr($documento, 0, 2);
    if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
        return ['valido' => false, 'mensaje' => "El código de provincia (primeros dos dígitos) no es válido en Ecuador."];
    }

    // 4. Particularidad del RUC: Validar establecimiento final
    if ($longitud === 13) {
        $establecimiento = substr($documento, 10, 3);
        if ($establecimiento === '000') {
            return ['valido' => false, 'mensaje' => "El RUC es inválido. Los últimos tres dígitos del establecimiento no pueden ser 000."];
        }
    }

    // 5. Determinar tipo de documento por el tercer dígito
    $tercerDigito = (int)$documento[2];
    
    if ($tercerDigito < 6) {
        // ---- PERSONA NATURAL O CÉDULA (Módulo 10) ----
        $coeficientes = array(2, 1, 2, 1, 2, 1, 2, 1, 2);
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $valor = (int)$documento[$i] * $coeficientes[$i];
            if ($valor >= 10) { $valor -= 9; }
            $suma += $valor;
        }
        $verificadorCalculado = (10 - ($suma % 10)) % 10;
        $verificadorReal = (int)$documento[9];
        
    } elseif ($tercerDigito === 9) {
        // ---- JURÍDICOS / SOCIEDADES PRIVADAS (Módulo 11) ----
        $coeficientes = array(4, 3, 2, 7, 6, 5, 4, 3, 2);
        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $suma += (int)$documento[$i] * $coeficientes[$i];
        }
        $residuo = $suma % 11;
        $verificadorCalculado = ($residuo === 0) ? 0 : (11 - $residuo);
        $verificadorReal = (int)$documento[9];
        
    } elseif ($tercerDigito === 6) {
        // ---- ENTIDADES PÚBLICAS (Módulo 11) ----
        $coeficientes = array(3, 2, 7, 6, 5, 4, 3, 2);
        $suma = 0;
        for ($i = 0; $i < 8; $i++) {
            $suma += (int)$documento[$i] * $coeficientes[$i];
        }
        $residuo = $suma % 11;
        $verificadorCalculado = ($residuo === 0) ? 0 : (11 - $residuo);
        $verificadorReal = (int)$documento[8];
    } else {
        return ['valido' => false, 'mensaje' => "El tercer dígito de la identificación no es válido bajo las normas del SRI."];
    }

    // 6. Confrontar dígito verificador
    if ($verificadorCalculado !== $verificadorReal) {
        $tipoDoc = ($longitud === 10) ? "La Cédula" : "El RUC";
        return ['valido' => false, 'mensaje' => "$tipoDoc ingresado es incorrecto (Fallo de dígito verificador)."];
    }

    return ['valido' => true, 'mensaje' => "Identificación válida."];
}
