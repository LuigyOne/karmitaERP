<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/conexion.php';

$area_actual = $_SESSION['area_actual'] ?? 1;
$vendedor_id = $_SESSION['salesman_code'] ?? 0;
$nombre_vendedor = $_SESSION['salesman_name'] ?? 'Vendedor';
$hoy = date('Y-m-d'); // Día de operación actual

// Inicializar variables por seguridad
$preventas_del_dia = [];
$total_clientes_zona = 0;
$visitas_realizadas = 0;
$clientes_nuevos_hoy = 0;
$clientes_con_compra = 0;
$clientes_sin_compra = 0;
$progreso_visitas = 0;
$venta_total_dia = 0.00;
$drop_size = 0.00;

try {
    $db = BaseDatos::obtenerConexion();

    // 1. Total de clientes programados en la zona
    $stmt_cli = $db->prepare("SELECT COUNT(*) as total FROM cust_branch WHERE area = :area AND inactive = 0");
    $stmt_cli->execute(['area' => $area_actual]);
    $total_clientes_zona = intval($stmt_cli->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 2. Visitas totales realizadas hoy (según auditoría VS)
    $stmt_vis = $db->prepare("SELECT COUNT(DISTINCT id_cliente) as visitados FROM pwa_auditar WHERE ruta_codigo = :area AND DATE(fecha_hora_creacion) = :hoy AND accion_codigo = 'VS'");
    $stmt_vis->execute(['area' => $area_actual, 'hoy' => $hoy]);
    $visitas_realizadas = intval($stmt_vis->fetch(PDO::FETCH_ASSOC)['visitados'] ?? 0);

    // 3. Clientes nuevos registrados hoy (Acción NC)
    $stmt_nc = $db->prepare("SELECT COUNT(*) as nuevos FROM pwa_auditar WHERE ruta_codigo = :area AND DATE(fecha_hora_creacion) = :hoy AND accion_codigo = 'NC'");
    $stmt_nc->execute(['area' => $area_actual, 'hoy' => $hoy]);
    $clientes_nuevos_hoy = intval($stmt_nc->fetch(PDO::FETCH_ASSOC)['nuevos'] ?? 0);

    // 4. Clientes con compra hoy (Ventas/Preventas generadas hoy)
    $stmt_compra = $db->prepare("SELECT COUNT(DISTINCT cod_clien) as con_compra, COALESCE(SUM(det.subtotal), 0) as total_venta FROM pwa_preventas_cabecera c JOIN (SELECT id_orden, SUM(cantidad * precio_sugerido) as subtotal FROM pwa_preventas_detalle GROUP BY id_orden) det ON c.id_orden = det.id_orden WHERE c.fec_reg_ord = :hoy AND c.vendedor = :vend");
    $stmt_compra->execute(['hoy' => $hoy, 'vend' => $vendedor_id]);
    $res_compra = $stmt_compra->fetch(PDO::FETCH_ASSOC);
    $clientes_con_compra = intval($res_compra['con_compra'] ?? 0);
    $venta_total_dia = floatval($res_compra['total_venta'] ?? 0.00);

    // Clientes sin compra
    $clientes_sin_compra = max(0, $visitas_realizadas - $clientes_con_compra);

    // Porcentaje de progreso de visitas
    $progreso_visitas = ($total_clientes_zona > 0) ? round(($visitas_realizadas / $total_clientes_zona) * 100) : 0;

    // Drop Size Promedio
    $drop_size = ($clientes_con_compra > 0) ? ($venta_total_dia / $clientes_con_compra) : 0.00;

    // 5. OBTENER LISTADO DE VENTAS DEL DÍA PARA EL TAB 2
    $sql_preventas = "SELECT c.id_orden, c.num_orden, c.fec_reg_ord, c.forma_pago, c.notas, 
                             dm.name as cliente_nombre, cb.branch_ref as sucursal_nombre,
                             COALESCE(SUM(d.cantidad * d.precio_sugerido), 0) as total_orden
                      FROM pwa_preventas_cabecera c
                      JOIN debtors_master dm ON c.cod_clien = dm.debtor_no
                      JOIN cust_branch cb ON c.cod_sucur = cb.branch_code
                      LEFT JOIN pwa_preventas_detalle d ON c.id_orden = d.id_orden
                      WHERE c.fec_reg_ord = :hoy AND c.vendedor = :vend
                      GROUP BY c.id_orden, c.num_orden, c.fec_reg_ord, c.forma_pago, c.notas, dm.name, cb.branch_ref
                      ORDER BY c.id_orden DESC";
    
    $stmt_p = $db->prepare($sql_preventas);
    $stmt_p->execute(['hoy' => $hoy, 'vend' => $vendedor_id]);
    $preventas_del_dia = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // Obtener detalles de ítems para el ticket térmico
    foreach ($preventas_del_dia as &$pv) {
        $stmt_det = $db->prepare("SELECT d.cantidad, d.precio_sugerido, sm.description as item_nombre 
                                  FROM pwa_preventas_detalle d 
                                  JOIN stock_master sm ON d.cod_item = sm.stock_id 
                                  WHERE d.id_orden = :id_orden");
        $stmt_det->execute(['id_orden' => $pv['id_orden']]);
        $pv['items'] = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($pv);

} catch (Exception $e) {
    $preventas_del_dia = [];
}