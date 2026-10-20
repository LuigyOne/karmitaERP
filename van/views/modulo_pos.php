<?php
require_once __DIR__ . '/../backend/pos_data.php';
$preselected_branch = isset($_GET['branch_code']) ? intval($_GET['branch_code']) : 0;

$GLOBAL_COMPANY_COY_NO   = $_SESSION['company_coy_no'] ?? 'SANCHEZ VERA DISTRIBUCION';
$GLOBAL_COMPANY_COY_NAME = $_SESSION['company_coy_name'] ?? 'SANCHEZ ESCOBAR RONALD';
$GLOBAL_COMPANY_RUC      = $_SESSION['company_ruc'] ?? '1759318114001';
$GLOBAL_COMPANY_DOMICILE = $_SESSION['company_domicile'] ?? '';
$GLOBAL_COMPANY_POSTAL   = $_SESSION['company_postal'] ?? '';
$GLOBAL_COMPANY_EMAIL    = $_SESSION['company_email'] ?? '';
$GLOBAL_COMPANY_PHONE    = $_SESSION['company_phone'] ?? '';
$GLOBAL_VENDEDOR_NOMBRE  = $_SESSION['user_real_name'] ?? $_SESSION['salesman_name'] ?? 'Vendedor';
$GLOBAL_VENDEDOR_CODIGO  = $_SESSION['user_id'] ?? $_SESSION['salesman_code'] ?? '001';
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAN Auto-Venta - Vender</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col overflow-hidden">

    <!-- CABECERA Y SELECCIÓN DE CLIENTE / SUCURSAL -->
    <div class="flex flex-col z-25 shrink-0 shadow-sm">
        <header class="bg-white px-4 py-3 flex items-center justify-between border-b border-slate-200">
            <div class="flex items-center gap-3">
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wide">POS - Preventa</h1>
            </div>
            <button onclick="window.location.href='index.php?view=ruta'" class="text-slate-600 hover:text-emerald-600 transition-colors flex items-center gap-1 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                <i data-lucide="map" class="w-4 h-4"></i>
                <span class="text-[10px] font-bold uppercase">Ir a Ruta</span>
            </button>
        </header>

        <!-- TARJETA DE DATOS BÁSICOS Y SUCURSALES -->
        <div class="bg-white border-b border-slate-200 px-4 py-3 space-y-2.5 shadow-sm">
            <div class="flex items-center gap-3">
                <i data-lucide="users" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <div class="flex flex-col w-full relative">
                    <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">Seleccionar Cliente:</span>
                    <select id="select-cliente" onchange="actualizarSucursales()" class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-black text-slate-900 focus:outline-none focus:border-emerald-500 w-full cursor-pointer shadow-sm">
                        <option value="">Seleccione un cliente</option>
                        <?php 
                        $clientes_unicos = [];
                        foreach ($clientes_sucursales as $row) {$clientes_unicos[$row['debtor_no']] =$row; }
                        foreach ($clientes_unicos as $cli):$etiqueta_selector = htmlspecialchars($cli['branch_ref'] . ' — ' .$cli['customer_name']);
                        ?>
                            <option value="<?php echo $cli['debtor_no']; ?>">
                                <?php echo $etiqueta_selector; ?> (RUC: <?php echo htmlspecialchars($cli['ruc_cedula'] ?? 'N/D'); ?>)
                            </option>
                        <?php endforeach; ?>
                        <option value="999" class="text-emerald-600 font-bold">CONSUMIDOR FINAL</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pl-8">
                <div class="flex flex-col w-full relative">
                    <span class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">Sucursal / Dirección de Entrega:</span>
                    <select id="select-sucursal" onchange="mostrarDetallesSucursal()" class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-emerald-700 focus:outline-none focus:border-emerald-500 w-full cursor-pointer shadow-sm" disabled>
                        <option value="">Primero seleccione un cliente</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL: CATÁLOGO Y CARRITO -->
    <div class="flex-1 flex flex-col lg:grid lg:grid-cols-12 overflow-hidden relative pb-20 lg:pb-0">
        <main id="seccion-catalogo" class="flex-1 lg:col-span-7 flex flex-col h-full bg-slate-50 p-4 overflow-y-auto border-r border-slate-200">
            <div class="sticky top-0 z-20 bg-slate-50 pb-3 pt-1">
                <div class="relative w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="input-busqueda-producto" placeholder="Buscar producto por nombre o ID..." 
                        class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 shadow-sm font-medium">
                </div>
            </div>
            <div id="grid-productos" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 pb-4 mt-1"></div>
        </main>

        <!-- PANEL DE CARRITO (ESCRITORIO / TABLET) -->
        <aside class="hidden lg:flex lg:col-span-5 flex-col h-full bg-white p-4 justify-between overflow-hidden border-l border-slate-200 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-600"></i>
                    <h2 class="text-xs font-black uppercase text-slate-900 tracking-wider">Pedido Actual</h2>
                </div>
                <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg border border-emerald-100 font-bold shadow-sm">Preventa</span>
            </div>
            <div id="contenedor-carrito" class="flex-1 overflow-y-auto py-3 pr-1 space-y-2 min-h-0"></div>
            <div class="pt-3 border-t border-slate-200 space-y-3 shrink-0">
                <div class="flex justify-between items-center bg-slate-50 px-4 py-3.5 rounded-2xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Estimado:</span>
                    <span id="total-monto" class="text-lg font-black text-slate-900">$0.00</span>
                </div>
                <button id="btn-pagar" disabled class="w-full py-4 bg-slate-100 text-slate-400 border border-slate-200 font-bold text-xs uppercase rounded-xl cursor-not-allowed shadow-sm">
                    Carrito Vacío
                </button>
            </div>
        </aside>
    </div>

    <!-- MODAL / CARRITO FLOTANTE PARA MÓVILES -->
    <div id="modal-carrito-movil" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex flex-col justify-end lg:hidden hidden">
        <div class="bg-white rounded-t-3xl p-5 max-h-[85vh] flex flex-col border-t border-slate-200 shadow-2xl animate-in slide-in-from-bottom duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-600"></i>
                    <h2 class="text-xs font-black uppercase text-slate-900 tracking-wider">Tu Pedido Actual</h2>
                </div>
                <button onclick="toggleModalMovil()" class="text-slate-400 hover:text-slate-900 p-1 rounded-xl bg-slate-50 border border-slate-200 shadow-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="contenedor-carrito-movil" class="flex-1 overflow-y-auto py-3 space-y-2 max-h-[50vh]"></div>
            <div class="pt-3 border-t border-slate-200 space-y-3 shrink-0 mt-2">
                <div class="flex justify-between items-center bg-slate-50 px-4 py-3.5 rounded-2xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Estimado:</span>
                    <span id="total-monto-movil" class="text-lg font-black text-slate-900">$0.00</span>
                </div>
                <button id="btn-pagar-movil" disabled class="w-full py-4 bg-slate-100 text-slate-400 border border-slate-200 font-bold text-xs uppercase rounded-xl cursor-not-allowed shadow-sm">
                    Carrito Vacío
                </button>
            </div>
        </div>
    </div>

    <!-- BARRA INFERIOR MÓVIL (ACCESO A CARRITO / CHECKOUT) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-between items-center px-6 z-30 shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.08)]">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-slate-500 hover:text-emerald-600 transition-colors">
            <i data-lucide="map" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold uppercase mt-0.5">Ruta</span>
        </button>
        <button onclick="toggleModalMovil()" class="flex flex-col items-center justify-center text-emerald-600 relative">
            <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            <span id="badge-carrito" class="absolute -top-1 -right-2 bg-emerald-500 text-white font-black text-[10px] w-4 h-4 rounded-full flex items-center justify-center hidden">0</span>
            <span class="text-[9px] font-bold uppercase mt-0.5">Ver Pedido</span>
        </button>
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-slate-500 hover:text-emerald-600 transition-colors">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold uppercase mt-0.5">Catálogo</span>
        </button>
    </div>

    <!-- MODAL DE CHECKOUT / CONFIRMACIÓN -->
    <div id="modal-checkout" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl w-full max-w-lg border border-slate-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            <div class="bg-white px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">Confirmar Preventa</h2>
                    <p class="text-[10px] text-slate-400 font-bold">Detalles de Entrega y Pago</p>
                </div>
                <button onclick="toggleModalCheckout()" class="text-slate-400 hover:text-slate-900 p-1 rounded-xl bg-slate-50 border border-slate-200 shadow-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-5 overflow-y-auto space-y-4 flex-1 bg-slate-50/50">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 flex justify-between items-center shadow-sm">
                    <div>
                        <span class="text-[9px] bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg border border-emerald-100 uppercase font-black">Cliente</span>
                        <h3 id="checkout-cliente-nombre" class="text-xs font-bold text-slate-900 mt-1">Cliente General</h3>
                        <div class="text-[10px] text-emerald-600 font-semibold mt-0.5" id="checkout-sucursal-info">Sucursal: Principal</div>
                    </div>
                    <div class="text-right">
                        <span id="checkout-total" class="text-base font-black text-slate-900">$0.00</span>
                        <span class="block text-[8px] text-slate-400 uppercase font-bold">Total Orden</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Forma de Pago</label>
                        <select id="forma_pago" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-xs text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
                            <option value="CONTADO">Efectivo / Contado</option>
                            <option value="CREDITO_15">Crédito 15 Días</option>
                            <option value="CREDITO_30">Crédito 30 Días</option>
                            <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Horario Estimado de Entrega</label>
                        <select id="horario_entrega" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-xs text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
                            <option value="MANANA">Mañana (08:00 - 12:00)</option>
                            <option value="TARDE">Tarde (13:00 - 17:00)</option>
                            <option value="URGENTE">Entrega Inmediata</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Persona de Contacto / Receptor</label>
                        <input type="text" id="contacto_entrega" placeholder="Ej. Don José (Propietario)" 
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-xs text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Observaciones / Notas para Matriz</label>
                        <textarea id="notas_pedido" rows="2" placeholder="Ej. Entregar por la puerta posterior..." 
                                class="w-full bg-white border border-slate-200 rounded-xl p-3 text-xs text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm resize-none"></textarea>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 border-t border-slate-200 flex gap-2">
                <button onclick="toggleModalCheckout()" class="flex-1 py-3.5 bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-colors">
                    Regresar
                </button>
                <button id="btn-enviar-orden" onclick="enviarPreventaERP()" class="flex-1 py-3.5 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i> Enviar Orden
                </button>
            </div>
        </div>
    </div>

    <!-- SPINNER DE CARGA GLOBAL -->
    <div id="loading-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex flex-col items-center justify-center text-white">
        <div class="w-14 h-14 border-4 border-emerald-400 border-t-transparent rounded-full animate-spin mb-3 shadow-lg"></div>
        <p class="text-xs font-black uppercase tracking-widest">Procesando Preventa en el ERP...</p>
        <p class="text-[10px] text-slate-300 mt-1">Sincronizando con módulos contables y SRI. Espere un momento.</p>
    </div>

    <!-- MODAL DE RECIBO / TICKET TÉRMICO (58mm) -->
    <div id="modal-recibo" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl w-full max-w-sm border border-slate-200 shadow-2xl flex flex-col max-h-[95vh] overflow-hidden">
            <div class="bg-white px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i> Recibo de Preventa
                </h2>
                <button onclick="cerrarReciboYContinuar()" class="text-slate-400 hover:text-slate-900 p-1 rounded-xl bg-slate-50 border border-slate-200 shadow-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-4 overflow-y-auto flex-1 bg-slate-100 flex justify-center">
                <div id="ticket-termico" class="w-[280px] bg-white text-black font-mono p-4 rounded shadow-sm text-[11px] leading-tight space-y-2 border border-slate-200">
                    <div class="text-center border-b border-dashed border-slate-400 pb-2 space-y-0.5">
                        <h3 class="font-black text-xs uppercase text-black"><?php echo htmlspecialchars($GLOBAL_COMPANY_COY_NO); ?></h3>
                        <p class="text-[9px] text-black font-semibold">RUC: <?php echo htmlspecialchars($GLOBAL_COMPANY_RUC); ?></p>
                        <p class="text-[9px] text-black">Razón Social: <?php echo htmlspecialchars($GLOBAL_COMPANY_COY_NAME); ?></p>
                        <p class="text-[9px] text-slate-700">Domicilio: <?php echo htmlspecialchars($GLOBAL_COMPANY_DOMICILE); ?></p>
                        <p class="text-[9px] text-slate-700">Email: <?php echo htmlspecialchars($GLOBAL_COMPANY_EMAIL); ?></p>
                        <p class="text-[9px] font-bold uppercase mt-1 text-black">Preventa en Ruta</p>
                    </div>
                    <div class="border-b border-dashed border-slate-400 pb-2 space-y-0.5">
                        <p><strong>ORDEN N°:</strong> <span id="recibo-num-orden">ORD-0000</span></p>
                        <p><strong>FECHA:</strong> <span id="recibo-fecha">--/--/----</span></p>
                        <p><strong>CLIENTE:</strong> <span id="recibo-cliente">--</span></p>
                        <p><strong>SUCURSAL:</strong> <span id="recibo-sucursal">--</span></p>
                        <p><strong>PAGO:</strong> <span id="recibo-pago">--</span></p>
                    </div>
                    <div class="border-b border-dashed border-slate-400 pb-2">
                        <table class="w-full text-[10px]">
                            <thead>
                                <tr class="border-b border-slate-300">
                                    <th class="text-left py-1">CANT / ITEM</th>
                                    <th class="text-right py-1">P.UNIT</th>
                                    <th class="text-right py-1">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody id="recibo-items-body"></tbody>
                        </table>
                    </div>
                    <div class="text-right space-y-0.5 pt-1">
                        <p class="text-xs font-black">TOTAL ESTIMADO: $<span id="recibo-total">0.00</span></p>
                    </div>
                    <div class="text-center text-[9px] pt-3 border-t border-dashed border-slate-400 text-slate-600">
                        <p>¡Gracias por su compra!</p>
                        <p>Documento para uso interno / preventa</p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 border-t border-slate-200 grid grid-cols-3 gap-2">
                <button onclick="imprimirTicket()" class="py-2.5 bg-slate-50 text-slate-700 border border-slate-200 font-bold text-[10px] uppercase rounded-xl hover:bg-slate-100 transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4 text-emerald-600"></i><span>Imprimir</span>
                </button>
                <button onclick="compartirWhatsApp()" class="py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[10px] uppercase rounded-xl hover:bg-emerald-100 transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                    <i data-lucide="share-2" class="w-4 h-4"></i><span>Compartir</span>
                </button>
                <button onclick="cerrarReciboYContinuar()" class="py-2.5 bg-slate-50 text-slate-700 border border-slate-200 font-bold text-[10px] uppercase rounded-xl hover:bg-slate-100 transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                    <i data-lucide="check" class="w-4 h-4"></i><span>Cerrar</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        window.POS_SUCURSALES = <?php echo json_encode($clientes_sucursales); ?>;
        window.POS_PRODUCTOS = <?php echo json_encode($productos_catalogo); ?>;
        window.PRESELECTED_BRANCH = <?php echo $preselected_branch; ?>;
        lucide.createIcons();

        // AUTODETECCIÓN Y PRESELECCIÓN FORZADA DE SUCURSAL AL CARGAR
        document.addEventListener("DOMContentLoaded", () => {
            if (window.PRESELECTED_BRANCH && window.PRESELECTED_BRANCH > 0) {
                const sucursalEncontrada = window.POS_SUCURSALES.find(s => parseInt(s.branch_code) === window.PRESELECTED_BRANCH);
                if (sucursalEncontrada) {
                    const selectCliente = document.getElementById('select-cliente');
                    if (selectCliente) {
                        selectCliente.value = sucursalEncontrada.debtor_no;
                        
                        // Forzar ejecución de la función lógica de sucursales si existe
                        if (typeof actualizarSucursales === 'function') {
                            actualizarSucursales();
                        }

                        // Asignación segura con múltiples intentos para sincronizar con pos_logica.js
                        const intentarAsignarSucursal = (intentos) => {
                            const selectSucursal = document.getElementById('select-sucursal');
                            if (selectSucursal && selectSucursal.options.length > 1) {
                                selectSucursal.value = window.PRESELECTED_BRANCH;
                                if (typeof mostrarDetallesSucursal === 'function') {
                                    mostrarDetallesSucursal();
                                }
                            } else if (intentos > 0) {
                                setTimeout(() => intentarAsignarSucursal(intentos - 1), 100);
                            }
                        };
                        intentarAsignarSucursal(10);
                    }
                }
            }
        });

        function toggleModalMovil() {
            const modal = document.getElementById('modal-carrito-movil');
            if (modal) modal.classList.toggle('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function toggleModalCheckout() {
            const modal = document.getElementById('modal-checkout');
            if (!modal) return;
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden')) {
                const carrito = JSON.parse(localStorage.getItem('pos_carrito_actual')) || [];
                let total = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
                const elTot = document.getElementById('checkout-total');
                if (elTot) elTot.innerText = '$' + total.toFixed(2);

                const selectCliente = document.getElementById('select-cliente');
                const selectSucursal = document.getElementById('select-sucursal');
                if (selectCliente && selectCliente.selectedIndex > 0) {
                    document.getElementById('checkout-cliente-nombre').innerText = selectCliente.options[selectCliente.selectedIndex].text;
                }
                if (selectSucursal && selectSucursal.selectedIndex > 0) {
                    document.getElementById('checkout-sucursal-info').innerText = 'Sucursal: ' + selectSucursal.options[selectSucursal.selectedIndex].text;
                }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        let datosUltimaPreventa = null;

        async function enviarPreventaERP() {
            const carrito = JSON.parse(localStorage.getItem('pos_carrito_actual')) || [];
            if (carrito.length === 0) {
                alert("El carrito está vacío.");
                return;
            }

            const selectCliente = document.getElementById('select-cliente');
            const selectSucursal = document.getElementById('select-sucursal');
            
            let codClien = selectCliente ? parseInt(selectCliente.value || 0) : 0;
            // Respaldo automático con la variable global de URL si el selector visual no se fijó
            let codSucur = selectSucursal && selectSucursal.value ? parseInt(selectSucursal.value) : window.PRESELECTED_BRANCH;

            if (!codClien || codClien <= 0) {
                alert("Por favor seleccione un cliente válido antes de facturar.");
                return;
            }

            if (!codSucur || codSucur <= 0) {
                alert("Por favor seleccione una sucursal de entrega válida.");
                return;
            }

            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.style.display = 'flex';
            }
            const btnEnviar = document.getElementById('btn-enviar-orden');
            if (btnEnviar) btnEnviar.disabled = true;

            const datosPreventa = {
                cod_clien: codClien,
                cod_sucur: codSucur,
                forma_pago: document.getElementById('forma_pago').value,
                horario_entrega: document.getElementById('horario_entrega').value,
                contacto_entrega: document.getElementById('contacto_entrega').value,
                notas_pedido: document.getElementById('notas_pedido').value,
                items: carrito
            };

            try {
                const response = await fetch('backend/guardar_preventa.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datosPreventa)
                });

                const data = await response.json();

                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                }
                if (btnEnviar) btnEnviar.disabled = false;

                if (data.success) {
                    const modalCheckout = document.getElementById('modal-checkout');
                    if (modalCheckout) modalCheckout.classList.add('hidden');

                    const numeroOrdenOficial = data.sri_secuencial || data.num_orden || ('001-010-' + String(data.trans_no_erp).padStart(9, '0'));

                    datosUltimaPreventa = {
                        num_orden: numeroOrdenOficial,
                        clave_acceso: data.sri_clave_acceso || '',
                        fecha: new Date().toLocaleDateString(),
                        cliente: selectCliente.options[selectCliente.selectedIndex].text,
                        sucursal: selectSucursal && selectSucursal.selectedIndex > 0 ? selectSucursal.options[selectSucursal.selectedIndex].text : 'Principal',
                        forma_pago: document.getElementById('forma_pago').value,
                        items: carrito,
                        total: carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0)
                    };

                    sessionStorage.setItem('ultima_preventa_json', JSON.stringify(datosUltimaPreventa));

                    mostrarModalRecibo(datosUltimaPreventa);
                    localStorage.removeItem('pos_carrito_actual');
                } else {
                    alert("Detalle del error: " + (data.message || 'Error desconocido'));
                }
            } catch (error) {
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                }
                if (btnEnviar) btnEnviar.disabled = false;
                console.error("Error:", error);
                alert("Error en la petición de red o tiempo de espera agotado.");
            }
        }

        function mostrarModalRecibo(info) {
            document.getElementById('recibo-num-orden').innerText = info.num_orden;
            document.getElementById('recibo-fecha').innerText = info.fecha;
            document.getElementById('recibo-cliente').innerText = info.cliente;
            document.getElementById('recibo-sucursal').innerText = info.sucursal;
            document.getElementById('recibo-pago').innerText = info.forma_pago;
            document.getElementById('recibo-total').innerText = info.total.toFixed(2);

            const tbody = document.getElementById('recibo-items-body');
            tbody.innerHTML = info.items.map(it => `
                <tr>
                    <td class="py-0.5">${it.cantidad}x ${it.name}</td>
                    <td class="text-right py-0.5">$${it.precio.toFixed(2)}</td>
                    <td class="text-right py-0.5">$${(it.precio * it.cantidad).toFixed(2)}</td>
                </tr>
            `).join('');

            const modalRecibo = document.getElementById('modal-recibo');
            if (modalRecibo) modalRecibo.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // IMPRESIÓN TÉRMICA NATIVA RAWBT (ADAPTADA AL SRI Y 32 CARACTERES)
        function imprimirTicket() {
            if (!datosUltimaPreventa) {
                alert("No hay datos de preventa recientes para imprimir.");
                return;
            }

            let clienteTexto = datosUltimaPreventa.cliente || "CONSUMIDOR FINAL";
            let rucCedulaCliente = "9999999999999";
            const matchRuc = clienteTexto.match(/RUC:\s*([0-9A-Z]+)/i);
            if (matchRuc) {
                rucCedulaCliente = matchRuc[1];
            } else if (clienteTexto.includes("CONSUMIDOR")) {
                rucCedulaCliente = "9999999999999";
            }

            let claveAccesoSRI = datosUltimaPreventa.clave_acceso || "17102026011759318114001100101000000123456789123416";
            if (claveAccesoSRI.length < 49) {
                claveAccesoSRI = claveAccesoSRI.padEnd(49, '0');
            }
            let parteClave1 = claveAccesoSRI.substring(0, 24);
            let parteClave2 = claveAccesoSRI.substring(24, 49);

            let ticket = "";
            ticket += "     SANCHEZ VERA DISTRIBUCION     \n";
            ticket += "       RUC: 1759318114001          \n";
            ticket += "--------------------------------\n";
            ticket += "NOTA DE VENTA / PREVENTA        \n";
            ticket += "NRO: " + datosUltimaPreventa.num_orden.padEnd(27, ' ') + "\n";
            ticket += "FECHA: " + datosUltimaPreventa.fecha.padEnd(24, ' ') + "\n";
            ticket += "--------------------------------\n";
            ticket += "CLIENTE: " + clienteTexto.substring(0, 23).padEnd(23, ' ') + "\n";
            ticket += "RUC/CI:  " + rucCedulaCliente.padEnd(23, ' ') + "\n";
            ticket += "PAGO:    " + datosUltimaPreventa.forma_pago.padEnd(23, ' ') + "\n";
            ticket += "--------------------------------\n";
            ticket += "CANT DESC              P.U TOTAL\n";
            ticket += "--------------------------------\n";

            datosUltimaPreventa.items.forEach(it => {
                let qtyStr = String(it.cantidad).padEnd(4, ' ');
                let nombreItem = it.name.substring(0, 15).padEnd(15, ' ');
                let pUnitStr = Number(it.precio).toFixed(2).padStart(5, ' ');
                let subtotalStr = (it.precio * it.cantidad).toFixed(2).padStart(6, ' ');
                
                ticket += `${qtyStr} ${nombreItem} ${subtotalStr}\n`;
                ticket += `     P.Unit: $${pUnitStr}\n`;
            });

            ticket += "--------------------------------\n";
            let totalStr = "$" + Number(datosUltimaPreventa.total).toFixed(2);
            ticket += "VALOR TOTAL:".padEnd(20, ' ') + totalStr.padStart(12, ' ') + "\n";
            ticket += "--------------------------------\n";
            ticket += "CLAVE DE ACCESO SRI:\n";
            ticket += parteClave1 + "\n";
            ticket += parteClave2 + "\n";
            ticket += "--------------------------------\n";
            ticket += "     DOCUMENTO DE PREVENTA      \n";
            ticket += "   TRANSACCION INTERNA EN RUTA  \n";
            ticket += "      ¡GRACIAS POR SU COMPRA!   \n\n\n\n";

            let textoCodificado = encodeURIComponent(ticket);

            // 3. Obtener el paquete configurado o usar el valor por defecto si está en blanco
            let defaultPackage = "ru.a402d.rawbtprinter";
            let customPackage = localStorage.getItem('pos_printer_package');
            let targetPackage = (customPackage && customPackage !== "") ? customPackage : defaultPackage;

            // 4. Enviar utilizando la sintaxis de esquema nativa con el paquete dinámico
            window.location.href = `intent:/${textoCodificado}#Intent;scheme=rawbt;package=${targetPackage};end;`;            
        }

        function compartirWhatsApp() {
            if (!datosUltimaPreventa) return;
            let texto = `*PREVENTA S&V DISTRIBUCIONES*\n`;
            texto += `Orden: ${datosUltimaPreventa.num_orden}\n`;
            texto += `Cliente: ${datosUltimaPreventa.cliente}\n`;
            texto += `Total: $${datosUltimaPreventa.total.toFixed(2)}\n\n*Ítems:*\n`;
            datosUltimaPreventa.items.forEach(it => {
                texto += `- ${it.cantidad}x ${it.name} ($${(it.precio * it.cantidad).toFixed(2)})\n`;
            });
            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }

        function cerrarReciboYContinuar() {
            const modalRecibo = document.getElementById('modal-recibo');
            if (modalRecibo) modalRecibo.classList.add('hidden');
            window.location.href = 'index.php?view=ruta';
        }
    </script>
    <script src="assets/js/pos_cliente.js"></script>
    <script src="assets/js/pos_logica.js"></script>
</body>
</html>