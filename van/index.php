<?php
// Incluir el validador central de sesión y variables globales
require_once 'backend/sesion.php';

// SISTEMA DE RUTEO (Front Controller)
$vista = $_GET['view'] ?? 'ruta'; 

// Inyecta el archivo correspondiente de forma invisible
switch ($vista) {
    case 'logout':
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    case 'pos':
        require_once 'views/modulo_pos.php';
        break;
    case 'catalogo': 
        require_once 'views/modulo_catalogo.php';
        break;            
    case 'ficha':
        require_once 'views/modulo_ficha_cliente.php';
        break;
    case 'caja':
        require_once 'views/modulo_caja_chica.php';
        break;
    case 'metas': 
        require_once 'views/modulo_metricas.php';
        break;  
    case 'details': 
        require_once 'views/modulo_details.php';
        break;            
    case 'kits': 
        require_once 'views/modulo_kits.php';
        break;            
    case 'kit_details': 
        require_once 'views/modulo_kit_details.php';
        break;       
    case 'bodega_movil': 
        require_once 'views/modulo_bodega_movil.php';
        break;               
    case 'nuevo_cliente':
        include 'views/modulo_nuevo_cliente.php';
        break;             
    case 'ruta':
    default:
        require_once 'views/modulo_ruta.php';
        break;
}
exit();
?>