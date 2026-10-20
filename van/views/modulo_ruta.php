<?php
// Cargar la lógica desacoplada de la ruta
require_once __DIR__ . '/../backend/ruta_data.php';
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAN Auto-Venta - Ruta</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="assets/js/pwa_helpers.js"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- OVERLAY DEL MENÚ LATERAL (Oculto por defecto) -->
    <div id="menu-lateral" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="toggleMenu()"></div>
        <div id="panel-menu" class="absolute top-0 left-0 bottom-0 w-3/4 max-w-sm bg-white border-r border-slate-200 transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl">
            
            <!-- Perfil del Vendedor Dinámico -->
            <div class="p-5 border-b border-slate-200 bg-slate-900 text-white">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                        <i data-lucide="user" class="w-6 h-6"></i>
                    </div>
                    <button onclick="toggleMenu()" class="text-slate-400 hover:text-white p-1 bg-slate-800 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <h2 class="text-base font-black text-white"><?php echo htmlspecialchars($GLOBAL_VENDEDOR_NOMBRE); ?></h2>
                <p class="text-xs text-emerald-400 font-bold mt-0.5">Van / Bodega: <?php echo htmlspecialchars($_SESSION['bodega_nombre'] ?? 'Rodante'); ?></p>

                <!-- SELECTOR DE ÁREA DE VENTA (ZONA) -->
                <div class="mt-4 pt-3 border-t border-slate-800">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3 h-3 text-emerald-400"></i> Área de Venta / Zona
                    </label>
                    <form id="form-zona" action="backend/actualizar_zona.php" method="POST">
                        <input type="hidden" name="view" value="ruta">
                        <input type="hidden" name="lat" id="zona-lat">
                        <input type="hidden" name="lon" id="zona-lon">
                        <select name="area_code" id="select-area" onchange="enviarFormularioConGPS('form-zona', 'zona-lat', 'zona-lon')" 
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl text-xs text-white font-medium py-2 px-3 focus:outline-none focus:border-emerald-500 transition-colors cursor-pointer">
                            <?php foreach ($GLOBAL_AREAS_VENTA as $area): ?>
                                <option value="<?php echo $area['area_code']; ?>" <?php echo ($GLOBAL_AREA_ACTUAL == $area['area_code']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($area['description']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

            </div>

            <!-- Opciones del Menú -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-3">
                <button class="w-full flex items-center gap-3 px-3 py-3 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-slate-400"></i> Sincronizar Datos
                </button>
                
                <button onclick="window.location.href='index.php?view=caja'" class="w-full flex items-center justify-between px-3 py-3 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-600 group-hover:text-emerald-700"></i>
                        <span>Gastos en Ruta</span>
                    </div>
                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Caja Chica</span>
                </button>

                <!-- CONFIGURACIÓN DE IMPRESORA BLUETOOTH EN ACORDEÓN OCULTO (ESTILO VAN) -->
                <div class="pt-1 pb-1">
                    <button onclick="toggleConfigImpresora()" class="w-full flex items-center justify-between px-3 py-3 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                        <div class="flex items-center gap-2">
                            <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                            <span>Config. Impresora</span>
                        </div>
                        <i id="icono-flecha-printer" data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform text-slate-400"></i>
                    </button>
                    
                    <!-- Panel desplegable protegido/oculto -->
                    <div id="panel-config-printer" class="hidden mt-2 p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                        <p class="text-[9px] text-amber-600 font-medium leading-tight">⚠️ Uso exclusivo técnico. No modificar a menos que cambie de aplicación de impresión.</p>
                        <div>
                            <label class="block text-[9px] text-slate-500 uppercase font-bold mb-1">Nombre del Paquete:</label>
                            <div class="flex gap-2">
                                <input type="text" id="input-package-config" placeholder="ru.a402d.rawbtprinter" 
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-mono focus:outline-none focus:border-emerald-500 shadow-sm">
                                <button type="button" onclick="guardarConfigImpresora()" 
                                        class="bg-emerald-500 text-white font-black text-xs px-3 py-1.5 rounded-lg hover:bg-emerald-600 transition-colors shrink-0 shadow-sm">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. TARJETA DETALLADA DE LA EMPRESA -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2 shadow-sm">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-2.5">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden p-1 shadow-sm">
                            <img src="assets/img/favicon.png" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-xs font-black text-slate-900 truncate uppercase"><?php echo htmlspecialchars($GLOBAL_COMPANY_COY_NO); ?></h4>
                            <p class="text-[10px] text-emerald-600 font-bold truncate">RUC: <?php echo htmlspecialchars($GLOBAL_COMPANY_RUC); ?></p>
                        </div>
                    </div>
                    <div class="text-[10px] text-slate-500 space-y-1">
                        <p><strong class="text-slate-700">Razón Social:</strong> <span class="text-slate-800"><?php echo htmlspecialchars($GLOBAL_COMPANY_COY_NAME); ?></span></p>
                        <p><strong class="text-slate-700">Domicilio:</strong> <?php echo htmlspecialchars($GLOBAL_COMPANY_DOMICILE); ?></p>
                        <p><strong class="text-slate-700">Dirección:</strong> <?php echo htmlspecialchars($GLOBAL_COMPANY_POSTAL); ?></p>
                        <p><strong class="text-slate-700">Email:</strong> <?php echo htmlspecialchars($GLOBAL_COMPANY_EMAIL); ?></p>
                        <p><strong class="text-slate-700">Teléfono:</strong> <?php echo htmlspecialchars($GLOBAL_COMPANY_PHONE); ?></p>
                    </div>
                </div>

                <!-- 2. TARJETA CÓDIGO QR RUC SRI -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-2">
                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider block">QR RUC — SRI</span>
                    <div class="bg-white p-2 rounded-lg inline-block shadow-sm border border-slate-200">
                        <img src="assets/img/QRSRI.png" alt="QR RUC SRI" class="w-28 h-28 object-contain mx-auto">
                    </div>
                    <p class="text-[9px] text-slate-500">Escanee para verificar RUC y documentos electrónicos</p>
                </div>

                <!-- 3. TARJETA CÓDIGO QR DEUNA -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center space-y-2">
                    <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider block">Cobros Digitales — DeUna</span>
                    <div class="bg-white p-2 rounded-lg inline-block shadow-sm border border-slate-200">
                        <img src="assets/img/QRDeUna.png" alt="QR DeUna" class="w-28 h-28 object-contain mx-auto">
                    </div>
                    <p class="text-[9px] text-slate-500">Escanee para realizar pagos instantáneos</p>
                </div>

            </nav>

            <div class="p-4 border-t border-slate-200 bg-slate-50">
                <button onclick="abrirModalLogout()" class="w-full flex items-center justify-center gap-2 py-3.5 bg-red-50 text-red-600 border border-red-200 font-black text-xs uppercase tracking-wider rounded-xl hover:bg-red-100 active:scale-95 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar Sesión
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN DE CIERRE DE SESIÓN -->
    <div id="modal-logout" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="cerrarModalLogout()"></div>
        <div class="relative bg-white border border-slate-200 rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center space-y-4">
            <div class="w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto border border-red-100">
                <i data-lucide="log-out" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">¿Cerrar Sesión de Auto-Venta?</h3>
                <p class="text-xs text-slate-500 mt-1">Verifica haber sincronizado todas las transacciones de tu bodega móvil antes de salir.</p>
            </div>
            
            <form id="form-logout" action="backend/logout.php" method="POST">
                <input type="hidden" name="lat" id="logout-lat">
                <input type="hidden" name="lon" id="logout-lon">
            </form>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="cerrarModalLogout()" 
                        class="flex-1 py-3 bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-all">
                    Cancelar
                </button>
                <button type="button" onclick="ejecutarLogoutConGPS()" 
                        class="flex-1 py-3 bg-red-600 text-white font-black text-xs uppercase rounded-xl shadow-lg shadow-red-600/20 hover:bg-red-700 transition-all active:scale-95">
                    Sí, Salir
                </button>
            </div>
        </div>
    </div>

    <!-- CABECERA PRINCIPAL -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-3">
            <button onclick="toggleMenu()" class="text-slate-600 hover:text-slate-900 transition-colors p-1 -ml-1">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase">ZONA: <?php echo htmlspecialchars($GLOBAL_NOMBRE_AREA); ?></h1>
                <p class="text-[10px] text-emerald-600 font-bold">Van: <?php echo htmlspecialchars($_SESSION['bodega_nombre'] ?? 'Rodante'); ?> • <?php echo count($GLOBAL_SUCURSALES); ?> Clientes</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php $geo_activa = $_SESSION['geo_disponible'] ?? false; ?>
            <div title="<?php echo $geo_activa ? 'GPS Activo' : 'Sin GPS'; ?>" 
                 class="flex items-center justify-center p-2 rounded-xl transition-all <?php echo $geo_activa ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm' : 'bg-slate-100 text-slate-400 border border-slate-200'; ?>">
                <i data-lucide="<?php echo $geo_activa ? 'locate-fixed' : 'locate-off'; ?>" class="w-5 h-5"></i>
            </div>

            <button class="text-slate-600 hover:text-slate-900 relative">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-emerald-500 rounded-full"></span>
            </button>
        </div>
    </header>

    <!-- ÁREA DE BÚSQUEDA Y FILTROS -->
    <div class="px-4 py-3 bg-slate-50 z-10 sticky top-[53px] border-b border-slate-200/60">
        <div class="flex items-center gap-2 mb-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="buscador-clientes" placeholder="Buscar cliente o sucursal..." 
                       class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 shadow-sm transition-colors">
                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </button>
            </div>

            <button onclick="window.location.href='index.php?view=nuevo_cliente'" 
                    class="flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-500 text-white font-black text-xs uppercase rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all active:scale-95 whitespace-nowrap">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Nuevo</span>
            </button>
        </div>

        <?php $dias_semana = ['L' => 'L', 'M' => 'M', 'X' => 'X', 'J' => 'J', 'V' => 'V', 'S' => 'S', 'D' => 'D']; ?>
        <div class="flex items-center justify-between gap-1 overflow-x-auto pb-1 hide-scrollbar">
            <?php foreach ($dias_semana as $key => $label): ?>
                <?php $activo = ($GLOBAL_DIA_ACTUAL === $key); ?>
                <a href="index.php?view=ruta&dia=<?php echo $key; ?>" 
                   class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all <?php echo $activo ? 'text-white bg-emerald-600 shadow-md shadow-emerald-500/20 font-black' : 'text-slate-600 bg-white border border-slate-200 hover:bg-slate-100'; ?>">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
            
            <?php $activo_todos = ($GLOBAL_DIA_ACTUAL === 'TODOS'); ?>
            <a href="index.php?view=ruta&dia=TODOS" 
               class="flex-shrink-0 px-3 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all <?php echo $activo_todos ? 'text-white bg-emerald-600 shadow-md shadow-emerald-500/20 font-black' : 'text-slate-600 bg-white border border-slate-200 hover:bg-slate-100'; ?>">
                Todos
            </a>
        </div>
    </div>

    <!-- LISTADO DINÁMICO DE SUCURSALES -->
    <main class="flex-1 overflow-y-auto px-4 py-3 pb-24 space-y-3" id="lista-clientes">
        <?php if (empty($GLOBAL_SUCURSALES)): ?>
            <div id="no-results" class="text-center py-10">
                <i data-lucide="folder-open" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <p class="text-sm font-bold text-slate-500">No hay sucursales asignadas a esta zona o día.</p>
            </div>
        <?php else: ?>
            <div id="no-results" class="text-center py-10 hidden">
                <i data-lucide="search-x" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
                <p class="text-sm font-bold text-slate-500">No se encontraron clientes con ese criterio de búsqueda.</p>
            </div>

            <?php foreach ($GLOBAL_SUCURSALES as $index => $sucursal): ?>
                <div class="cliente-card bg-white rounded-2xl border border-slate-200 p-4 hover:border-emerald-500/50 transition-all shadow-sm hover:shadow-md"
                     data-search="<?php echo strtolower(htmlspecialchars($sucursal['branch_ref'] . ' ' . $sucursal['customer_name'] . ' ' . $sucursal['br_name'] . ' ' . ($sucursal['phone'] ?? ''))); ?>">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-sm font-black text-slate-900"><?php echo htmlspecialchars($sucursal['branch_ref']); ?></h3>
                            <p class="text-xs text-emerald-600 font-bold mt-0.5"><?php echo htmlspecialchars($sucursal['customer_name']); ?></p>
                            <p class="text-[11px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($sucursal['br_address']); ?></p>
                        </div>
                        <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            <?php echo htmlspecialchars($sucursal['br_name']); ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center mt-3 pt-2.5 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-600 font-semibold bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                                📞 <?php echo htmlspecialchars($sucursal['phone'] ?? 'Sin teléfono'); ?>
                            </span>
                            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                                Días: <?php echo htmlspecialchars($sucursal['dias_visita'] ?: 'No especificado'); ?>
                            </span>
                        </div>
                        <button onclick="window.location.href='index.php?view=ficha&branch_code=<?php echo $sucursal['branch_code']; ?>'" 
                                class="px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition-colors shadow-sm">
                            Ver ficha
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- MENÚ INFERIOR GLOBAL -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-around items-center px-1 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-emerald-600 flex-1 py-1 transition-colors group">
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
        <button onclick="window.location.href='index.php?view=metas'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors group">
            <i data-lucide="bar-chart-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            <span class="text-[9px] font-bold mt-1 uppercase tracking-wide">Metas</span>
        </button>
    </nav>

    <script>
        lucide.createIcons();

        const inputBuscador = document.getElementById('buscador-clientes');
        if (inputBuscador) {
            inputBuscador.addEventListener('input', function(e) {
                const termino = e.target.value.toLowerCase().trim();
                const tarjetas = document.querySelectorAll('.cliente-card');
                let encontrados = 0;

                tarjetas.forEach(card => {
                    const textoBusqueda = card.getAttribute('data-search');
                    if (textoBusqueda.includes(termino)) {
                        card.style.display = 'block';
                        encontrados++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                const mensajeNoResults = document.getElementById('no-results');
                if (mensajeNoResults) {
                    mensajeNoResults.style.display = (encontrados === 0 && tarjetas.length > 0) ? 'block' : 'none';
                }
            });
        }

        function toggleMenu() {
            const contenedor = document.getElementById('menu-lateral');
            const panel = document.getElementById('panel-menu');
            
            if (contenedor.classList.contains('hidden')) {
                contenedor.classList.remove('hidden');
                setTimeout(() => panel.classList.remove('-translate-x-full'), 10);
            } else {
                panel.classList.add('-translate-x-full');
                setTimeout(() => contenedor.classList.add('hidden'), 300);
            }
        }

        // Función para ocultar/mostrar de forma segura la configuración avanzada de la impresora
        function toggleConfigImpresora() {
            const panel = document.getElementById('panel-config-printer');
            const icono = document.getElementById('icono-flecha-printer');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                icono.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                icono.style.transform = 'rotate(0deg)';
            }
        }

        // Cargar la configuración de impresora guardada al iniciar la vista
        document.addEventListener("DOMContentLoaded", () => {
            const savedPackage = localStorage.getItem('pos_printer_package');
            const inputConfig = document.getElementById('input-package-config');
            if (inputConfig && savedPackage) {
                inputConfig.value = savedPackage;
            }
        });

        function guardarConfigImpresora() {
            const inputConfig = document.getElementById('input-package-config');
            if (!inputConfig) return;
            
            let packageName = inputConfig.value.trim();
            localStorage.setItem('pos_printer_package', packageName);
            alert("¡Configuración de impresora guardada con éxito!");
            toggleConfigImpresora(); // Ocultar el panel al guardar con éxito
        }

        function abrirModalLogout() {
            const contenedorMenu = document.getElementById('menu-lateral');
            const panelMenu = document.getElementById('panel-menu');
            
            if (!contenedorMenu.classList.contains('hidden')) {
                panelMenu.classList.add('-translate-x-full');
                setTimeout(() => contenedorMenu.classList.add('hidden'), 300);
            }
            
            document.getElementById('modal-logout').classList.remove('hidden');
            lucide.createIcons();
        }

        function cerrarModalLogout() {
            document.getElementById('modal-logout').classList.add('hidden');
        }

        function ejecutarLogoutConGPS() {
            const btnSalir = event.target;
            btnSalir.innerText = "Saliendo...";
            btnSalir.disabled = true;

            const enviarLogoutServidor = (lat = null, lon = null) => {
                const formData = new URLSearchParams();
                formData.append('lat', lat || '');
                formData.append('lon', lon || '');

                fetch('backend/logout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                })
                .then(() => {
                    window.location.replace('index.php');
                })
                .catch(error => {
                    console.error("Error al cerrar sesión:", error);
                    window.location.replace('index.php');
                });
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        enviarLogoutServidor(position.coords.latitude, position.coords.longitude);
                    },
                    (error) => {
                        enviarLogoutServidor(null, null);
                    },
                    { timeout: 4000, enableHighAccuracy: true }
                );
            } else {
                enviarLogoutServidor(null, null);
            }
        }
    </script>
</body>
</html>