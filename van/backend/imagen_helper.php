<?php
// pos/backend/imagen_helper.php

function obtenerImagenStockUnificada($stock_id) {
    // Lista completa de extensiones posibles (soporta mayúsculas y minúsculas para servidores Linux/Hosting)
    $exts = ['jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF', 'jpeg', 'JPEG'];
    
    foreach ($exts as $ext) {
        $nombre_archivo = strtolower($stock_id) . '.' . $ext;
        
        // __DIR__ se encuentra en pos/backend/
        // Subimos dos niveles para salir a la raíz del proyecto y buscar la ruta real del ERP
        $ruta_fisica = __DIR__ . '/../../company/1/images/stock_items/' . $nombre_archivo;
        
        if (file_exists($ruta_fisica)) {
            // Retornamos la ruta relativa correcta hacia el navegador
            return '../company/1/images/stock_items/' . $nombre_archivo;
        }
    }
    
    return null;
}
?>