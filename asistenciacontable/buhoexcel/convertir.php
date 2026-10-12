<?php 
header("Content-Type: application/vnd.ms-excel; charset=ISO-8859-1");

$servername = "localhost";
$username = "redpwifi_remoto";
$password = "Polaca2024*";
$dbname = "redpwifi_sacco";

$connection= mysqli_connect ($servername, $username, $password,$dbname);

$id=$_REQUEST['id'];

$sql1 = "select * from upload where id=$id"; 
$result = mysqli_query($connection, $sql1);

if (mysqli_num_rows($result) > 0) {
	// Output data of each row
	while($row = mysqli_fetch_assoc($result)) {
		$field1 = $row["name"];
	}
	} else {
	echo "0 results";
}

// $file = mysqli_fetch_array($result); 
$file = $field1; 

$file_pointer = "upload/".$file; 

// echo 'archivo - '.$file_pointer;

		$contenido = str_replace([
        '<![CDATA[<?xml version="1.0" encoding="UTF-8" standalone="no"?>',		// Apertura de CDATA y documento XML
		'<![CDATA[<?xml version="1.0" encoding="UTF-8"?>', // Apertura de CDATA y documento XML v2
        ']]' // Cierre simple de CDATA
		], '', file_get_contents($file_pointer));
		
		/* $contenido = str_replace([
        '<![CDATA[<?xml version="1.0" encoding="UTF-8"?>', // Apertura de CDATA y documento XML
        ']]' // Cierre simple de CDATA
		], '', file_get_contents($file_pointer)); */
		
        $xml = simplexml_load_string($contenido,'SimpleXMLElement');
		
		$objJsonDocument = json_encode($xml);
		$arrOutput = json_decode($objJsonDocument, TRUE);
	
		echo 'Estado'."\t".$arrOutput['estado']."\n";
		echo 'Numero Autorizacion '.$arrOutput['numeroAutorizacion']."\n";
		echo 'Fecha Autorizacion'."\t".$arrOutput['fechaAutorizacion']."\n";
		echo 'Ambiente SRI'."\t".$arrOutput['ambiente']."\n\n";
		echo 'Razon Social'."\t".$arrOutput['comprobante']['factura']['infoTributaria']['razonSocial']."\n";
		echo 'Nombre Comercial'."\t".$arrOutput['comprobante']['factura']['infoTributaria']['nombreComercial']."\n";
		echo 'RUC '.$arrOutput['comprobante']['factura']['infoTributaria']['ruc']."\n";
		echo '* - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - * - '."\n";	
		echo 'COD PRINC'."\t".'DESCRIPCION '."\t".'CANTIDAD '."\t".'PRECIO Sin '."\t".'TOTAL Sin '."\n";
		
		// if (is_array($arrOutput['comprobante']['factura']['detalles']['detalle']) && empty($conf['context'])) {
			
		// echo sizeof($arrOutput['comprobante']['factura']['detalles']['detalle'], 1)."\n";
		// echo count($arrOutput['comprobante']['factura']['detalles']['detalle'])."\n";
	
		//	echo count($arrOutput['comprobante']['factura']['detalles']['detalle'],1)."\n";
		//	echo count($arrOutput['comprobante']['factura']['detalles']['detalle'])."\n";
			
			$keys = array_keys($arrOutput['comprobante']['factura']['detalles']['detalle']);
			
			if ($keys !== range(0, count($arrOutput['comprobante']['factura']['detalles']['detalle']) - 1)) {
				// Associative array
				// echo 'Asociativa matriz'."\n";
				echo $arrOutput['comprobante']['factura']['detalles']['detalle']['codigoPrincipal']."\t";
				echo $arrOutput['comprobante']['factura']['detalles']['detalle']['descripcion']."\t";
				echo str_replace('.',chr(44),$arrOutput['comprobante']['factura']['detalles']['detalle']['cantidad'])."\t";
				echo str_replace('.',chr(44),$arrOutput['comprobante']['factura']['detalles']['detalle']['precioUnitario'])."\t";
				echo str_replace('.',chr(44),$arrOutput['comprobante']['factura']['detalles']['detalle']['precioTotalSinImpuesto'])."\n";

			} else {
				
				// sequential array
				//echo 'Secuencial matriz'."\n";
				foreach($arrOutput['comprobante']['factura']['detalles']['detalle'] as $campos){
					echo $campos['codigoPrincipal']."\t".$campos['descripcion']."\t".str_replace('.',chr(44),$campos['cantidad'])."\t";
					echo str_replace('.',chr(44),$campos['precioUnitario'])."\t".str_replace('.',chr(44),$campos['precioTotalSinImpuesto'])."\n";
				}
			}			

		

header("Content-disposition: attachment; filename=xmlBuho.xls");

/* echo'<script type="text/javascript">
    alert("Tarea realizada '.$file_pointer.'");
    window.location.href="index.php";
    </script>'; */

// header("Location: index.php"); 
 ?>
