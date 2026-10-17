<?php
// Asegurar el prefijo de tablas vacío para entornos con BBDD separadas
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

$kits = [];
try {
    $db = BaseDatos::obtenerConexion();
    
    // 1. Obtener los kits únicos desde item_codes[cite: 41]
    $sql_kits = "SELECT ic.item_code as kit_code, ic.description as kit_description, ic.category_id
                 FROM " . TB_PREF . "item_codes ic
                 WHERE ic.is_foreign = 0 AND ic.inactive = 0
                 GROUP BY ic.item_code, ic.description, ic.category_id
                 HAVING COUNT(ic.stock_id) > 1 OR ic.item_code NOT IN (SELECT stock_id FROM " . TB_PREF . "stock_master)
                 ORDER BY ic.description ASC";
    
    $stmt = $db->prepare($sql_kits);
    $stmt->execute();
    $lista_kits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lista_kits as $k) {
        $kit_code = $k['kit_code'];
        
        // 2. Consultar los componentes y calcular el precio con IVA individual por cantidad[cite: 41]
        $sql_comps = "SELECT ic.quantity, 
                             COALESCE(p.price, 0) as precio_base,
                             COALESCE(t.rate, 0.00) as tasa_iva
                      FROM " . TB_PREF . "item_codes ic
                      LEFT JOIN " . TB_PREF . "prices p ON ic.stock_id = p.stock_id AND p.sales_type_id = 1
                      LEFT JOIN " . TB_PREF . "stock_master sm ON ic.stock_id = sm.stock_id
                      LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
                      LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
                      LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
                      WHERE ic.item_code = :kit_code AND ic.is_foreign = 0";
        
        $stmt_comps = $db->prepare($sql_comps);
        $stmt_comps->execute(['kit_code' => $kit_code]);
        $componentes = $stmt_comps->fetchAll(PDO::FETCH_ASSOC);

        $precio_total_kit = 0;
        foreach ($componentes as $comp) {
            $precio_base = floatval($comp['precio_base']);
            $tasa_iva = floatval($comp['tasa_iva']);
            $cantidad = floatval($comp['quantity']);
            
            $precio_con_iva_unitario = $precio_base * (1 + ($tasa_iva / 100));
            $precio_total_kit += ($precio_con_iva_unitario * $cantidad);
        }

        $kits[] = [
            'kit_code' => $kit_code,
            'kit_description' => $k['kit_description'],
            'precio_total' => round($precio_total_kit, 2)
        ];
    }

} catch (Exception $e) {
    $kits = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Kits de Venta</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                <i data-lucide="boxes" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Kits de Productos</h1>
                <p class="text-[10px] text-slate-400 font-bold">Combos y Paquetes Promocionales</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto pb-24 p-4 space-y-3">

        <!-- BARRA DE BÚSQUEDA DINÁMICA -->
        <div class="flex items-center gap-2">         
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="inputBusquedaKits" placeholder="Buscar por código o descripción del kit..." 
                    class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 shadow-sm transition-colors font-medium"
                    onkeyup="filtrarKits()">
            </div>
        </div>

        <div class="space-y-3" id="contenedorKits">
            <?php if (empty($kits)): ?>
                <div class="text-center py-16 text-slate-400 text-sm font-medium">
                    <i data-lucide="box" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                    No se encontraron kits de venta configurados.
                </div>
            <?php else: ?>
                <?php foreach ($kits as $kit): 
                    $texto_busqueda = strtolower($kit['kit_code'] . ' ' . $kit['kit_description']);
                ?>
                    <a href="index.php?view=kit_details&id=<?php echo urlencode($kit['kit_code']); ?>" 
                       class="kit-card block bg-white p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 transition-all shadow-sm hover:shadow-md"
                       data-search="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES); ?>">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center shrink-0 shadow-inner">
                                <i data-lucide="boxes" class="w-8 h-8 text-emerald-600"></i>
                            </div>
                            <div class="flex-1">
                                <span class="text-[9px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded-lg border border-purple-200 uppercase font-black">Kit de Venta</span>
                                <h3 class="text-sm font-bold text-slate-900 mt-1 leading-tight"><?php echo htmlspecialchars($kit['kit_description']); ?></h3>
                                <span class="text-[10px] text-slate-400 font-mono font-medium">Código: <?php echo htmlspecialchars($kit['kit_code']); ?></span>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-slate-900">$<?php echo number_format($kit['precio_total'], 2); ?></span>
                                <span class="block text-[8px] text-slate-400 uppercase font-bold">Incl. IVA (Armado)</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                
                <div id="sinResultadosKits" class="text-center py-12 text-slate-400 text-sm font-medium hidden">
                    <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    No se encontraron kits coincidentes.
                </div>
            <?php endif; ?>
        </div>
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

    <script>
        lucide.createIcons();

        function filtrarKits() {
            const input = document.getElementById('inputBusquedaKits').value.toLowerCase().trim();
            const tarjetas = document.querySelectorAll('.kit-card');
            const sinResultados = document.getElementById('sinResultadosKits');
            let encontrados = 0;

            tarjetas.forEach(card => {
                const textoData = card.getAttribute('data-search');
                if (textoData.includes(input)) {
                    card.style.display = 'block';
                    encontrados++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (encontrados === 0) {
                sinResultados.classList.remove('hidden');
            } else {
                sinResultados.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
