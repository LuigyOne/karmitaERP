<?php
// Asegurar el prefijo de tablas vacío para entornos con BBDD separadas
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

$kit_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$kit_info = null;
$componentes = [];
$precio_total_kit = 0;

if (!empty($kit_id)) {
    try {
        $db = BaseDatos::obtenerConexion();
        
        // 1. Obtener la descripción general del kit desde item_codes
        $sql_kit = "SELECT ic.item_code, ic.description as kit_description, ic.category_id
                    FROM " . TB_PREF . "item_codes ic
                    WHERE ic.item_code = :kit_id AND ic.is_foreign = 0 LIMIT 1";
        $stmt = $db->prepare($sql_kit);
        $stmt->execute(['kit_id' => $kit_id]);
        $kit_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($kit_info) {
            // 2. Obtener componentes con precios individuales y tasas de IVA
            $sql_comp = "SELECT ic.stock_id, sm.description as comp_name, ic.quantity, sm.units,
                                COALESCE(p.price, 0) as precio_base,
                                COALESCE(t.rate, 0.00) as tasa_iva
                         FROM " . TB_PREF . "item_codes ic
                         INNER JOIN " . TB_PREF . "stock_master sm ON ic.stock_id = sm.stock_id
                         LEFT JOIN " . TB_PREF . "prices p ON sm.stock_id = p.stock_id AND p.sales_type_id = 1
                         LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
                         LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
                         LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
                         WHERE ic.item_code = :kit_id AND ic.is_foreign = 0";
            
            $stmt_comp = $db->prepare($sql_comp);
            $stmt_comp->execute(['kit_id' => $kit_id]);
            $raw_components = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);

            foreach ($raw_components as $comp) {
                $precio_base = floatval($comp['precio_base']);
                $tasa_iva = floatval($comp['tasa_iva']);
                $cantidad = floatval($comp['quantity']);
                
                $precio_con_iva = round($precio_base * (1 + ($tasa_iva / 100)), 2);
                $subtotal_componente = round($precio_con_iva * $cantidad, 2);
                
                $precio_total_kit += $subtotal_componente;

                $componentes[] = [
                    'stock_id' => $comp['stock_id'],
                    'comp_name' => $comp['comp_name'],
                    'quantity' => $cantidad,
                    'units' => $comp['units'],
                    'precio_con_iva' => $precio_con_iva,
                    'subtotal' => $subtotal_componente,
                    'tasa_iva' => intval($tasa_iva)
                ];
            }
        }

    } catch (Exception $e) {
        $kit_info = null;
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Detalle de Kit</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="index.php?view=kits" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Composición del Kit</h1>
                <p class="text-[10px] text-slate-400 font-bold">Detalle y Artículos</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">
        <?php if (!$kit_info): ?>
            <div class="text-center py-16 text-slate-400 text-sm font-medium">
                <i data-lucide="package-x" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                No se encontró información para el kit especificado.
            </div>
        <?php else: ?>

            <!-- TARJETA CABECERA DEL KIT CON PRECIO TOTAL ARMADO -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-col items-center justify-center shadow-sm">
                <div class="w-24 h-24 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center shadow-inner">
                    <i data-lucide="boxes" class="w-12 h-12 text-emerald-600"></i>
                </div>
                <h2 class="text-base font-black text-slate-900 mt-3 text-center leading-tight"><?php echo htmlspecialchars($kit_info['kit_description']); ?></h2>
                <span class="text-[10px] text-slate-400 font-mono mt-0.5 font-medium">Código Kit: <?php echo htmlspecialchars($kit_info['item_code']); ?></span>
                <div class="mt-3 text-center">
                    <span class="text-xl font-black text-slate-900">$<?php echo number_format($precio_total_kit, 2); ?></span>
                    <span class="block text-[8px] text-slate-400 uppercase font-bold">Precio Total Armado (Incl. IVA)</span>
                </div>
            </div>

            <!-- LISTADO DE COMPONENTES CON PRECIOS Y SUBTOTALES -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                    <i data-lucide="list-tree" class="w-4 h-4 text-emerald-600"></i>
                    Artículos que Conforman el Kit
                </h3>
                
                <?php if (empty($componentes)): ?>
                    <p class="text-xs text-slate-400 text-center py-4">Este kit no tiene componentes asociados registrados.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($componentes as $comp): ?>
                            <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl flex justify-between items-center shadow-sm">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($comp['comp_name']); ?></h4>
                                    <span class="text-[9px] text-slate-400 font-mono font-medium">Código: <?php echo htmlspecialchars($comp['stock_id']); ?></span>
                                    <span class="block text-[9px] text-emerald-600 font-semibold mt-0.5">
                                        Cant: <?php echo number_format($comp['quantity'], 0); ?> <?php echo htmlspecialchars($comp['units'] ?: 'Unid'); ?> × $<?php echo number_format($comp['precio_con_iva'], 2); ?> c/u
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-slate-900">
                                        $<?php echo number_format($comp['subtotal'], 2); ?>
                                    </span>
                                    <span class="block text-[8px] text-slate-400 uppercase font-bold">Subtotal</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </main>

    <!-- Menú inferior global -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-around items-center px-1 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="map" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Ruta</span>
        </button>
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Catálogo</span>
        </button>
        <button onclick="window.location.href='index.php?view=kits'" class="flex flex-col items-center justify-center text-purple-600 flex-1 py-1 transition-colors">
            <i data-lucide="boxes" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Kits</span>
        </button>
        <button onclick="window.location.href='index.php?view=pos'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Vender</span>
        </button>
    </nav>

    <script>lucide.createIcons();</script>
</body>
</html>
