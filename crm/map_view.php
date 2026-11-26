<?php
/**********************************************************************
    NotrinosERP - Mapa Interactivo CRM por Capas y Pantalla Completa
***********************************************************************/
$page_security = 'SA_OPEN';
$path_to_root  = '..';

include_once($path_to_root . '/includes/session.inc');

$current_company = isset($company) ? $company : 0;
$current_user    = isset($_SESSION['wa_current_user']) ? $_SESSION['wa_current_user']->username : 'Invitado';
$is_fullscreen   = isset($_GET['fullscreen']) && $_GET['fullscreen'] == '1';

$sync_interval = 60;
$pref_res = db_query("SELECT value FROM " . TB_PREF . "sys_prefs WHERE name = 'sync_interval' AND category = 'crm_geo'", "Error leyendo sync_interval");
if ($pref_res && db_num_rows($pref_res) > 0) {
    $pref_row = db_fetch($pref_res);
    $val = (int)$pref_row['value'];
    if ($val >= 10) $sync_interval = $val;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa Interactivo CRM - Operación General</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: Tahoma, Arial, sans-serif; font-size: 12px; background: #fff; overflow: hidden; display: flex; flex-direction: column; height: 100vh; }
        .map-header-bar { display: flex; justify-content: space-between; align-items: center; padding: 5px 10px; background: #f8fafc; border-bottom: 1px solid #cbd5e1; gap: 6px; flex-wrap: wrap; }
        .toolbar-group { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .layer-label { display: inline-flex; align-items: center; gap: 4px; cursor: pointer; background: #fff; border: 1px solid #cbd5e1; padding: 2px 6px; border-radius: 4px; user-select: none; font-size: 11px; }
        .layer-label:hover { background: #eef2ff; border-color: #93c5fd; }
        .dot-legend { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        #map-container { flex: 1; width: 100%; min-height: 300px; background: #e5e3df; }
        .btn-fullscreen { background: #0d6efd; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
        .btn-fullscreen:hover { background: #0b5ed7; }
        .btn-action { background: #2563eb; color: #fff; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold; }
        .btn-action:hover { background: #1d4ed8; }
        .btn-secondary { background: #64748b; color: #fff; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 11px; }
        .btn-secondary:hover { background: #475569; }
        .badge-movil { background: #16a34a; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-fijo { background: #64748b; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .status-pill { font-size: 11px; padding: 2px 7px; border-radius: 3px; background: #e2e8f0; color: #334155; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Barra Principal: Interruptores de Capas + Botón Pantalla Completa -->
    <div class="map-header-bar">
        <div class="toolbar-group">
            <strong>Capas:</strong>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Bodega"><span class="dot-legend" style="background:#6f42c1;"></span> Bodegas (<span id="cnt_Bodega">0</span>)</label>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Oficina"><span class="dot-legend" style="background:#20c997;"></span> Oficinas (<span id="cnt_Oficina">0</span>)</label>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Vehiculo"><span class="dot-legend" style="background:#fd7e14;"></span> Vehículos (<span id="cnt_Vehiculo">0</span>)</label>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Persona"><span class="dot-legend" style="background:#007bff;"></span> Personas (<span id="cnt_Persona">0</span>)</label>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Cliente"><span class="dot-legend" style="background:#e83e8c;"></span> Clientes (<span id="cnt_Cliente">0</span>)</label>
            <label class="layer-label"><input type="checkbox" checked class="layer-toggle" data-tipo="Otros"><span class="dot-legend" style="background:#6c757d;"></span> Otros (<span id="cnt_Otros">0</span>)</label>
        </div>
        <div class="toolbar-group">
            <span id="gps_status" class="status-pill">Conectando...</span>
            <?php if ($is_fullscreen): ?>
                <span>Compañía: <b><?php echo htmlspecialchars($current_company); ?></b> | Usuario: <b><?php echo htmlspecialchars($current_user); ?></b></span>
                <button type="button" id="btn_fullscreen" class="btn-fullscreen" title="Alternar pantalla completa del navegador">⛶ PANTALLA COMPLETA</button>
            <?php else: ?>
                <button type="button" id="btn_fullscreen" class="btn-fullscreen" title="Abrir mapa desacoplado en una pestaña independiente">⛶ PANTALLA COMPLETA</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Barra de Filtros y Acciones -->
    <div class="map-header-bar" style="background:#ffffff; padding: 4px 10px;">
        <div class="toolbar-group">
            <strong>Filtrar:</strong>
            <select id="filtro_modalidad" style="padding: 2px 4px; font-size: 11px;">
                <option value="">-- Fijos y Móviles --</option>
                <option value="movil">Solo Móviles (GPS)</option>
                <option value="fijo">Solo Fijos</option>
            </select>
            <input type="text" id="map_search" placeholder="Nombre, código, placa o IMEI..." style="padding: 2px 6px; font-size: 11px; width: 185px;">
            <button type="button" id="btn_buscar" class="btn-action">Buscar</button>
            <button type="button" id="btn_fit" class="btn-secondary" title="Encuadrar todos los pines visibles">🎯 Centrar Todo</button>
            <button type="button" id="btn_reload" class="btn-secondary">🔄 Actualizar</button>
        </div>
        <div class="toolbar-group">
            <label style="cursor:pointer; font-size:11px;"><input type="checkbox" id="chk_autorefresh" checked> Auto-actualizar cada <b><?php echo $sync_interval; ?>s</b></label>
        </div>
    </div>

    <!-- Contenedor del Mapa -->
    <div id="map-container"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var SYNC_INTERVAL_MS = <?php echo $sync_interval * 1000; ?>;
        var map = L.map('map-container').setView([-0.242542, -79.171127], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var layerGroups = {
            'Bodega':   L.layerGroup().addTo(map),
            'Oficina':  L.layerGroup().addTo(map),
            'Vehiculo': L.layerGroup().addTo(map),
            'Persona':  L.layerGroup().addTo(map),
            'Cliente':  L.layerGroup().addTo(map),
            'Otros':    L.layerGroup().addTo(map)
        };

        var allItems = [];

        function getCategoryStyle(tipo, esMovil, estadoGps) {
            var colors = {
                'Bodega':   '#6f42c1',
                'Oficina':  '#20c997',
                'Vehiculo': '#fd7e14',
                'Persona':  '#007bff',
                'Cliente':  '#e83e8c',
                'Otros':    '#6c757d'
            };
            var bg = colors[tipo] || '#6c757d';
            var ring = esMovil 
                ? (estadoGps === 'online' ? '3px solid #16a34a' : '3px solid #dc2626') 
                : '2px solid #ffffff';

            return L.divIcon({
                className: 'erp-geo-pin',
                html: `<div style="background:${bg}; width:16px; height:16px; border-radius:50%; border:${ring}; box-shadow:0 1px 5px rgba(0,0,0,0.45);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10],
                popupAnchor: [0, -10]
            });
        }

        function updateCounters(items) {
            var counts = { 'Bodega': 0, 'Oficina': 0, 'Vehiculo': 0, 'Persona': 0, 'Cliente': 0, 'Otros': 0 };
            items.forEach(item => {
                var key = counts.hasOwnProperty(item.tipo) ? item.tipo : 'Otros';
                counts[key]++;
            });
            Object.keys(counts).forEach(k => {
                var el = document.getElementById('cnt_' + k);
                if (el) el.textContent = counts[k];
            });
        }

        function renderMarkers(items, shouldFitBounds) {
            Object.keys(layerGroups).forEach(k => layerGroups[k].clearLayers());
            updateCounters(items);

            var bounds = L.latLngBounds();
            var visibleCount = 0;

            items.forEach(item => {
                var tipoKey = layerGroups.hasOwnProperty(item.tipo) ? item.tipo : 'Otros';
                var layer   = layerGroups[tipoKey];

                var badge = item.es_movil
                    ? `<span class="badge-movil">MÓVIL (${item.estado_gps === 'online' ? 'En línea' : 'Sin señal'})</span>`
                    : `<span class="badge-fijo">FIJO</span>`;

                var extraInfo = item.es_movil
                    ? `<b>Dispositivo/IMEI:</b> ${item.device_id}<br/>
                       <b>Velocidad:</b> ${item.velocidad} | <b>Batería:</b> ${item.bateria}<br/>`
                    : (item.responsable ? `<b>Detalle/Sucursal:</b> ${item.responsable}<br/>` : '');

                var popupHtml = `
                    <div style="min-width:205px; line-height:1.4;">
                        <div style="font-size:13px; font-weight:bold; margin-bottom:3px;">${item.nombre}</div>
                        ${badge} <span style="color:#555; font-size:11px;">• ${item.tipo} (Cód: ${item.codigo})</span>
                        <hr style="margin:5px 0; border:0; border-top:1px solid #ddd;"/>
                        ${extraInfo}
                        <b>Coords:</b> ${item.lat.toFixed(6)}, ${item.lng.toFixed(6)}<br/>
                        <small style="color:#666;"><b>Actualizado:</b> ${item.ultima_actividad}</small>
                    </div>
                `;

                var marker = L.marker([item.lat, item.lng], {
                    icon: getCategoryStyle(tipoKey, item.es_movil, item.estado_gps)
                }).bindPopup(popupHtml);

                layer.addLayer(marker);

                if (map.hasLayer(layer)) {
                    bounds.extend([item.lat, item.lng]);
                    visibleCount++;
                }
            });

            if (shouldFitBounds && visibleCount > 0) {
                map.fitBounds(bounds, { padding: [35, 35], maxZoom: 15 });
            }
        }

        function applyFilters(shouldFitBounds) {
            var q   = document.getElementById('map_search').value.toLowerCase().trim();
            var mod = document.getElementById('filtro_modalidad').value;

            var filtered = allItems.filter(item => {
                var matchMod = true;
                if (mod === 'movil') matchMod = item.es_movil === true;
                if (mod === 'fijo')  matchMod = item.es_movil === false;

                var matchText = q === '' ||
                    (item.nombre && item.nombre.toLowerCase().includes(q)) ||
                    (item.codigo && String(item.codigo).toLowerCase().includes(q)) ||
                    (item.device_id && item.device_id.toLowerCase().includes(q)) ||
                    (item.responsable && item.responsable.toLowerCase().includes(q));

                return matchMod && matchText;
            });

            renderMarkers(filtered, shouldFitBounds);
        }

        function loadMapData(fitAfterLoad) {
            var statusEl = document.getElementById('gps_status');
            statusEl.textContent = 'Sincronizando...';

            fetch('get_map_data.php')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        allItems = res.data;
                        applyFilters(fitAfterLoad);

                        if (res.traccar_status === 'connected') {
                            statusEl.textContent = 'GPS Conectado (' + res.moviles_activos + ' móviles / ' + res.total + ' pines)';
                            statusEl.style.background = '#d1fae5';
                            statusEl.style.color = '#065f46';
                        } else if (res.traccar_status === 'not_needed') {
                            statusEl.textContent = 'Puntos Fijos (' + res.total + ' pines)';
                            statusEl.style.background = '#e2e8f0';
                            statusEl.style.color = '#334155';
                        } else {
                            statusEl.textContent = 'Traccar: ' + res.traccar_status;
                            statusEl.style.background = '#fee2e2';
                            statusEl.style.color = '#991b1b';
                            statusEl.title = res.traccar_status;
                        }
                    } else {
                        statusEl.textContent = 'Error: ' + res.message;
                    }
                })
                .catch(err => {
                    console.error(err);
                    statusEl.textContent = 'Error de red';
                });
        }

        // Encender / Apagar capas individuales
        document.querySelectorAll('.layer-toggle').forEach(chk => {
            chk.addEventListener('change', function() {
                var tipo = this.getAttribute('data-tipo');
                if (this.checked) {
                    map.addLayer(layerGroups[tipo]);
                } else {
                    map.removeLayer(layerGroups[tipo]);
                }
            });
        });

        document.getElementById('btn_buscar').addEventListener('click', () => applyFilters(true));
        document.getElementById('filtro_modalidad').addEventListener('change', () => applyFilters(true));
        document.getElementById('map_search').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') applyFilters(true);
        });
        document.getElementById('btn_fit').addEventListener('click', () => applyFilters(true));
        document.getElementById('btn_reload').addEventListener('click', () => loadMapData(false));

        // Botón Pantalla Completa: desacopla en pestaña independiente si está dentro del Dashboard
        document.getElementById('btn_fullscreen').addEventListener('click', function() {
            if (window.self !== window.top) {
                var url = new URL(window.location.href);
                url.searchParams.set('fullscreen', '1');
                window.open(url.toString(), '_blank');
            } else {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            }
        });

        setInterval(function() {
            if (document.getElementById('chk_autorefresh').checked) {
                loadMapData(false);
            }
        }, SYNC_INTERVAL_MS);

        // Recalcular dimensiones del mapa al redimensionar o abrir en pestaña nueva
        window.addEventListener('resize', () => map.invalidateSize());
        setTimeout(() => map.invalidateSize(), 250);

        loadMapData(true);
    </script>
</body>
</html>