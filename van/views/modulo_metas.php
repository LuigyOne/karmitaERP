<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>POS SVDist - Metas y KPIs</title>

    <!-- Favicon para la pestaña del navegador -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <!-- Icono para dispositivos móviles / PWA (Pantalla de inicio) -->
    <link rel="apple-touch-icon" href="assets/img/favicon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-[#111827] text-[#F3F4F6] font-sans antialiased select-none flex flex-col">

    <!-- CABECERA PRINCIPAL -->
    <header class="bg-[#1F2937] border-b border-[#374151] px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-cyan-900/30 text-cyan-400 flex items-center justify-center border border-cyan-800/50">
                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-sm font-black text-white leading-tight uppercase tracking-wider">Mis Metas</h1>
                <p class="text-[10px] text-gray-400 font-bold">Septiembre 2026</p>
            </div>
        </div>
        <button class="text-cyan-400 hover:text-cyan-300 transition-colors">
            <i data-lucide="calendar-days" class="w-5 h-5"></i>
        </button>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">

        <!-- 1. CUOTA DE VENTA (El KPI Principal) -->
        <div class="bg-[#1F2937] p-4 rounded-2xl border border-[#374151] shadow-lg relative overflow-hidden">
            <!-- Fondo decorativo -->
            <div class="absolute -right-6 -top-6 text-[#111827] opacity-50">
                <i data-lucide="target" class="w-32 h-32"></i>
            </div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Progreso de Cuota</span>
                    <span class="text-xs font-black text-cyan-400 bg-cyan-950/50 px-2 py-0.5 rounded border border-cyan-900/50">68%</span>
                </div>
                
                <div class="flex items-baseline gap-1 mb-3">
                    <span class="text-3xl font-black text-white">$3,400</span>
                    <span class="text-xs font-bold text-gray-500">/ $5,000</span>
                </div>

                <!-- Barra de progreso -->
                <div class="w-full bg-[#111827] rounded-full h-3 mb-1 border border-[#374151] overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-600 to-cyan-400 h-3 rounded-full" style="width: 68%"></div>
                </div>
                <p class="text-[10px] text-gray-400 text-right font-medium">Faltan $1,600 para la meta</p>
            </div>
        </div>

        <!-- 2. COMISIÓN PROYECTADA (El motivador) -->
        <div class="bg-gradient-to-br from-green-900/40 to-[#1F2937] p-4 rounded-2xl border border-green-900/30 shadow-lg flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-green-400 uppercase tracking-wider block mb-1">Comisión Proyectada</span>
                <span class="text-2xl font-black text-white">$170.00</span>
            </div>
            <div class="w-12 h-12 bg-green-950/50 rounded-full flex items-center justify-center border border-green-800/50 text-green-400">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 3. GRID DE EFECTIVIDAD (Métricas operativas) -->
        <div class="grid grid-cols-2 gap-3">
            
            <!-- Cobertura de Ruta -->
            <div class="bg-[#1F2937]/60 p-3 rounded-xl border border-[#374151]">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-purple-400"></i>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Cobertura</span>
                </div>
                <div class="flex items-end justify-between">
                    <span class="text-xl font-black text-white">12<span class="text-xs text-gray-500">/18</span></span>
                    <span class="text-[10px] font-bold text-purple-400">66%</span>
                </div>
                <!-- Mini barra -->
                <div class="w-full bg-[#111827] rounded-full h-1.5 mt-2">
                    <div class="bg-purple-400 h-1.5 rounded-full" style="width: 66%"></div>
                </div>
            </div>

            <!-- Efectividad de Compra (Drop Size / Hit Rate) -->
            <div class="bg-[#1F2937]/60 p-3 rounded-xl border border-[#374151]">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-yellow-400"></i>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Efectividad</span>
                </div>
                <div class="flex items-end justify-between">
                    <span class="text-xl font-black text-white">9<span class="text-xs text-gray-500">/12</span></span>
                    <span class="text-[10px] font-bold text-yellow-400">75%</span>
                </div>
                <!-- Mini barra -->
                <div class="w-full bg-[#111827] rounded-full h-1.5 mt-2">
                    <div class="bg-yellow-400 h-1.5 rounded-full" style="width: 75%"></div>
                </div>
            </div>

            <!-- Ticket Promedio -->
            <div class="bg-[#1F2937]/60 p-3 rounded-xl border border-[#374151]">
                <div class="flex items-center gap-2 mb-1">
                    <i data-lucide="receipt" class="w-4 h-4 text-blue-400"></i>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Ticket Prom.</span>
                </div>
                <span class="text-lg font-black text-white">$45.20</span>
            </div>

            <!-- Devoluciones / Rechazos -->
            <div class="bg-[#1F2937]/60 p-3 rounded-xl border border-[#374151]">
                <div class="flex items-center gap-2 mb-1">
                    <i data-lucide="package-minus" class="w-4 h-4 text-red-400"></i>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Rechazos</span>
                </div>
                <span class="text-lg font-black text-white">2 <span class="text-xs text-gray-500 font-normal">clientes</span></span>
            </div>

        </div>

    </main>

    <!-- MENÚ INFERIOR DE 4 OPCIONES (Global) -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-[#111827] border-t border-[#374151] flex justify-around items-center px-1 z-50 shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.5)]">
        
        <!-- Botón 1: Ruta -->
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-gray-500 hover:text-cyan-400 flex-1 py-1 transition-colors group">
            <i data-lucide="map" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Ruta</span>
        </button>

        <!-- Botón 2: Catálogo (Promos y Consulta) -->
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-gray-500 hover:text-cyan-400 flex-1 py-1 transition-colors group">
            <i data-lucide="book-open" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Catálogo</span>
        </button>

        <!-- Botón 3: POS (Venta y Carrito) -->
        <button onclick="window.location.href='index.php?view=pos'" class="flex flex-col items-center justify-center text-gray-500 hover:text-cyan-400 flex-1 py-1 transition-colors group relative">
            <div class="bg-cyan-900/30 p-1.5 rounded-lg border border-cyan-800/50">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide text-cyan-400">Vender</span>
        </button>

        <!-- Botón 4: Metas -->
        <button onclick="window.location.href='index.php?view=metas'" class="flex flex-col items-center justify-center text-gray-500 hover:text-cyan-400 flex-1 py-1 transition-colors group">
            <i data-lucide="bar-chart-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Metas</span>
        </button>

    </nav>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>