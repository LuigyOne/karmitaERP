<?php
require_once __DIR__ . '/../backend/metricas_data.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Métricas & Ventas Diarias</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA CON TABS -->
    <header class="bg-white px-4 pt-3 pb-0 flex flex-col sticky top-0 z-20 border-b border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <button onclick="window.location.href='index.php?view=ruta'" class="text-slate-600 hover:text-slate-900 transition-colors p-1 -ml-1">
                    <i data-lucide="arrow-left" class="w-6 h-6"></i>
                </button>
                <h1 class="text-sm font-black text-slate-900 uppercase tracking-wide">Ejecución y Control Diario</h1>
            </div>
        </div>

        <!-- NAVEGACIÓN DE TABS -->
        <div class="flex border-t border-slate-200">
            <button onclick="cambiarTab('metricas')" id="btn-tab-metricas" class="flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-emerald-600 border-b-2 border-emerald-600 transition-all flex items-center justify-center gap-2">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Métricas
            </button>
            <button onclick="cambiarTab('preventas')" id="btn-tab-preventas" class="flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-slate-400 border-b-2 border-transparent transition-all flex items-center justify-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4"></i> Ventas del Día (<?php echo count($preventas_del_dia ?? []); ?>)
            </button>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">
        
        <!-- ================= TAB 1: MÉTRICAS ================= -->
        <div id="content-tab-metricas" class="space-y-4">
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
                    <span>Visitas Totales: <strong class="text-slate-900"><?php echo $visitas_realizadas; ?> / <?php echo$total_clientes_zona; ?></strong></span>
                    <span>Clientes Nuevos Hoy: <strong class="text-emerald-600">+<?php echo $clientes_nuevos_hoy; ?></strong></span>
                </div>
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
        </div>

        <!-- ================= TAB 2: VENTAS DEL DÍA ================= -->
        <div id="content-tab-preventas" class="space-y-3 hidden">
            <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block font-bold">Total Facturado Hoy</span>
                    <span class="text-base font-black text-emerald-600">$<?php echo number_format($venta_total_dia, 2); ?> USD</span>
                </div>
                <button onclick="imprimirConsolidadoDia()" class="bg-emerald-500 text-white font-black text-xs px-3.5 py-2.5 rounded-xl hover:bg-emerald-600 transition-colors flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Reporte (32 Col)
                </button>
            </div>

            <?php if (empty($preventas_del_dia)): ?>
                <div class="text-center py-16 text-slate-400 text-sm bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <i data-lucide="receipt-text" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                    No se han registrado ventas en el día de hoy.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($preventas_del_dia as$pv): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2 shadow-sm">
                            <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                                <div>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 font-mono font-bold"><?php echo htmlspecialchars($pv['num_orden']); ?></span>
                                    <h3 class="text-xs font-black text-slate-900 mt-1"><?php echo htmlspecialchars($pv['cliente_nombre']); ?></h3>
                                    <p class="text-[10px] text-slate-500">Sucursal: <?php echo htmlspecialchars($pv['sucursal_nombre']); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-emerald-600">$<?php echo number_format($pv['total_orden'], 2); ?></span>
                                    <span class="block text-[9px] text-slate-400 uppercase font-bold"><?php echo htmlspecialchars($pv['forma_pago']); ?></span>
                                </div>
                            </div>
                            <div class="text-[11px] text-slate-600 space-y-1">
                                <p class="font-bold text-slate-500 text-[10px] uppercase">Ítems solicitados:</p>
                                <ul class="pl-2 space-y-0.5">
                                    <?php foreach ($pv['items'] as$it): ?>
                                        <li class="flex justify-between">
                                            <span>• <?php echo intval($it['cantidad']); ?>x <?php echo htmlspecialchars($it['item_nombre']); ?></span>
                                            <span class="font-mono text-slate-500">$<?php echo number_format($it['precio_sugerido'] *$it['cantidad'], 2); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

        const preventasDelDiaJson = <?php echo json_encode($preventas_del_dia ?? []); ?>;
        const totalVentaDia = "<?php echo number_format($venta_total_dia, 2); ?>";
        const vendedorNombre = "<?php echo htmlspecialchars($nombre_vendedor); ?>";

        function cambiarTab(tab) {
            const btnMetricas = document.getElementById('btn-tab-metricas');
            const btnPreventas = document.getElementById('btn-tab-preventas');
            const contentMetricas = document.getElementById('content-tab-metricas');
            const contentPreventas = document.getElementById('content-tab-preventas');

            if (tab === 'metricas') {
                btnMetricas.className = "flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-emerald-600 border-b-2 border-emerald-600 transition-all flex items-center justify-center gap-2";
                btnPreventas.className = "flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-slate-400 border-b-2 border-transparent transition-all flex items-center justify-center gap-2";
                contentMetricas.classList.remove('hidden');
                contentPreventas.classList.add('hidden');
            } else {
                btnPreventas.className = "flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-emerald-600 border-b-2 border-emerald-600 transition-all flex items-center justify-center gap-2";
                btnMetricas.className = "flex-1 py-2.5 text-xs font-black uppercase tracking-wider text-slate-400 border-b-2 border-transparent transition-all flex items-center justify-center gap-2";
                contentPreventas.classList.remove('hidden');
                contentMetricas.classList.add('hidden');
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function imprimirConsolidadoDia() {
            if (!preventasDelDiaJson || preventasDelDiaJson.length === 0) {
                alert("No hay ventas registradas hoy para imprimir.");
                return;
            }

            let ticket = "";
            ticket += "     SANCHEZ VERA DISTRIBUCION     \n";
            ticket += "       RUC: 1759318114001          \n";
            ticket += "--------------------------------\n";
            ticket += "CONSOLIDADO VENTAS DEL DIA (VAN)\n";
            ticket += "VENDEDOR: " + vendedorNombre.substring(0, 21).padEnd(21, ' ') + "\n";
            ticket += "FECHA:    " + new Date().toLocaleDateString().padEnd(24, ' ') + "\n";
            ticket += "--------------------------------\n";

            preventasDelDiaJson.forEach((pv, idx) => {
                ticket += `(${idx + 1}) ${pv.num_orden} - $${Number(pv.total_orden).toFixed(2)}\n`;
                ticket += `CLIE: ${pv.cliente_nombre.substring(0, 25).padEnd(25, ' ')}\n`;
                pv.items.forEach(it => {
                    let qty = String(it.cantidad).padEnd(3, ' ');
                    let desc = it.item_nombre.substring(0, 18).padEnd(18, ' ');
                    let sub = Number(it.precio_sugerido * it.cantidad).toFixed(2).padStart(7, ' ');
                    ticket += `  ${qty}x ${desc} ${sub}\n`;
                });
                ticket += "--------------------------------\n";
            });

            ticket += "TOTAL GENERAL:".padEnd(20, ' ') + ("$" + totalVentaDia).padStart(12, ' ') + "\n";
            ticket += "--------------------------------\n";
            ticket += "     REPORTE DIARIO DE RUTA     \n";
            ticket += "      ¡GRACIAS POR SU GESTION!  \n\n\n\n";

            let textoCodificado = encodeURIComponent(ticket);

            let defaultPackage = "ru.a402d.rawbtprinter";
            let customPackage = localStorage.getItem('pos_printer_package');
            let targetPackage = (customPackage && customPackage !== "") ? customPackage : defaultPackage;

            window.location.href = `intent:/${textoCodificado}#Intent;scheme=rawbt;package=${targetPackage};end;`;
        }
    </script>
</body>
</html>