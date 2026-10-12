<?php
// === INICIO HEADER SRI DINÁMICO Y SEGURO ===

// --- EXTRAER VARIABLES DE SYS_PREFS ---
$sri_ambiente  = get_company_pref('sri_ambiente_emision');
$sri_obligado  = get_company_pref('sri_obligado_contabilidad');
$sri_rimpe     = get_company_pref('sri_contribuyente_rimpe');
$sri_agente    = get_company_pref('sri_agente_retencion');
$sri_especial  = get_company_pref('sri_contribuyente_especial');
$sri_fantasia  = $this->company['coy_no'];

// --- DARLES FORMATO PARA EL TEXTO DEL PDF ---
// Si ambiente es 1 es Producción, si es 2 (o diferente) es Pruebas
$texto_ambiente = ($sri_ambiente == 2) ? "PRODUCCIÓN" : "PRUEBAS";

// Si obligado es 1 es SI, de lo contrario NO
$texto_obligado = ($sri_obligado == 1) ? "SI" : "NO";

$page_width = $this->pageWidth; 
$top_y = $this->pageHeight - $this->topMargin; 

// Extraer datos reales del SRI desde la base de datos
$clave_acceso = isset($this->formData['sri_clave_acceso']) ? $this->formData['sri_clave_acceso'] : '';
$fecha_aut   = isset($this->formData['sri_fecha_autorizacion']) ? date('d/m/Y H:i:s', strtotime($this->formData['sri_fecha_autorizacion'])) : 'N/A';
$ruc_emisor  = isset($this->company['coy_no']) ? $this->company['coy_no'] : "1713871174001"; 

if (empty($clave_acceso) || $clave_acceso == 'PENDIENTE DE AUTORIZACIÓN') {
    $estado_texto = "PENDIENTE DE AUTORIZACIÓN";
} else {
    $estado_texto = $clave_acceso;
}

// === 1. CUADRO DERECHO (R.U.C., FACTURA, AUTORIZACIÓN, CLAVE) ===
$rect_w = 230; 
$rect_h = 240; 
$rect_x = $page_width - $this->rightMargin - $rect_w; 
$rect_y_top = $top_y; 

$this->SetDrawColor(0, 0, 0);
$this->SetLineWidth(0.5);
$this->rectangle($rect_x, $rect_y_top, $rect_w, $rect_h, "D");

$this->fontSize += 2;
$this->TextWrap($rect_x + 10, $rect_y_top - 20, $rect_w - 20, "R.U.C.:   " . $this->company['gst_no'], 'L');
$this->fontSize -= 2;

$this->fontSize += 4;
$this->Font('bold');
$this->TextWrap($rect_x + 10, $rect_y_top - 45, $rect_w - 20, "FACTURA", 'L');
$this->Font('');
$this->fontSize -= 4;

$this->TextWrap($rect_x + 10, $rect_y_top - 65, $rect_w - 20, "No. " . $this->formData['sri_secuencial'], 'L');
$this->TextWrap($rect_x + 10, $rect_y_top - 85, $rect_w - 20, "NÚMERO DE AUTORIZACIÓN", 'L');

$this->fontSize -= 1;
$this->TextWrap($rect_x + 10, $rect_y_top - 100, $rect_w - 20, $estado_texto, 'L');
$this->fontSize += 1;

$this->TextWrap($rect_x + 10, $rect_y_top - 120, 110, "FECHA Y HORA AUT.:", 'L');
$this->TextWrap($rect_x + 115, $rect_y_top - 120, 100, $fecha_aut, 'L');

$this->TextWrap($rect_x + 10, $rect_y_top - 135, 110, "AMBIENTE:", 'L');
$this->TextWrap($rect_x + 115, $rect_y_top - 135, 100, $texto_ambiente, 'L');

$this->TextWrap($rect_x + 10, $rect_y_top - 150, 110, "EMISIÓN:", 'L');
$this->TextWrap($rect_x + 115, $rect_y_top - 150, 100, "NORMAL", 'L');

$this->TextWrap($rect_x + 10, $rect_y_top - 170, $rect_w - 20, "CLAVE DE ACCESO", 'L');

$this->fontSize -= 2;
$this->TextWrap($rect_x + 10, $rect_y_top - 182, $rect_w - 20, $estado_texto, 'C');
$this->fontSize += 2;

// === GENERADOR VECTORIAL MANUAL CODE 128B SRI ===
$clave_limpia = trim(preg_replace('/[^0-9]/', '', $clave_acceso));

if (!empty($clave_limpia) && strlen($clave_limpia) == 49) {
    $barcode_x = $rect_x + 12;
    $barcode_y = $rect_y_top - 195;
    $barcode_w = $rect_w - 24;
    $barcode_h = 24;

    // Matriz 100% completa de 107 caracteres para evitar fallos de cálculo
    $c128 = array(
        "212222","222122","222221","121223","121322","131222","122213","122312","132212","221213",
        "221312","231212","112232","122132","122231","113222","123122","123221","223112","223211",
        "212312","221321","312122","321122","321221","312212","322112","322211","212123","212321",
        "232121","111323","131123","131321","112313","132113","132311","211313","231113","231311",
        "112133","112331","132131","113123","113321","133121","313121","211331","231131","213113",
        "213311","213131","311123","311321","331121","312113","312311","332111","314111","221411",
        "431111","111224","111422","121124","121421","141122","141221","112214","112412","122114",
        "122411","142112","142211","241211","221114","413111","241112","134111","111242","121142",
        "121241","114212","124112","124211","411212","421112","421211","212141","214121","412121",
        "111143","111341","131141","114113","114311","411113","411311","113141","114131","311141",
        "114131","311141","411131","211412","211214","211232","2331112"
    );

    $sum = 105;
    $weight = 1;
    $sequence = array(105);

    // Procesamos 48 dígitos en pares (Subset C)
    for ($i = 0; $i < 48; $i += 2) {
        $val = intval(substr($clave_limpia, $i, 2));
        $sequence[] = $val;
        $sum += $val * $weight++;
    }
    
    // Cambiamos al Subset B (100) para el último dígito impar
    $sequence[] = 100;
    $sum += 100 * $weight++;
    
    // Extraemos el último dígito
    $val = ord($clave_limpia[48]) - 32;
    $sequence[] = $val;
    $sum += $val * $weight;

    // Dígito verificador y parada
    $sequence[] = $sum % 103;
    $sequence[] = 106;

    // Unimos los patrones
    $pattern = '';
    foreach ($sequence as $v) {
        $pattern .= $c128[$v];
    }

    // Calculamos el grosor de impresión dinámico
    $total_modules = 0;
    for ($i = 0; $i < strlen($pattern); $i++) {
        $total_modules += intval($pattern[$i]);
    }
    $module_w = $barcode_w / $total_modules;

    // Dibujamos las barras directo en el PDF (sin usar librerías externas)
    $current_x = $barcode_x;
    $is_black = true;
    
    $this->SetDrawColor(0, 0, 0);
    
    for ($i = 0; $i < strlen($pattern); $i++) {
        $w = intval($pattern[$i]) * $module_w;
        if ($is_black) {
            $this->rectangle($current_x, $barcode_y, $w, $barcode_h, "F");
        }
        $current_x += $w;
        $is_black = !$is_black;
    }
} else {
    $this->SetDrawColor(200, 200, 200); 
    $this->rectangle($rect_x + 10, $rect_y_top - 195, $rect_w - 20, 30, "D"); 
    $this->SetTextColor(150, 150, 150);
    $this->TextWrap($rect_x + 10, $rect_y_top - 212, $rect_w - 20, "[ CÓDIGO DE BARRAS PENDIENTE ]", 'C');
    $this->SetTextColor(0, 0, 0); 
    $this->SetDrawColor(0, 0, 0); 
}

// === 2. CUADRO IZQUIERDO Y LOGO EMPRESA ===
$logo_h_max = 60; 
$rect_izq_x = $this->leftMargin; 
$rect_izq_w = $rect_x - $this->leftMargin - 10; 
$logo = company_path() . "/images/" . $this->company['coy_logo'];

if ($this->company['coy_logo'] != '' && file_exists($logo)) {
    $size = getimagesize($logo);
    if ($size) {
        $img_w = $size[0];
        $img_h = $size[1];
        $scale = min(($rect_izq_w - 20) / $img_w, $logo_h_max / $img_h);
        $new_w = $img_w * $scale;
        $new_h = $img_h * $scale;
        $y_pos = $top_y - $logo_h_max + (($logo_h_max - $new_h) / 2);
        $this->AddImage($logo, $this->leftMargin + 10, $y_pos, $new_w, $new_h);
    }
}

$rect_izq_y_top = $top_y - $logo_h_max - 10; 
$rect_izq_h = $rect_h - $logo_h_max - 10; 

$this->rectangle($rect_izq_x, $rect_izq_y_top, $rect_izq_w, $rect_izq_h, "D");

$this->fontSize += 1;
$this->Font('bold');
$this->TextWrap($rect_izq_x + 10, $rect_izq_y_top - 20, $rect_izq_w - 20, $sri_fantasia, 'L');
$this->Font('');
$this->fontSize -= 1;

$this->TextWrap($rect_izq_x + 10, $rect_izq_y_top - 45, 80, "Dir. Matriz:", 'L');
$this->TextWrap($rect_izq_x + 80, $rect_izq_y_top - 45, $rect_izq_w - 90, $this->company['postal_address'], 'L');

$this->TextWrap($rect_izq_x + 10, $rect_izq_y_top - 65, 80, "Dir. Sucursal:", 'L');
$this->TextWrap($rect_izq_x + 80, $rect_izq_y_top - 65, $rect_izq_w - 90, $this->company['domicile'], 'L');

$this->TextWrap($rect_izq_x + 10, $rect_izq_y_top - 100, 170, "OBLIGADO A LLEVAR CONTABILIDAD : ", 'L');
$this->TextWrap($rect_izq_x + 180, $rect_izq_y_top - 100, 30, $texto_obligado, 'L');

// === 3. CUADRO DEL CLIENTE (RAZÓN SOCIAL Y FECHA) ===
$cli_w = $page_width - $this->leftMargin - $this->rightMargin;
$cli_h = 45;
$cli_y_top = $top_y - $rect_h - 10; 

$this->rectangle($this->leftMargin, $cli_y_top, $cli_w, $cli_h, "D");

$this->TextWrap($this->leftMargin + 10, $cli_y_top - 15, 80, "Razón Social:", 'L');
$this->TextWrap($this->leftMargin + 90, $cli_y_top - 15, 250, $this->formData['DebtorName'], 'L');

$this->TextWrap($this->leftMargin + 10, $cli_y_top - 30, 80, "Identificación:", 'L');
$this->TextWrap($this->leftMargin + 90, $cli_y_top - 30, 150, $this->formData['tax_id'], 'L');

$this->TextWrap($this->leftMargin + 350, $cli_y_top - 15, 50, "Fecha:", 'L');
$this->TextWrap($this->leftMargin + 390, $cli_y_top - 15, 100, sql2date($this->formData['document_date']), 'L');

// === 4. ENCABEZADOS Y CUADRÍCULA DE LA TABLA DE ARTÍCULOS ===
$this->row = $cli_y_top - $cli_h - 20; 
$grid_top = $this->row + 12; 

$this->Font('bold');
$count = count($this->headers);
$this->cols[$count] = $page_width - $this->rightMargin;
for ($i = 0; $i < $count; $i++) {
    $this->TextCol($i, $i + 1, $this->headers[$i], -2);
}
$this->Font('');

$this->LineTo($this->leftMargin, $this->row - 5, $page_width - $this->rightMargin, $this->row - 5); 

$grid_bottom = $this->bottomMargin + (15 * $this->lineHeight);

$this->SetDrawColor(0, 0, 0);
$this->SetLineWidth(0.5);

$grid_h = $grid_top - $grid_bottom;
$this->rectangle($this->leftMargin, $grid_top, $page_width - $this->leftMargin - $this->rightMargin, $grid_h, "D");

for ($i = 1; $i < $count; $i++) {
    $this->LineTo($this->cols[$i], $grid_top, $this->cols[$i], $grid_bottom);
}

$temp = $this->row - 15; 
?>