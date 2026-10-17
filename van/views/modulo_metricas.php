<?php
require_once __DIR__ . '/../backend/metricas_data.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Métricas Diarias</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA -->
    <header class="bg-white px-4 py-3 flex items-center justify-between sticky top-0 z-20 border-b border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <button onclick="window.location.href='index.php?view=ruta'" class="text-slate-600 hover:text-slate-900 transition-colors p-1 -ml-1">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </button>
            <h1 class="text-sm font-black text-slate-900 uppercase tracking-wide">Métricas de Ejecución Diaria</h1>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">
        
        <!-- TARJETA 1: MI JORNADA EN RUTA -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-sm">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <span class="text-xs font-black text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="compass" class="w-4 h-4"></i> Mi Jornada en Ruta
                </span>
                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-100 shadow-sm">
                    Progreso: <?php echo $progreso_visitas; ?>%
                </span>
            </div>
            <div class="text-xs text-slate-600 font-medium flex justify-between">
                <span>Visitas Totales: <strong class="text-slate-900"><?php echo $visitas_realizadas; ?> / <?php echo $total_clientes_zona; ?></strong></span>
                <span>Clientes Nuevos Hoy: <strong class="text-emerald-600">+<?php echo $clientes_nuevos_hoy; ?></strong></span>
            </div>
            <!-- Barra de progreso visual -->
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200 shadow-inner">
                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?php echo $progreso_visitas; ?>%;"></div>
            </div>
        </div>

        <!-- TARJETA 2: RESUMEN DE VISITAS (EFECTIVIDAD) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-sm">
            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">Resumen de Visitas (Efectividad)</span>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center shadow-sm">
                    <span class="text-[10px] text-slate-400 uppercase block font-bold">🛒 Con Compra</span>
                    <span class="text-lg font-black text-emerald-600"><?php echo $clientes_con_compra; ?></span>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center shadow-sm">
                    <span class="text-[10px] text-slate-400 uppercase block font-bold">❌ Sin Compra</span>
                    <span class="text-lg font-black text-red-600"><?php echo $clientes_sin_compra; ?></span>
                </div>
            </div>
            <div class="text-[11px] text-slate-500 pt-1">
                Top Motivo No Venta Hoy: <span class="text-slate-800 font-medium italic">"Stock lleno de competencia"</span>
            </div>
        </div>

        <!-- TARJETA 3: AVANCE DE METAS COMERCIALES -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 shadow-sm">
            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">Avance de Metas Comerciales</span>
            
            <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-slate-600">TÉS (Foco)</span>
                    <span class="text-emerald-700">$120 / $200 (60%)</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200 shadow-inner">
                    <div class="bg-emerald-500 h-full w-[60%]"></div>
                </div>
            </div>

            <div class="space-y-2 pt-1">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-slate-600">AGUAS (Gancho)</span>
                    <span class="text-emerald-700">$150 / $100 (150%)</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden border border-slate-200 shadow-inner">
                    <div class="bg-emerald-500 h-full w-full"></div>
                </div>
            </div>
        </div>

        <!-- TARJETA 4: INDICADORES DE CRECIMIENTO & CONTROL -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2.5 shadow-sm">
            <span class="text-xs font-black text-slate-700 uppercase tracking-wider block">Indicadores de Crecimiento & Control</span>
            <ul class="text-xs text-slate-600 space-y-2">
                <li class="flex justify-between items-center bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="font-medium">• Tasa de Reclutamiento:</span>
                    <strong class="text-emerald-700 font-bold">18.1% de la venta diaria</strong>
                </li>
                <li class="flex justify-between items-center bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="font-medium">• Drop Size Promedio:</span>
                    <strong class="text-slate-900 font-bold">$<?php echo number_format($drop_size, 2); ?> USD</strong>
                </li>
                <li class="flex justify-between items-center bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-200 shadow-sm">
                    <span class="font-medium">• Margen Ponderado Hoy:</span>
                    <strong class="text-emerald-700 font-bold">13.8% (Objetivo: >15%)</strong>
                </li>
            </ul>
        </div>

    </main>

    <!-- MENÚ INFERIOR GLOBAL -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-around items-center px-1 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="map" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Ruta</span>
        </button>
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="book-open" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Catálogo</span>
        </button>
        <button onclick="window.location.href='index.php?view=pos'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group relative">
            <div class="bg-emerald-50 p-1.5 rounded-xl border border-emerald-100 shadow-sm">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide text-emerald-600">Vender</span>
        </button>
        <button onclick="window.location.href='index.php?view=metas'" class="flex flex-col items-center justify-center text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="bar-chart-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Metas</span>
        </button>
    </nav>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
