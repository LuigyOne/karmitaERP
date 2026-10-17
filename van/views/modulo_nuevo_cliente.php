<?php
// Validar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_logueado']) || $_SESSION['usuario_logueado'] !== true) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../backend/conexion.php';

// Obtener lista de clientes existentes para el TAB 2 (Nueva Sucursal)
$clientes_existentes = [];
try {
    $db = BaseDatos::obtenerConexion();
    $stmt =$db->query("SELECT debtor_no, name, debtor_ref, tax_id FROM debtors_master WHERE inactive = 0 ORDER BY name ASC");
    $clientes_existentes =$stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {$clientes_existentes = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Nuevo Cliente / Sucursal</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
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
            <h1 class="text-sm font-black text-slate-900 uppercase tracking-wide">Gestión de Clientes y Sucursales</h1>
        </div>
    </header>

    <!-- PESTAÑAS (TABS) -->
    <div class="bg-white border-b border-slate-200 px-4 flex gap-6 sticky top-[53px] z-10 shadow-sm">
        <button id="tab-btn-cliente" onclick="cambiarTab('cliente')" class="py-3 text-xs font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Cliente Nuevo
        </button>
        <button id="tab-btn-sucursal" onclick="cambiarTab('sucursal')" class="py-3 text-xs font-bold text-slate-400 border-b-2 border-transparent transition-colors uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="map-pin-plus" class="w-4 h-4"></i> Nueva Sucursal
        </button>
    </div>

    <main class="flex-1 overflow-y-auto p-4 relative pb-28">
        
        <!-- ================= TAB 1: CLIENTE NUEVO ================= -->
        <form id="form-cliente-nuevo" onsubmit="guardarRegistro(event, 'cliente_nuevo')" class="space-y-5 block">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Razón Social</label>
                <input type="text" name="razon_social" required placeholder="Ej: Importadora Comercial S.A." 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Nombre Comercial / Fantasía</label>
                <input type="text" name="nombre_comercial" required placeholder="Ej: Supermercado El Ahorro" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">RUC / Cédula</label>
                <input type="text" name="ruc" required maxlength="13" placeholder="1759318114001" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Teléfono</label>
                <input type="tel" name="telefono" placeholder="0991234567" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <!-- GEOLOCALIZACIÓN Y MAPA -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase block">Coordenadas GPS</span>
                        <span id="coords-status-cliente" class="text-[10px] text-emerald-600 font-bold">Sin capturar</span>
                    </div>
                    <button type="button" onclick="capturarGPS('cliente')" class="px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 flex items-center gap-1.5 transition-all shadow-sm">
                        <i data-lucide="locate-fixed" class="w-4 h-4"></i> Tomar GPS
                    </button>
                </div>
                <input type="hidden" name="lat" id="lat-cliente">
                <input type="hidden" name="lon" id="lon-cliente">

                <div id="map-container-cliente" class="w-full h-48 bg-slate-50 rounded-xl border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 text-xs font-medium shadow-inner">
                    Pulse "Tomar GPS" para registrar y mostrar ubicación
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Dirección Sucursal (Postal Address)</label>
                <input type="text" name="direccion" required placeholder="Av. Principal y Calle Secundaria" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Localidad (Domicile)</label>
                <input type="text" name="localidad" placeholder="Ciudad / Sector / Parroquia" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Persona de Contacto</label>
                <input type="text" name="contacto_persona" placeholder="Nombre del encargado" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Email</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <!-- DÍAS DE VISITA -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Días de Visita</label>
                <div class="grid grid-cols-7 gap-1.5">
                    <?php foreach (['L' => 'L', 'M' => 'M', 'X' => 'X', 'J' => 'J', 'V' => 'V', 'S' => 'S', 'D' => 'D'] as $key =>$lbl): ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="dias_visita[]" value="<?php echo $key; ?>" class="peer hidden">
                            <div class="h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-xs font-bold text-slate-500 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 transition-all shadow-sm">
                                <?php echo $lbl; ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FRECUENCIA -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Frecuencia</label>
                <select name="frecuencia" class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium cursor-pointer shadow-sm">
                    <option value="Semanal">Semanal</option>
                    <option value="Quincenal">Quincenal</option>
                    <option value="Mensual">Mensual</option>
                </select>
            </div>

            <!-- LISTA DE PRECIOS -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Lista de Precios</label>
                <select name="sales_type" class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium cursor-pointer shadow-sm">
                    <option value="1">Al por menor</option>
                    <option value="2">Al por mayor</option>
                </select>
            </div>

            <!-- CONDICIONES DE PAGO -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Condiciones de Pago</label>
                <select name="payment_terms" class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium cursor-pointer shadow-sm">
                    <option value="4">Contado</option>
                    <option value="3">A 10 días</option>
                    <option value="5">A 15 días</option>
                    <option value="6">A 30 días</option>
                </select>
            </div>

            <!-- BOTÓN GUARDAR CLIENTE -->
            <button type="submit" class="w-full py-4 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 active:scale-95">
                <i data-lucide="save" class="w-4 h-4"></i> Guardar Cliente Nuevo
            </button>
        </form>


        <!-- ================= TAB 2: NUEVA SUCURSAL ================= -->
        <form id="form-sucursal-nueva" onsubmit="guardarRegistro(event, 'sucursal_nueva')" class="space-y-5 hidden">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Seleccionar Cliente Existente</label>
                <select name="debtor_no" required class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium cursor-pointer shadow-sm">
                    <option value="">-- Buscar / Escoger Cliente --</option>
                    <?php foreach ($clientes_existentes as$cli): ?>
                        <option value="<?php echo $cli['debtor_no']; ?>">
                            <?php echo htmlspecialchars($cli['debtor_ref'] . ' — ' . $cli['name'] . ' (RUC: ' .$cli['tax_id'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- GEOLOCALIZACIÓN Y MAPA -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-700 uppercase block">Coordenadas GPS Sucursal</span>
                        <span id="coords-status-sucursal" class="text-[10px] text-emerald-600 font-bold">Sin capturar</span>
                    </div>
                    <button type="button" onclick="capturarGPS('sucursal')" class="px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 flex items-center gap-1.5 transition-all shadow-sm">
                        <i data-lucide="locate-fixed" class="w-4 h-4"></i> Tomar GPS
                    </button>
                </div>
                <input type="hidden" name="lat" id="lat-sucursal">
                <input type="hidden" name="lon" id="lon-sucursal">

                <div id="map-container-sucursal" class="w-full h-48 bg-slate-50 rounded-xl border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 text-xs font-medium shadow-inner">
                    Pulse "Tomar GPS" para registrar y mostrar ubicación
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Dirección Sucursal (Postal Address)</label>
                <input type="text" name="direccion" required placeholder="Av. Principal y Calle Secundaria" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Localidad (Domicile)</label>
                <input type="text" name="localidad" placeholder="Ciudad / Sector / Parroquia" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Persona de Contacto Sucursal</label>
                <input type="text" name="contacto_persona" placeholder="Nombre del encargado en sucursal" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Teléfono Sucursal</label>
                <input type="tel" name="telefono" placeholder="0991234567" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Email</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" 
                       class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium shadow-sm">
            </div>

            <!-- DÍAS DE VISITA -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Días de Visita</label>
                <div class="grid grid-cols-7 gap-1.5">
                    <?php foreach (['L' => 'L', 'M' => 'M', 'X' => 'X', 'J' => 'J', 'V' => 'V', 'S' => 'S', 'D' => 'D'] as $key =>$lbl): ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="dias_visita[]" value="<?php echo $key; ?>" class="peer hidden">
                            <div class="h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-xs font-bold text-slate-500 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 transition-all shadow-sm">
                                <?php echo $lbl; ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FRECUENCIA -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Frecuencia</label>
                <select name="frecuencia" class="w-full px-3.5 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 font-medium cursor-pointer shadow-sm">
                    <option value="Semanal">Semanal</option>
                    <option value="Quincenal">Quincenal</option>
                    <option value="Mensual">Mensual</option>
                </select>
            </div>

            <!-- BOTÓN GUARDAR SUCURSAL -->
            <button type="submit" class="w-full py-4 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all flex items-center justify-center gap-2 active:scale-95">
                <i data-lucide="save" class="w-4 h-4"></i> Guardar Nueva Sucursal
            </button>
        </form>

    </main>

    <script>
        lucide.createIcons();

        function cambiarTab(tab) {
            const formCliente = document.getElementById('form-cliente-nuevo');
            const formSucursal = document.getElementById('form-sucursal-nueva');
            const btnCliente = document.getElementById('tab-btn-cliente');
            const btnSucursal = document.getElementById('tab-btn-sucursal');

            if (tab === 'cliente') {
                formCliente.classList.remove('hidden');
                formSucursal.classList.add('hidden');
                btnCliente.className = "py-3 text-xs font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors uppercase tracking-wider flex items-center gap-2";
                btnSucursal.className = "py-3 text-xs font-bold text-slate-400 border-b-2 border-transparent transition-colors uppercase tracking-wider flex items-center gap-2";
            } else {
                formCliente.classList.add('hidden');
                formSucursal.classList.remove('hidden');
                btnCliente.className = "py-3 text-xs font-bold text-slate-400 border-b-2 border-transparent transition-colors uppercase tracking-wider flex items-center gap-2";
                btnSucursal.className = "py-3 text-xs font-bold text-emerald-600 border-b-2 border-emerald-600 transition-colors uppercase tracking-wider flex items-center gap-2";
            }
            lucide.createIcons();
        }

        function capturarGPS(tipo) {
            const statusEl = document.getElementById(`coords-status-${tipo}`);
            const latInput = document.getElementById(`lat-${tipo}`);
            const lonInput = document.getElementById(`lon-${tipo}`);
            const mapContainer = document.getElementById(`map-container-${tipo}`);

            if (!navigator.geolocation) {
                alert("La geolocalización no es compatible con este dispositivo.");
                return;
            }

            statusEl.innerText = "Obteniendo ubicación GPS...";

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    latInput.value = lat;
                    lonInput.value = lon;
                    statusEl.innerText = `Lat: ${lat.toFixed(6)}, Lon: ${lon.toFixed(6)}`;

                    // Renderizar mapa embebido de Google Maps
                    mapContainer.innerHTML = `
                        <iframe width="100%" height="100%" frameborder="0" 
                            src="https://maps.google.com/maps?q=${lat},${lon}&hl=es&z=17&output=embed">
                        </iframe>
                    `;
                },
                (error) => {
                    console.error(error);
                    statusEl.innerText = "Error al obtener GPS";
                    alert("No se pudo obtener la ubicación GPS. Verifique los permisos del navegador.");
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        async function guardarRegistro(event, tipoAccion) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('tipo_accion', tipoAccion);

            try {
                const respuesta = await fetch('backend/guardar_cliente_sucursal.php', {
                    method: 'POST',
                    body: formData
                });
                const resultado = await respuesta.json();

                if (resultado.success) {
                    alert("¡Registro guardado y auditado exitosamente!");
                    window.location.href = 'index.php?view=ruta';
                } else {
                    alert("Error: " + resultado.message);
                }
            } catch (error) {
                console.error("Error de red:", error);
                alert("No se pudo conectar con el servidor para guardar el registro.");
            }
        }
    </script>
</body>
</html>
