<?php
// backend/login.php
session_start();
require_once 'conexion.php';

// Obtener bodegas disponibles excluyendo la bodega matriz/default ('DEF')
$bodegas_moviles = [];
try {
    $db = BaseDatos::obtenerConexion();
    $stmt = $db->query("SELECT loc_code, location_name FROM locations WHERE loc_code != 'DEF' AND inactive = 0 ORDER BY location_name ASC");
    $bodegas_moviles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bodegas_moviles = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VAN Auto-Venta - Acceso</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="apple-touch-icon" href="../assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex items-center justify-center p-4">

    <div id="login-container" class="w-full max-w-sm bg-white p-8 rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-sm">
                <i data-lucide="truck" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">VAN Auto-Venta</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Seleccione su bodega móvil y credenciales</p>
        </div>

        <form id="login-form" action="validar.php" method="POST" class="space-y-4">
            
            <!-- SELECCIÓN DE BODEGA MÓVIL / VEHÍCULO -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Bodega Móvil / Vehículo</label>
                <div class="relative">
                    <i data-lucide="package" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-600"></i>
                    <select name="bodega_movil" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all cursor-pointer font-semibold">
                        <option value="">-- Seleccione Bodega / Van --</option>
                        <?php foreach ($bodegas_moviles as $bod): ?>
                            <option value="<?php echo htmlspecialchars($bod['loc_code']); ?>">
                                <?php echo htmlspecialchars($bod['location_name'] . ' (' . $bod['loc_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">Usuario / Email</label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="username" required autocomplete="off" placeholder="correo@ejemplo.com"
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wider">PIN</label>
                <div class="relative">
                    <i data-lucide="key" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" name="password" required placeholder="••••"
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all font-medium">
                </div>
            </div>

            <!-- CAMPOS OCULTOS PARA GEOLOCALIZACIÓN -->
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lon" id="lon">

            <!-- BLOQUE DE ERRORES -->
            <?php if(isset($_GET['error'])): ?>
                <div class="text-xs font-bold text-red-600 bg-red-50 border border-red-200 p-3 rounded-xl text-center">
                    <?php 
                        if($_GET['error'] == 'credenciales') echo "Usuario, contraseña o bodega incorrectos.";
                        if($_GET['error'] == 'bodega') echo "Debe seleccionar una bodega móvil válida.";
                        if($_GET['error'] == 'expirado') echo "Su sesión expiró por inactividad.";
                    ?>
                </div>
            <?php endif; ?>

            <button type="submit" 
                    class="w-full py-3.5 bg-emerald-500 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-500/25 hover:bg-emerald-600 transition-all active:scale-95">
                Iniciar Jornada Auto-Venta
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    document.getElementById('lat').value = pos.coords.latitude;
                    document.getElementById('lon').value = pos.coords.longitude;
                },
                function(err) {
                    console.warn("Geolocalización no disponible: " + err.message);
                },
                { timeout: 10000, maximumAge: 60000 }
            );
        }
    </script>
</body>
</html>