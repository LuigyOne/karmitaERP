<?php
// Validar estado de sesión de forma segura para evitar avisos duplicados
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAN Auto-Venta - Gastos en Ruta</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <button onclick="window.location.href='index.php?view=ruta'" class="text-slate-600 hover:text-slate-900 transition-colors p-1 -ml-1">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </button>
            <h1 class="text-sm font-black text-slate-900 uppercase tracking-wide">Caja Chica</h1>
        </div>
    </header>

    <!-- PESTAÑAS (TABS) -->
    <div class="bg-white border-b border-slate-200 px-4 flex gap-6 sticky top-[53px] z-10 shadow-sm">
        <button id="tab-btn-nuevo" onclick="cambiarPestana('nuevo')" class="py-3 text-sm font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors">
            Nuevo Gasto
        </button>
        <button id="tab-btn-lista" onclick="cambiarPestana('lista')" class="py-3 text-sm font-bold text-slate-400 border-b-2 border-transparent transition-colors flex items-center gap-2">
            Movimientos de Hoy <span class="bg-slate-200 text-slate-700 text-[10px] px-1.5 py-0.5 rounded-full hidden" id="badge-gastos">0</span>
        </button>
    </div>

    <main class="flex-1 overflow-y-auto p-4 relative">
        
        <!-- PESTAÑA 1: FORMULARIO DE INGRESO -->
        <form id="form-gasto" onsubmit="guardarGasto(event)" class="space-y-6 pb-24 block">
            
            <!-- 1. MONTO -->
            <div class="text-center">
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Monto Total</label>
                <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center justify-center max-w-[200px] mx-auto shadow-sm">
                    <span class="text-3xl font-black text-emerald-600 mr-1">$</span>
                    <input type="number" id="monto" step="0.01" placeholder="0.00" required autocomplete="off"
                           class="bg-transparent w-full text-4xl font-black text-slate-900 focus:outline-none text-left placeholder-slate-300">
                </div>
            </div>

            <!-- 2. CATEGORÍAS -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-3 uppercase tracking-wider">Categoría</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer relative">
                        <input type="radio" name="categoria" value="Combustible" class="peer hidden" required>
                        <div class="p-3 bg-white border border-slate-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i data-lucide="fuel" class="w-6 h-6 mx-auto mb-1.5"></i>
                            <span class="text-[10px] font-bold uppercase block">Combustible</span>
                        </div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="categoria" value="Peaje" class="peer hidden">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i data-lucide="car" class="w-6 h-6 mx-auto mb-1.5"></i>
                            <span class="text-[10px] font-bold uppercase block">Peaje</span>
                        </div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="categoria" value="Almuerzo" class="peer hidden">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i data-lucide="utensils" class="w-6 h-6 mx-auto mb-1.5"></i>
                            <span class="text-[10px] font-bold uppercase block">Almuerzo</span>
                        </div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="categoria" value="Parqueo" class="peer hidden">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i data-lucide="circle-parking" class="w-6 h-6 mx-auto mb-1.5"></i>
                            <span class="text-[10px] font-bold uppercase block">Parqueo</span>
                        </div>
                    </label>

                    <label class="cursor-pointer relative">
                        <input type="radio" name="categoria" value="Varios" class="peer hidden">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i data-lucide="package-open" class="w-6 h-6 mx-auto mb-1.5"></i>
                            <span class="text-[10px] font-bold uppercase block">Varios</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 3. DESCRIPCIÓN -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Detalle (Opcional)</label>
                <div class="relative">
                    <i data-lucide="align-left" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="descripcion" placeholder="Ej: Cuidador vehículo centro..." 
                           class="w-full pl-10 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 shadow-sm transition-colors">
                </div>
            </div>

            <!-- 4. FOTO DEL RECIBO (Opcional con capture="environment") -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">
                    Comprobante <span class="text-slate-400 normal-case tracking-normal font-normal">(Opcional)</span>
                </label>
                
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-xl bg-white hover:bg-slate-50 cursor-pointer transition-colors relative overflow-hidden group shadow-sm">
                    <div id="zona-texto-foto" class="flex flex-col items-center justify-center text-slate-400 group-hover:text-emerald-600 transition-colors">
                        <i data-lucide="camera" class="w-6 h-6 mb-2"></i>
                        <p class="text-xs font-bold uppercase">Adjuntar Foto</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Cámara o Galería</p>
                    </div>
                    
                    <input type="file" id="foto-recibo" accept="image/*" capture="environment" class="hidden" onchange="previsualizarFoto(event)">
                    
                    <img id="preview-img" class="absolute inset-0 w-full h-full object-cover hidden" alt="Vista previa">
                    
                    <div id="btn-cambiar-foto" class="absolute bottom-2 right-2 bg-slate-900/80 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase hidden flex items-center gap-1 border border-white/20">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i> Cambiar
                    </div>
                </label>
            </div>
        </form>

        <!-- PESTAÑA 2: MOVIMIENTOS DE HOY (Oculta por defecto) -->
        <div id="tab-lista" class="hidden pb-24 space-y-4">
            
            <!-- Resumen Total -->
            <div class="bg-red-50 border border-red-200 p-4 rounded-2xl flex justify-between items-center shadow-sm mb-6">
                <span class="text-xs font-bold text-red-600 uppercase tracking-wider">Total Egresos Hoy</span>
                <span id="total-egresos" class="text-xl font-black text-red-700">$0.00</span>
            </div>

            <!-- Contenedor dinámico de lista -->
            <div id="lista-movimientos" class="space-y-3">
                <div class="text-center py-10 text-slate-400">
                    <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-3 opacity-50"></i>
                    <p class="text-sm font-medium">No hay gastos registrados hoy.</p>
                </div>
            </div>

        </div>
    </main>

    <!-- BOTÓN INFERIOR FIJO -->
    <div id="barra-inferior" class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-3 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)]">
        <button onclick="document.getElementById('form-gasto').requestSubmit()" class="w-full py-4 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-colors flex items-center justify-center gap-2 active:scale-95">
            <i data-lucide="save" class="w-4 h-4"></i> Guardar en Caja Chica
        </button>
    </div>

    <script>
        lucide.createIcons();

        // Cargar gastos de hoy al iniciar la página
        document.addEventListener("DOMContentLoaded", () => {
            actualizarContadorBadge();
        });

        function cambiarPestana(pestana) {
            const tabNuevo = document.getElementById('form-gasto');
            const tabLista = document.getElementById('tab-lista');
            const btnNuevo = document.getElementById('tab-btn-nuevo');
            const btnLista = document.getElementById('tab-btn-lista');
            const barraInferior = document.getElementById('barra-inferior');

            if (pestana === 'nuevo') {
                tabNuevo.classList.remove('hidden');
                tabLista.classList.add('hidden');
                barraInferior.classList.remove('hidden');
                
                btnNuevo.className = "py-3 text-sm font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors";
                btnLista.className = "py-3 text-sm font-bold text-slate-400 border-b-2 border-transparent transition-colors flex items-center gap-2";
            } else {
                tabNuevo.classList.add('hidden');
                tabLista.classList.remove('hidden');
                barraInferior.classList.add('hidden');
                
                btnNuevo.className = "py-3 text-sm font-bold text-slate-400 border-b-2 border-transparent transition-colors";
                btnLista.className = "py-3 text-sm font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors flex items-center gap-2";
                
                renderizarListaHoy();
            }
        }

        function previsualizarFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('zona-texto-foto').classList.add('hidden');
                    const img = document.getElementById('preview-img');
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    document.getElementById('btn-cambiar-foto').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // Guardar Gasto hacia el backend conectado a pwa_auditar
        async function guardarGasto(event) {
            event.preventDefault();
            
            const monto = document.getElementById('monto').value;
            const categoria = document.querySelector('input[name="categoria"]:checked').value;
            const descripcion = document.getElementById('descripcion').value;
            const inputFile = document.getElementById('foto-recibo');
            
            const formData = new FormData();
            formData.append('monto', monto);
            formData.append('categoria', categoria);
            formData.append('descripcion', descripcion);
            
            if (inputFile.files.length > 0) {
                formData.append('foto_recibo', inputFile.files[0]);
            }

            try {
                const respuesta = await fetch('backend/guardar_gasto.php', {
                    method: 'POST',
                    body: formData
                });
                const resultado = await respuesta.json();
                
                if (resultado.success) {
                    // Resetear formulario
                    document.getElementById('form-gasto').reset();
                    document.getElementById('zona-texto-foto').classList.remove('hidden');
                    document.getElementById('preview-img').classList.add('hidden');
                    document.getElementById('btn-cambiar-foto').classList.add('hidden');
                    document.getElementById('preview-img').src = '';

                    alert("Gasto registrado y auditado exitosamente.");
                    actualizarContadorBadge();
                    cambiarPestana('lista');
                } else {
                    alert("Error: " + resultado.message);
                }
            } catch (error) {
                console.error("Error de red:", error);
                alert("No se pudo completar la petición de guardado.");
            }
        }

        // Consultar y renderizar únicamente los movimientos de hoy desde la base de datos
        async function renderizarListaHoy() {
            const contenedor = document.getElementById('lista-movimientos');
            const totalEgresos = document.getElementById('total-egresos');

            try {
                const respuesta = await fetch('backend/obtener_gastos_hoy.php');
                const data = await respuesta.json();

                if (!data.success || data.gastos.length === 0) {
                    contenedor.innerHTML = `
                        <div class="text-center py-10 text-slate-400">
                            <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-3 opacity-50"></i>
                            <p class="text-sm font-medium">No hay gastos registrados hoy.</p>
                        </div>
                    `;
                    totalEgresos.innerText = '$0.00';
                    lucide.createIcons();
                    return;
                }

                let html = '';
                let total = 0;

                data.gastos.forEach(gasto => {
                    const valor = parseFloat(gasto.valor);
                    total += valor;
                    
                    const horaStr = gasto.fecha_hora_creacion ? gasto.fecha_hora_creacion.split(' ')[1].substring(0, 5) : '--:--';

                    html += `
                        <div class="bg-white p-4 rounded-2xl border border-slate-200 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-emerald-50 rounded-xl text-emerald-600 border border-emerald-100 shadow-sm">
                                    <i data-lucide="receipt" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        ${gasto.observaciones.split('|')[0] || 'Gasto'}
                                        ${gasto.foto_url ? `<a href="${gasto.foto_url}" target="_blank" class="text-emerald-600"><i data-lucide="image" class="w-3.5 h-3.5"></i></a>` : ''}
                                    </p>
                                    <p class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5 font-medium">
                                        <i data-lucide="clock" class="w-3 h-3"></i> ${horaStr} — ${gasto.observaciones}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-red-600">-$${valor.toFixed(2)}</p>
                            </div>
                        </div>
                    `;
                });

                contenedor.innerHTML = html;
                totalEgresos.innerText = `$${total.toFixed(2)}`;
                lucide.createIcons();

            } catch (error) {
                console.error("Error al obtener los gastos:", error);
            }
        }

        async function actualizarContadorBadge() {
            try {
                const respuesta = await fetch('backend/obtener_gastos_hoy.php');
                const data = await respuesta.json();
                const badge = document.getElementById('badge-gastos');
                
                if (data.success && data.gastos.length > 0) {
                    badge.innerText = data.gastos.length;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>
