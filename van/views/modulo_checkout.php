<?php
// Asegurar el prefijo de tablas vacío
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Confirmación de Preventa</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="index.php?view=pos" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Confirmar Preventa</h1>
                <p class="text-[10px] text-slate-400 font-bold">Detalles de Entrega y Pago</p>
            </div>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">

        <!-- RESUMEN RÁPIDO DE CLIENTE Y TOTAL -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex justify-between items-center">
            <div>
                <span class="text-[9px] bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg border border-emerald-100 uppercase font-black">Cliente Seleccionado</span>
                <h3 id="checkout-cliente-nombre" class="text-sm font-bold text-slate-900 mt-1">Cargando cliente...</h3>
                <span id="checkout-cliente-ruc" class="text-[10px] text-slate-400 font-mono font-medium">RUC/CI: --</span>
                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5" id="checkout-sucursal-info">Sucursal: --</div>
            </div>
            <div class="text-right">
                <span id="checkout-total" class="text-lg font-black text-slate-900">$0.00</span>
                <span class="block text-[8px] text-slate-400 uppercase font-bold">Total Orden (Incl. IVA)</span>
            </div>
        </div>

        <!-- FORMULARIO DE LOGÍSTICA Y PAGO -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-4 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-emerald-600"></i>
                Parámetros de la Orden
            </h3>

            <!-- Forma de Pago -->
            <div>
                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Forma de Pago</label>
                <select id="forma_pago" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
                    <option value="CONTADO">Efectivo / Contado</option>
                    <option value="CREDITO_15">Crédito 15 Días</option>
                    <option value="CREDITO_30">Crédito 30 Días</option>
                    <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                </select>
            </div>

            <!-- Horario Estimado de Entrega -->
            <div>
                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Horario Estimado de Entrega</label>
                <select id="horario_entrega" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
                    <option value="MANANA">Mañana (08:00 - 12:00)</option>
                    <option value="TARDE">Tarde (13:00 - 17:00)</option>
                    <option value="URGENTE">Entrega Inmediata</option>
                </select>
            </div>

            <!-- Contacto para la Entrega -->
            <div>
                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Persona de Contacto / Receptor</label>
                <input type="text" id="contacto_entrega" placeholder="Ej. Don José (Propietario)" 
                       class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-3 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm">
            </div>

            <!-- Observaciones / Notas para Matriz -->
            <div>
                <label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Observaciones / Notas para Matriz</label>
                <textarea id="notas_pedido" rows="3" placeholder="Ej. Entregar por la puerta posterior, verificar factura anterior..." 
                          class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-800 font-medium focus:outline-none focus:border-emerald-500 shadow-sm resize-none"></textarea>
            </div>
        </div>

    </main>


    <!-- MODAL / OVERLAY DE CARGA (SPINNER) -->
    <div id="loading-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex flex-col items-center justify-center text-white">
        <div class="w-12 h-12 border-4 border-emerald-400 border-t-transparent rounded-full animate-spin mb-3"></div>
        <p class="text-xs font-black uppercase tracking-wider">Procesando y sincronizando con el SRI...</p>
        <p class="text-[10px] text-slate-300 mt-1">Por favor, no cierre esta ventana.</p>
    </div>

    <!-- BARRA INFERIOR DE ACCIÓN -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 z-40 flex gap-3 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="window.location.href='index.php?view=pos'" class="flex-1 py-3.5 bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-colors">
            Volver al POS
        </button>
        <button id="btn-enviar" onclick="enviarPreventaERP()" class="flex-1 py-3.5 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer">
            <i data-lucide="send" class="w-4 h-4"></i> Enviar Orden
        </button>
    </div>

    <script>
        lucide.createIcons();

        document.addEventListener("DOMContentLoaded", () => {
            const carrito = JSON.parse(localStorage.getItem('pos_carrito_actual')) || [];
            let total = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
            const elTotal = document.getElementById('checkout-total');
            if (elTotal) elTotal.innerText = '$' + total.toFixed(2);

            const clienteSeleccionado = localStorage.getItem('pos_cliente_seleccionado');
            const sucursalSeleccionada = localStorage.getItem('pos_sucursal_seleccionada');

            if (clienteSeleccionado) {
                const cli = JSON.parse(clienteSeleccionado);
                document.getElementById('checkout-cliente-nombre').innerText = cli.customer_name || cli.nombre_comercial || 'Cliente General';
                document.getElementById('checkout-cliente-ruc').innerText = 'RUC/CI: ' + (cli.ruc_cedula || 'N/D');
            }

            if (sucursalSeleccionada) {
                const suc = JSON.parse(sucursalSeleccionada);
                document.getElementById('checkout-sucursal-info').innerText = 'Sucursal / Entrega: ' + (suc.branch_ref || 'Principal');
            }
        });

        async function enviarPreventaERP() {
            const carrito = JSON.parse(localStorage.getItem('pos_carrito_actual')) || [];
            if (carrito.length === 0) {
                alert("El carrito está vacío.");
                return;
            }

            // Obtener cliente y sucursal desde localStorage o de los selectores globales
            let codClien = 0;
            let codSucur = 0;

            const cliStorage = JSON.parse(localStorage.getItem('pos_cliente_seleccionado') || '{}');
            const sucStorage = JSON.parse(localStorage.getItem('pos_sucursal_seleccionada') || '{}');

            codClien = parseInt(cliStorage.debtor_no || cliStorage.id || 0);
            codSucur = parseInt(sucStorage.branch_code || sucStorage.id || 0);

            // Respaldo por si se usan los selectores directos en pantalla
            const selectCliente = document.getElementById('select-cliente');
            const selectSucursal = document.getElementById('select-sucursal');
            if (!codClien && selectCliente) codClien = parseInt(selectCliente.value || 0);
            if (!codSucur && selectSucursal) codSucur = parseInt(selectSucursal.value || 0);

            if (!codClien || codClien <= 0) {
                alert("Por favor seleccione un cliente válido antes de facturar.");
                return;
            }

            // 1. MOSTRAR SPINNER DE CARGA VISIBLE Y BLOQUEAR BOTÓN
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.style.display = 'flex'; // Fuerza el despliegue visual en caso de conflictos con Tailwind
            }
            const btnEnviar = document.getElementById('btn-enviar');
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

                // 2. OCULTAR SPINNER Y RESTAURAR BOTÓN
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.style.display = 'none';
                }
                if (btnEnviar) btnEnviar.disabled = false;

                if (data.success) {
                    const modalCheckout = document.getElementById('modal-checkout');
                    if (modalCheckout) modalCheckout.classList.add('hidden');

                    // Asegurar lectura infalible del secuencial del SRI o respaldo interno
                    const numeroOrdenOficial = data.sri_secuencial || data.num_orden || ('001-010-' + String(data.trans_no_erp).padStart(9, '0'));

                    const nombreClienteTexto = selectCliente && selectCliente.selectedIndex > 0 
                        ? selectCliente.options[selectCliente.selectedIndex].text 
                        : (cliStorage.customer_name || 'Cliente General');

                    const nombreSucursalTexto = selectSucursal && selectSucursal.selectedIndex > 0 
                        ? selectSucursal.options[selectSucursal.selectedIndex].text 
                        : (sucStorage.branch_ref || 'Principal');

                    datosUltimaPreventa = {
                        num_orden: numeroOrdenOficial,
                        clave_acceso: data.sri_clave_acceso || '',
                        fecha: new Date().toLocaleDateString(),
                        cliente: nombreClienteTexto,
                        sucursal: nombreSucursalTexto,
                        forma_pago: document.getElementById('forma_pago').value,
                        items: carrito,
                        total: carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0)
                    };

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

        function imprimirTicket() {
            const contenido = document.getElementById('ticket-termico').innerHTML;
            const ventanaImpresion = window.open('', '_blank', 'width=300,height=600');
            ventanaImpresion.document.write(`
                <html>
                    <head>
                        <title>Imprimir Ticket</title>
                        <style>
                            body { font-family: monospace; width: 58mm; margin: 0; padding: 5px; font-size: 11px; color: black; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { text-align: left; }
                            .text-right { text-align: right; }
                            .text-center { text-align: center; }
                            .border-b { border-bottom: 1px dashed #000; }
                        </style>
                    </head>
                    <body onload="window.print(); window.close();">
                        ${contenido}
                    </body>
                </html>
            `);
            ventanaImpresion.document.close();
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

        fn cerrarReciboYContinuar = function() {
            const modalRecibo = document.getElementById('modal-recibo');
            if (modalRecibo) modalRecibo.classList.add('hidden');
            window.location.href = 'index.php?view=ruta';
        }
    </script>

</body>
</html>