<?php
// Incluir la lógica de backend desacoplada
require_once __DIR__ . '/../backend/ficha_cliente_data.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Ficha de Cliente</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="assets/js/foto_cliente_helper.js"></script>
    <style>
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] summary ~ * { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-5px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA DE RETORNO -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-3">
            <button onclick="window.location.href='index.php?view=ruta'" class="text-slate-600 hover:text-slate-900 transition-colors p-1 -ml-1">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </button>
            <h1 class="text-sm font-black text-slate-900 uppercase tracking-wide">Ficha de Cliente</h1>
        </div>
        <span class="bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-sm">⭐ VIP</span>
    </header>

    <main class="flex-1 overflow-y-auto pb-24">
        
        <!-- PERFIL Y FOTOS -->
        <div class="p-4 bg-white border-b border-slate-200 shadow-sm">
            <h2 class="text-xl font-black text-slate-900 leading-tight"><?php echo htmlspecialchars($nombre_comercial); ?></h2>
            <p class="text-xs text-emerald-600 font-bold flex items-center gap-1.5 mt-1">
                <i data-lucide="user" class="w-3.5 h-3.5"></i> <?php echo htmlspecialchars($nombre_cliente); ?>
            </p>
            
            <!-- Carrusel fijo de 4 espacios fotográficos del local -->
            <div class="mt-4 flex gap-3 overflow-x-auto hide-scrollbar pb-2 snap-x">
                <?php for ($i = 1; $i <= 4; $i++): 
                    $foto_col = "foto_$i";
                    $tiene_foto = !empty($sucursal[$foto_col]);
                    // Construir la ruta apuntando a la carpeta compartida un nivel arriba
                    $ruta_img = $tiene_foto ? '../company/1/images/branches/' . $sucursal[$foto_col] : '';
                ?>
                    <div class="snap-start relative w-36 h-24 bg-slate-100 rounded-2xl flex-shrink-0 border border-slate-200 overflow-hidden group flex flex-col items-center justify-center shadow-sm">
                        <?php if ($tiene_foto): ?>
                            <!-- Imagen existente -->
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($ruta_img); ?>');"></div>
                            <!-- Botón flotante para cambiar la foto -->
                            <button onclick="abrirCapturaFotoCliente(<?php echo $branch_code; ?>, <?php echo $i; ?>)" 
                                    class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white cursor-pointer">
                                <i data-lucide="refresh-cw" class="w-5 h-5 mb-1 text-emerald-400"></i>
                                <span class="text-[9px] font-bold uppercase tracking-wider">Cambiar #<?php echo $i; ?></span>
                            </button>
                        <?php else: ?>
                            <!-- Espacio vacío para capturar nueva foto -->
                            <button onclick="abrirCapturaFotoCliente(<?php echo $branch_code; ?>, <?php echo $i; ?>)" 
                                    class="w-full h-full flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 transition-colors cursor-pointer">
                                <i data-lucide="camera" class="w-5 h-5 mb-1"></i>
                                <span class="text-[9px] font-bold uppercase tracking-wider">Foto #<?php echo $i; ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>        

        <!-- ACORDEONES DE INFORMACIÓN -->
        <div class="p-4 space-y-3">
            
            <!-- 1. Dirección y Mapa -->
            <details class="bg-white border border-slate-200 rounded-2xl overflow-hidden group shadow-sm">
                <summary class="flex justify-between items-center p-4 cursor-pointer hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100"><i data-lucide="map-pin" class="w-4 h-4"></i></div>
                        <span class="text-sm font-bold text-slate-800">Ubicación y Dirección</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <p class="text-xs text-slate-600 mb-3 leading-relaxed"><?php echo htmlspecialchars($direccion); ?></p>
                    
                    <?php if ($lat != '' && $lon != ''): ?>
                        <div class="w-full h-64 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                            <iframe width="100%" height="100%" frameborder="0" src="https://maps.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lon; ?>&hl=es&z=17&output=embed"></iframe>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-24 bg-white rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-medium">
                            Sin coordenadas GPS registradas
                        </div>
                    <?php endif; ?>
                </div>
            </details>

            <!-- 2. Información Tributaria y Logística -->
            <details class="bg-white border border-slate-200 rounded-2xl overflow-hidden group shadow-sm">
                <summary class="flex justify-between items-center p-4 cursor-pointer hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100"><i data-lucide="file-text" class="w-4 h-4"></i></div>
                        <span class="text-sm font-bold text-slate-800">Datos Tributarios y Logística</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 space-y-3">
                    <div class="flex justify-between items-end border-b border-slate-200 pb-2">
                        <span class="text-xs text-slate-500 font-medium">RUC / Cédula</span>
                        <span class="text-sm font-black text-slate-900 tracking-widest"><?php echo htmlspecialchars($ruc); ?></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                        <span class="text-xs text-slate-500 font-medium">Días de Visita</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100"><?php echo htmlspecialchars($dias_visita); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium">Frecuencia</span>
                        <span class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($frecuencia); ?></span>
                    </div>
                </div>
            </details>

            <!-- 3. Balance y Sugeridos -->
            <details class="bg-white border border-slate-200 rounded-2xl overflow-hidden group shadow-sm" open>
                <summary class="flex justify-between items-center p-4 cursor-pointer hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></div>
                        <span class="text-sm font-bold text-slate-800">Balance Comercial</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Total Compras</span>
                            <span class="text-sm font-black text-slate-900">$<?php echo number_format($total_compras, 2); ?></span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block mb-0.5">Total Pagos</span>
                            <span class="text-sm font-black text-emerald-600">$<?php echo number_format($total_pagos, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center bg-red-50 p-3.5 rounded-xl border border-red-200 shadow-sm">
                        <span class="text-xs font-bold text-red-600">Saldo Pendiente</span>
                        <span class="text-lg font-black text-red-700">$<?php echo number_format($saldo_pendiente, 2); ?></span>
                    </div>
                </div>
            </details>

        </div>
    </main>

    <!-- BARRA DE ACCIÓN INFERIOR -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-3 flex gap-2 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button class="flex-1 py-3.5 bg-white text-slate-700 border border-slate-200 font-bold text-[10px] uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 hover:bg-slate-50 transition-colors shadow-sm">
            <i data-lucide="edit-3" class="w-4 h-4"></i> Editar
        </button>
        
        <!-- NUEVO BOTÓN VISITA -->
        <button onclick="abrirModalVisita()" class="flex-1 py-3.5 bg-amber-50 text-amber-700 border border-amber-200 font-black text-[10px] uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 hover:bg-amber-100 transition-colors shadow-sm">
            <i data-lucide="clipboard-check" class="w-4 h-4"></i> Visita
        </button>

        <button onclick="window.location.href='index.php?view=pos&branch_code=<?php echo $branch_code; ?>'" class="flex-[1.5] py-3.5 bg-emerald-500 text-white font-black text-[10px] uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-colors flex items-center justify-center gap-1.5 active:scale-95">
            <i data-lucide="shopping-cart" class="w-4 h-4"></i> Venta
        </button>
    </div>

    <!-- MODAL DE OPCIONES DE VISITA -->
    <div id="modal-visita" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="cerrarModalVisita()"></div>
        <div class="relative bg-white border border-slate-200 rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-amber-50 text-amber-700 rounded-xl flex items-center justify-center border border-amber-200 shadow-sm">
                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Registro de Visita</h3>
                        <p class="text-[10px] text-slate-500 truncate max-w-[200px]"><?php echo htmlspecialchars($nombre_comercial); ?></p>
                    </div>
                </div>
                <button onclick="cerrarModalVisita()" class="text-slate-400 hover:text-slate-900 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Formulario que mantiene todos sus elementos intactos en el DOM -->
            <form id="form-visita" action="backend/guardar_visita.php" method="POST">
                <input type="hidden" name="branch_code" value="<?php echo $branch_code; ?>">
                <input type="hidden" name="debtor_no" value="<?php echo htmlspecialchars($sucursal['debtor_no'] ?? ''); ?>">
                <input type="hidden" name="resultado_visita" id="input-resultado-visita">
                <input type="hidden" name="lat" id="visita-lat">
                <input type="hidden" name="lon" id="visita-lon">

                <!-- Opciones de Visita -->
                <div id="visita-opciones" class="space-y-2 py-1">
                    <button type="button" onclick="seleccionarVisita('Tiene Producto')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-emerald-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                        <span>Tiene Producto</span>
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-slate-400 group-hover:text-emerald-600"></i>
                    </button>
                    <button type="button" onclick="seleccionarVisita('Cerrado')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-red-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                        <span>Cerrado</span>
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 group-hover:text-red-600"></i>
                    </button>
                    <button type="button" onclick="seleccionarVisita('Sin encargado')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-amber-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                        <span>Sin encargado</span>
                        <i data-lucide="user-x" class="w-4 h-4 text-slate-400 group-hover:text-amber-600"></i>
                    </button>
                    <button type="button" onclick="seleccionarVisita('ReAgenda')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-blue-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                        <span>ReAgenda</span>
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400 group-hover:text-blue-600"></i>
                    </button>
                </div>

                <!-- Indicador de Carga (Oculto inicialmente) -->
                <div id="visita-loading" class="hidden py-6 text-center space-y-3">
                    <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Guardando Visita...</h3>
                    <p class="text-[10px] text-slate-500">Capturando geolocalización y registrando auditoría.</p>
                </div>
            </form>

            <div id="contenedor-btn-cancelar" class="pt-1">
                <button type="button" onclick="cerrarModalVisita()" class="w-full py-3 bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/pwa_helpers.js"></script>
    <script>
        lucide.createIcons();

        function abrirModalVisita() {
            document.getElementById('visita-opciones').classList.remove('hidden');
            document.getElementById('visita-loading').classList.add('hidden');
            document.getElementById('contenedor-btn-cancelar').classList.remove('hidden');
            
            document.getElementById('modal-visita').classList.remove('hidden');
            lucide.createIcons();
        }

        function cerrarModalVisita() {
            document.getElementById('modal-visita').classList.add('hidden');
        }

        function seleccionarVisita(opcion) {
            document.getElementById('input-resultado-visita').value = opcion;
            
            document.getElementById('visita-opciones').classList.add('hidden');
            document.getElementById('contenedor-btn-cancelar').classList.add('hidden');
            document.getElementById('visita-loading').classList.remove('hidden');
            
            enviarFormularioConGPS('form-visita', 'visita-lat', 'visita-lon');
        }


    </script>
</body>
</html>