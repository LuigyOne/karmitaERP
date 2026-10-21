<?php
// pos/backend/imagen_helper.php

function obtenerImagenStockUnificada($stock_id) {
    $directorio_fisico = __DIR__ . '/../../company/1/images/stock_items/';
    
    if (!is_dir($directorio_fisico)) {
        return null;
    }
    
    // Escanear el directorio para hacer una búsqueda case-insensitive real (compatible con Linux/Hosting)
    $archivos = scandir($directorio_fisico);
    if ($archivos === false) {
        return null;
    }
    
    $stock_id_lower = strtolower(trim($stock_id));
    
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') continue;
        
        // Obtener el nombre del archivo sin la extensión y pasarlo a minúsculas para comparar
        $nombre_sin_ext = strtolower(pathinfo($archivo, PATHINFO_FILENAME));
        
        if ($nombre_sin_ext === $stock_id_lower) {
            // Retornamos la ruta relativa y el nombre exacto del archivo tal como está en el servidor
            return '../company/1/images/stock_items/' . $archivo;
        }
    }
    
    return null;
}
?>