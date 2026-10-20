<?php
require_once __DIR__ . '/../backend/imagen_helper.php';

// Asegurar el prefijo de tablas vacío para entornos con BBDD separadas
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

$stock_id = isset($_GET['id']) ? trim($_GET['id']) : '';

$producto = null;
$lista_precios = [];
$stocks_bodegas = [];

if (!empty($stock_id)) {
    try {
        $db = BaseDatos::obtenerConexion();
        
        // 1. Obtener datos principales del producto, categoría, unidad y tarifa de IVA
        $sql = "SELECT sm.stock_id, sm.description, sm.long_description, sm.mb_flag, sm.units,
                       sc.description as category_description,
                       COALESCE(t.rate, 0.00) as tasa_iva
                FROM " . TB_PREF . "stock_master sm
                LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
                LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
                LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
                WHERE sm.stock_id = :stock_id LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['stock_id' => $stock_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // 2. Obtener precios de venta para todos los tipos de venta (sales_types)
            $sql_precios = "SELECT st.sales_type, COALESCE(p.price, 0) as precio_base
                            FROM " . TB_PREF . "sales_types st
                            LEFT JOIN " . TB_PREF . "prices p ON st.id = p.sales_type_id AND p.stock_id = :stock_id";
            
            $stmt_precios = $db->prepare($sql_precios);
            $stmt_precios->execute(['stock_id' => $stock_id]);
            $lista_precios = $stmt_precios->fetchAll(PDO::FETCH_ASSOC);

            // 3. Obtener stock por bodega usando stock_moves y voided
            $sql_stock = "SELECT l.loc_code, l.location_name,
                                 COALESCE(SUM(CASE WHEN voided.id IS NULL THEN move.qty ELSE 0 END), 0) as quantity
                          FROM " . TB_PREF . "locations l
                          LEFT JOIN " . TB_PREF . "stock_moves move ON l.loc_code = move.loc_code AND move.stock_id = :stock_id
                          LEFT JOIN " . TB_PREF . "voided voided ON voided.type = move.type AND voided.id = move.trans_no
                          WHERE l.inactive = 0
                          GROUP BY l.loc_code, l.location_name";
            
            $stmt_stock = $db->prepare($sql_stock);
            $stmt_stock->execute(['stock_id' => $stock_id]);
            $stocks_bodegas = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (Exception $e) {
        $producto = null;
    }
}


$foto_url = $producto ? obtenerImagenStockUnificada($producto['stock_id']) : null;
$tasa_iva = $producto ? floatval($producto['tasa_iva']) : 0;
$es_kit = $producto ? ($producto['mb_flag'] === 'K' || $producto['mb_flag'] === 'D') : false;
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Detalle de Producto</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA PRINCIPAL -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="index.php?view=catalogo" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Detalle del Producto</h1>
                <p class="text-[10px] text-slate-400 font-bold">Información y Stock</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">
        <?php if (!$producto): ?>
            <div class="text-center py-16 text-slate-400 text-sm font-medium">
                <i data-lucide="package-x" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                No se encontró información para el artículo especificado (ID: <?php echo htmlspecialchars($stock_id); ?>).
            </div>
        <?php else: ?>

            <!-- TARJETA SUPERIOR: FOTOGRAFÍA AMPLIADA -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col items-center justify-center shadow-sm">
                <?php if ($foto_url): ?>
                    <div class="w-44 h-44 rounded-xl bg-contain bg-no-repeat bg-center border border-slate-200 bg-slate-50 shadow-inner" style="background-image: url('<?php echo $foto_url; ?>');"></div>
                <?php else: ?>
                    <div class="w-44 h-44 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center shadow-inner">
                        <i data-lucide="<?php echo $es_kit ? 'boxes' : 'package'; ?>" class="w-16 h-16 text-emerald-600"></i>
                    </div>
                <?php endif; ?>
                <h2 class="text-base font-black text-slate-900 mt-3 text-center leading-tight"><?php echo htmlspecialchars($producto['description']); ?></h2>
                <span class="text-[10px] text-slate-400 font-mono mt-0.5 font-medium">ID: <?php echo htmlspecialchars($producto['stock_id']); ?></span>
            </div>

            <!-- DATOS GENERALES A DETALLE -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Especificaciones Técnicas</h3>
                
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="block text-slate-400 font-bold uppercase text-[9px]">Código del Artículo</span>
                        <span class="text-slate-800 font-mono font-bold"><?php echo htmlspecialchars($producto['stock_id']); ?></span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-bold uppercase text-[9px]">Unidad de Medida</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($producto['units'] ?: 'Unidad'); ?></span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-bold uppercase text-[9px]">Categoría</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($producto['category_description'] ?: 'General'); ?></span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-bold uppercase text-[9px]">Tarifa de IVA</span>
                        <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 inline-block"><?php echo ($tasa_iva > 0) ? 'IVA (' . intval($tasa_iva) . '%)' : 'Sin IVA (0%)'; ?></span>
                    </div>
                </div>
            </div>

            <!-- DIVISIÓN: PRECIOS DE VENTA INCLUYENDO IMPUESTOS -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Precios de Venta (Con Impuestos)</h3>
                
                <?php if (empty($lista_precios)): ?>
                    <p class="text-xs text-slate-400 text-center py-2">No hay tarifas configuradas para este artículo.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <?php foreach ($lista_precios as $lp): 
                            $precio_base = floatval($lp['precio_base']);
                            $precio_con_iva = round($precio_base * (1 + ($tasa_iva / 100)), 2);
                        ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex justify-between items-center shadow-sm">
                                <span class="text-xs font-bold text-slate-700 uppercase"><?php echo htmlspecialchars($lp['sales_type']); ?></span>
                                <div class="text-right">
                                    <span class="text-base font-black text-slate-900">$<?php echo number_format($precio_con_iva, 2); ?></span>
                                    <span class="block text-[8px] text-slate-400 uppercase font-bold">Incl. IVA</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TARJETA: INVENTARIO POR BODEGAS -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i data-lucide="warehouse" class="w-4 h-4 text-emerald-600"></i>
                    Inventario en Bodegas
                </h3>
                
                <?php if (empty($stocks_bodegas)): ?>
                    <p class="text-xs text-slate-400 text-center py-2">No hay registros de inventario en bodegas.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($stocks_bodegas as $b): 
                            $cantidad = floatval($b['quantity']);
                        ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex justify-between items-center shadow-sm">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($b['location_name']); ?></h4>
                                    <span class="text-[9px] text-slate-400 font-mono">Código: <?php echo htmlspecialchars($b['loc_code']); ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black <?php echo $cantidad > 0 ? 'text-emerald-600' : 'text-slate-400'; ?>">
                                        <?php echo number_format($cantidad, 0); ?>
                                    </span>
                                    <span class="block text-[8px] text-slate-400 uppercase font-bold"><?php echo htmlspecialchars($producto['units'] ?: 'Und'); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </main>

    <!-- MENÚ INFERIOR DE 4 OPCIONES (Global) -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-around items-center px-1 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="map" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Ruta</span>
        </button>
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="book-open" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Catálogo</span>
        </button>
        <button onclick="window.location.href='index.php?view=pos'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group relative">
            <div class="bg-emerald-50 p-1.5 rounded-xl border border-emerald-100 shadow-sm">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide text-emerald-600">Vender</span>
        </button>
        <button onclick="window.location.href='index.php?view=metas'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="bar-chart-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Metas</span>
        </button>
    </nav>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
