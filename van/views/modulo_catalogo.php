<?php
require_once __DIR__ . '/../backend/imagen_helper.php';

// Asegurar el prefijo de tablas vacío para entornos con BBDD separadas
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

$productos = [];
try {
    $db = BaseDatos::obtenerConexion();
    
    // Consulta SQL cargando todos los productos activos para filtrado instantáneo en cliente
    $sql = "SELECT sm.stock_id, sm.description, sm.long_description, sm.mb_flag, 
                   COALESCE(p.price, 0) as precio_base,
                   COALESCE(t.rate, 0.00) as tasa_iva
            FROM " . TB_PREF . "stock_master sm
            LEFT JOIN " . TB_PREF . "prices p ON sm.stock_id = p.stock_id AND p.sales_type_id = 1
            LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
            LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
            LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
            WHERE sm.inactive = 0
            ORDER BY sm.description ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $productos = [];
}

?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Catálogo</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA PRINCIPAL CON BOTÓN DE BODEGA MÓVIL (TRUCK) -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Catálogo Digital</h1>
                <p class="text-[10px] text-slate-400 font-bold">Promos y Productos</p>
            </div>
        </div>

        <!-- Enlace al nuevo módulo de bodega móvil -->
        <a href="index.php?view=bodega_movil" class="bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 px-3 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-sm group" title="Ver Stock en Bodega Móvil">
            <i data-lucide="truck" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-xs font-black uppercase tracking-wide hidden sm:inline">Bodega Móvil</span>
        </a>
    </header>

    <main class="flex-1 overflow-y-auto pb-24">

        <!-- SECCIÓN 1: CARRUSEL DE PROMOCIONES Y FIDELIZACIÓN -->
        <div class="pt-4 pb-2">
            <div class="px-4 mb-2 flex justify-between items-center">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Campañas Activas</h2>
                <span class="text-[9px] bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded font-black uppercase animate-pulse">¡Nuevas!</span>
            </div>
            
            <div class="flex overflow-x-auto gap-4 px-4 pb-4 snap-x snap-mandatory hide-scrollbar">
                <div class="snap-center shrink-0 w-[85%] sm:w-72 bg-gradient-to-br from-emerald-600 to-slate-900 rounded-3xl border border-emerald-500/30 p-4 shadow-lg relative overflow-hidden text-white">
                    <i data-lucide="gift" class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10"></i>
                    <div class="relative z-10">
                        <span class="text-[10px] font-black bg-white/20 text-white px-2.5 py-1 rounded-lg uppercase backdrop-blur-sm border border-white/10">Fidelización</span>
                        <h3 class="text-lg font-black text-white mt-2 leading-tight">Plan Socio Tienda</h3>
                        <p class="text-xs text-emerald-100 mt-1 font-medium">Acumula 500 pts y canjea un exhibidor premium.</p>
                    </div>
                </div>
                <div class="snap-center shrink-0 w-[85%] sm:w-72 bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl border border-slate-700 p-4 shadow-lg relative overflow-hidden text-white">
                    <i data-lucide="tag" class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10"></i>
                    <div class="relative z-10">
                        <span class="text-[10px] font-black bg-emerald-500 text-white px-2.5 py-1 rounded-lg uppercase shadow-sm">Descuento 15%</span>
                        <h3 class="text-lg font-black text-white mt-2 leading-tight">Combo Parrillero</h3>
                        <p class="text-xs text-slate-300 mt-1 font-medium">Llevando 3 cajas de Chorizo + 1 Pack Bebidas.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: BUSCADOR DINÁMICO Y BOTÓN DE KITS -->
        <div class="px-4 mb-4 sticky top-[53px] z-10 bg-slate-50/95 backdrop-blur-sm py-2 border-b border-slate-200/60">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="inputBusqueda" placeholder="Buscar por descripción o código ID..." 
                           class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 shadow-sm transition-colors font-medium"
                           onkeyup="filtrarProductos()">
                </div>
                <a href="index.php?view=kits" class="bg-white border border-slate-200 hover:border-emerald-500 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 transition-colors shrink-0 shadow-sm group" title="Ver Kits de Venta">
                    <i data-lucide="boxes" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs font-black uppercase tracking-wide hidden sm:inline">Kits</span>
                </a>
            </div>
        </div>

        <!-- SECCIÓN 3: LISTADO DINÁMICO DE PRODUCTOS -->
        <div class="px-4 space-y-3" id="contenedorProductos">
            <?php if (empty($productos)): ?>
                <div class="text-center py-12 text-slate-400 text-sm font-medium">
                    <i data-lucide="package-open" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    No se encontraron artículos en el catálogo.
                </div>
            <?php else: ?>
                <?php foreach ($productos as $index => $item): 
                    $es_kit = ($item['mb_flag'] === 'K' || $item['mb_flag'] === 'D');
                    $foto_url = obtenerImagenStockUnificada($item['stock_id']);
                    
                    $precio_base = floatval($item['precio_base']);
                    $tasa_iva = floatval($item['tasa_iva']);
                    $precio_con_iva = round($precio_base * (1 + ($tasa_iva / 100)), 2);

                    $estrellas = 4 + (($index % 2)); 

                    $texto_busqueda = strtolower($item['stock_id'] . ' ' . $item['description'] . ' ' . $item['long_description']);
                ?>
                    <div class="producto-card bg-white p-3 rounded-2xl border border-slate-200 flex gap-3 items-center shadow-sm hover:border-emerald-500/50 transition-all" 
                         data-search="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES); ?>">
                        
                        <!-- CAJA 1: FOTOGRAFÍA -->
                        <a href="index.php?view=details&id=<?php echo urlencode($item['stock_id']); ?>" class="shrink-0 group" title="Ver detalles y stock completo">
                            <?php if ($foto_url): ?>
                                <div class="w-24 h-24 rounded-xl bg-contain bg-no-repeat bg-center border border-slate-200 bg-slate-50 group-hover:border-emerald-400 transition-colors relative shadow-inner" style="background-image: url('<?php echo $foto_url; ?>');">
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center backdrop-blur-[2px]">
                                        <i data-lucide="zoom-in" class="w-6 h-6 text-white"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="w-24 h-24 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-center group-hover:border-emerald-400 transition-colors relative shrink-0 shadow-inner">
                                    <i data-lucide="<?php echo $es_kit ? 'boxes' : 'package'; ?>" class="w-8 h-8 text-emerald-600"></i>
                                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center backdrop-blur-[2px]">
                                        <i data-lucide="zoom-in" class="w-6 h-6 text-white"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- CAJA 2: DETALLES DEL PRODUCTO -->
                        <div class="flex-1 flex flex-col justify-between py-0.5">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 leading-tight"><?php echo htmlspecialchars($item['description']); ?></h4>
                                
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] text-slate-400 font-mono font-medium">ID: <?php echo htmlspecialchars($item['stock_id']); ?></span>
                                    <?php if ($es_kit): ?>
                                        <span class="text-[8px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded-lg border border-purple-200 uppercase font-black">Kit</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex justify-between items-end mt-2.5">
                                <div>
                                    <span class="text-base font-black text-slate-900">$<?php echo number_format($precio_con_iva, 2); ?></span>
                                    <span class="block text-[9px] text-slate-400 font-bold uppercase">
                                        <?php echo ($tasa_iva > 0) ? 'Incluye IVA (' . intval($tasa_iva) . '%)' : 'No Incluye IVA'; ?>
                                    </span>
                                </div>

                                <div class="flex items-center gap-0.5 text-amber-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i data-lucide="star" class="w-3 h-3 <?php echo ($i <= $estrellas) ? 'fill-amber-400 text-amber-400' : 'text-slate-200'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

                <div id="sinResultados" class="text-center py-12 text-slate-400 text-sm font-medium hidden">
                    <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                    No se encontraron productos coincidentes.
                </div>
            <?php endif; ?>
        </div>
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

        function filtrarProductos() {
            const input = document.getElementById('inputBusqueda').value.toLowerCase().trim();
            const tarjetas = document.querySelectorAll('.producto-card');
            const sinResultados = document.getElementById('sinResultados');
            let encontrados = 0;

            tarjetas.forEach(card => {
                const textoData = card.getAttribute('data-search');
                if (textoData.includes(input)) {
                    card.style.display = 'flex';
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
