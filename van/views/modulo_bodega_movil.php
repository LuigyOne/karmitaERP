<?php
// Requerir el helper unificado de imágenes
require_once __DIR__ . '/../backend/imagen_helper.php';

// Asegurar el prefijo de tablas vacío
if (!defined('TB_PREF')) {
    define('TB_PREF', '');
}

// Obtener la bodega móvil asignada desde la sesión del login o por parámetro GET opcional
$loc_code = isset($_GET['loc_code']) ? trim($_GET['loc_code']) : ($_SESSION['bodega_movil'] ?? 'VAN01');
$nombre_bodega =$_SESSION['bodega_nombre'] ?? 'Bodega Móvil';

$GLOBAL_COMPANY_COY_NO   = $_SESSION['company_coy_name'] ?? $_SESSION['company_coy_no'] ?? 'SANCHEZ VERA DISTRIBUCION';
$GLOBAL_COMPANY_RUC      =$_SESSION['company_ruc'] ?? '1759318114001';

try {
    $db = BaseDatos::obtenerConexion();

    // Si se pasa una bodega por GET distinta, actualizar el nombre correspondiente
    if (isset($_GET['loc_code'])) {
        $stmt_loc =$db->prepare("SELECT location_name FROM " . TB_PREF . "locations WHERE loc_code = :loc_code LIMIT 1");
        $stmt_loc->execute(['loc_code' =>$loc_code]);
        $res_loc =$stmt_loc->fetch(PDO::FETCH_ASSOC);
        if ($res_loc) {
            $nombre_bodega =$res_loc['location_name'];
        }
    }

    // Consulta SQL basada en NotrinosERP (stock_moves + voided) para calcular stock real por operaciones
    $sql = "SELECT sm.stock_id, sm.description, sm.units,
                   COALESCE(SUM(CASE WHEN voided.id IS NULL THEN move.qty ELSE 0 END), 0) as qoh,
                   COALESCE(p.price, 0) as precio_base,
                   COALESCE(t.rate, 0.00) as tasa_iva
            FROM " . TB_PREF . "stock_master sm
            INNER JOIN " . TB_PREF . "stock_moves move ON sm.stock_id = move.stock_id AND move.loc_code = :loc_code
            LEFT JOIN " . TB_PREF . "voided voided ON voided.type = move.type AND voided.id = move.trans_no
            LEFT JOIN " . TB_PREF . "prices p ON sm.stock_id = p.stock_id AND p.sales_type_id = 1
            LEFT JOIN " . TB_PREF . "stock_category sc ON sm.category_id = sc.category_id
            LEFT JOIN " . TB_PREF . "item_tax_types itt ON sc.dflt_tax_type = itt.id
            LEFT JOIN " . TB_PREF . "tax_types t ON t.rate = CASE WHEN itt.name LIKE '%15%' THEN 15 ELSE 0 END
            WHERE sm.inactive = 0
            GROUP BY sm.stock_id, sm.description, sm.units, p.price, t.rate
            HAVING qoh > 0
            ORDER BY sm.description ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute(['loc_code' =>$loc_code]);
    $productos_stock =$stmt->fetchAll(PDO::FETCH_ASSOC);

    // Listado de bodegas para selector rápido en caso de cambiar de VAN
    $stmt_todas =$db->query("SELECT loc_code, location_name FROM " . TB_PREF . "locations WHERE inactive = 0");
    $todas_bodegas =$stmt_todas->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $productos_stock = [];$todas_bodegas = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Auto-Venta - Inventario Bodega Móvil</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased select-none flex flex-col">

    <!-- CABECERA -->
    <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 shadow-sm no-print">
        <div class="flex items-center gap-3">
            <a href="index.php?view=catalogo" class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 hover:bg-emerald-100 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-wider">Inventario en Van</h1>
                <p class="text-[10px] text-slate-400 font-bold"><?php echo htmlspecialchars($nombre_bodega); ?></p>
            </div>
        </div>

        <!-- Botón que abre el modal de opciones de impresión -->
        <button onclick="abrirModalImpresion()" class="bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 px-3.5 py-2 rounded-xl flex items-center gap-2 transition-colors shadow-sm font-bold text-xs uppercase">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Imprimir Stock</span>
        </button>
    </header>

    <main class="flex-1 overflow-y-auto p-4 space-y-4 pb-24">

        <!-- SELECTOR DE BODEGA RÁPIDO -->
        <?php if (count($todas_bodegas ?? []) > 1): ?>
        <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3 no-print">
            <i data-lucide="truck" class="w-5 h-5 text-emerald-600"></i>
            <div class="flex-1">
                <label class="block text-[9px] text-slate-400 font-bold uppercase">Cambiar Bodega Móvil:</label>
                <select onchange="window.location.href='index.php?view=bodega_movil&loc_code=' + this.value" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500">
                    <?php foreach ($todas_bodegas as$b): ?>
                        <option value="<?php echo $b['loc_code']; ?>" <?php echo ($b['loc_code'] ===$loc_code) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['location_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <!-- LISTADO DE PRODUCTOS EN STOCK -->
        <div class="space-y-3">
            <?php if (empty($productos_stock)): ?>
                <div class="text-center py-16 text-slate-400 text-sm font-medium bg-white rounded-2xl border border-slate-200 p-6">
                    <i data-lucide="package-open" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                    No hay productos con stock disponible en esta bodega móvil actualmente.
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[10px] text-slate-500 uppercase font-black">
                            <tr>
                                <th class="p-3">Artículo / Descripción</th>
                                <th class="p-3 text-center">Stock Físico (QOH)</th>
                                <th class="p-3 text-right">P. Unit. Estimado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($productos_stock as $item):$foto = obtenerImagenStockUnificada($item['stock_id']);$precio_con_iva = round(floatval($item['precio_base']) * (1 + (floatval($item['tasa_iva']) / 100)), 2);
                            ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-3 flex items-center gap-3">
                                        <?php if ($foto): ?>
                                            <div class="w-10 h-10 rounded-lg bg-cover bg-center shrink-0 border border-slate-200 bg-slate-50" style="background-image: url('<?php echo $foto; ?>');"></div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 text-emerald-600">
                                                <i data-lucide="package" class="w-5 h-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-bold text-slate-900 leading-tight"><?php echo htmlspecialchars($item['description']); ?></p>
                                            <span class="text-[10px] text-slate-400 font-mono font-medium">ID: <?php echo htmlspecialchars($item['stock_id']); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-black rounded-lg border border-emerald-100 shadow-sm inline-block">
                                            <?php echo number_format($item['qoh'], 0); ?> <?php echo htmlspecialchars($item['units'] ?: 'Und'); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-black text-slate-900">
                                        $<?php echo number_format($precio_con_iva, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- MODAL DE SELECCIÓN DE TIPO DE IMPRESIÓN -->
    <div id="modal-impresion" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0" onclick="cerrarModalImpresion()"></div>
        <div class="relative bg-white border border-slate-200 rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 shadow-sm">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Opciones de Impresión</h3>
                        <p class="text-[10px] text-slate-400 font-bold">Seleccione el formato de salida</p>
                    </div>
                </div>
                <button onclick="cerrarModalImpresion()" class="text-slate-400 hover:text-slate-900 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-2 py-1">
                <button onclick="imprimirStock('ticket')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-emerald-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                        <div>
                            <span class="block text-slate-900">Impresora de Tickets (58mm)</span>
                            <span class="text-[9px] text-slate-400 font-normal">Formato estrecho (32 col)</span>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform"></i>
                </button>

                <button onclick="imprimirStock('normal')" class="w-full text-left px-4 py-3 bg-white border border-slate-200 hover:border-emerald-500 rounded-xl text-xs font-bold text-slate-700 hover:text-slate-900 flex items-center justify-between group transition-all shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i>
                        <div>
                            <span class="block text-slate-900">Impresora Normal (A4 / Carta)</span>
                            <span class="text-[9px] text-slate-400 font-normal">Formato ancho, listado completo</span>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform"></i>
                </button>
            </div>

            <button type="button" onclick="cerrarModalImpresion()" class="w-full py-3 bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs uppercase rounded-xl hover:bg-slate-200 transition-all">
                Cancelar
            </button>
        </div>
    </div>

    <!-- MENÚ INFERIOR GLOBAL -->
    <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200 flex justify-around items-center px-1 z-50 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)] no-print">
        <button onclick="window.location.href='index.php?view=ruta'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="map" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Ruta</span>
        </button>
        <button onclick="window.location.href='index.php?view=catalogo'" class="flex flex-col items-center justify-center text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="book-open" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Catálogo</span>
        </button>
        <button onclick="window.location.href='index.php?view=pos'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors relative">
            <div class="bg-emerald-50 p-1.5 rounded-xl border border-emerald-100 shadow-sm">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <span class="text-[9px] font-bold mt-1 uppercase text-emerald-600">Vender</span>
        </button>
        <button onclick="window.location.href='index.php?view=metas'" class="flex flex-col items-center justify-center text-slate-400 hover:text-emerald-600 flex-1 py-1 transition-colors">
            <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
            <span class="text-[9px] font-bold mt-1 uppercase">Metas</span>
        </button>
    </nav>

    <!-- Datos JSON de los productos para la generación dinámica de impresión -->
    <script>
        const datosStock = <?php echo json_encode($productos_stock); ?>;
        const nombreEmpresa = "<?php echo htmlspecialchars($GLOBAL_COMPANY_COY_NO); ?>";
        const rucEmpresa = "<?php echo htmlspecialchars($GLOBAL_COMPANY_RUC); ?>";
        const nombreBodega = "<?php echo htmlspecialchars($nombre_bodega); ?>";
        const codigoBodega = "<?php echo htmlspecialchars($loc_code); ?>";

        lucide.createIcons();

        function abrirModalImpresion() {
            document.getElementById('modal-impresion').classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function cerrarModalImpresion() {
            document.getElementById('modal-impresion').classList.add('hidden');
        }

        function imprimirStock(tipo) {
            cerrarModalImpresion();
            const ahora = new Date();
            const fechaHoraStr = ahora.toLocaleDateString() + ' ' + ahora.toLocaleTimeString();

            if (tipo === 'ticket') {
                // GENERACIÓN DE TICKET DE 32 CARACTERES PARA INTENT NATIVO RAWBT
                let ticket = "";
                ticket += "     SANCHEZ VERA DISTRIBUCION     \n";
                ticket += "       RUC: 1759318114001          \n";
                ticket += "--------------------------------\n";
                ticket += "INVENTARIO BODEGA MOVIL (VAN)   \n";
                ticket += "VAN: " + nombreBodega.substring(0, 26).padEnd(26, ' ') + "\n";
                ticket += "COD: " + codigoBodega.padEnd(26, ' ') + "\n";
                ticket += "FECHA: " + fechaHoraStr.padEnd(24, ' ') + "\n";
                ticket += "--------------------------------\n";
                ticket += "ITEM / DESCRIPCION      QOH P.EST\n";
                ticket += "--------------------------------\n";

                datosStock.forEach(item => {
                    let stockId = item.stock_id.substring(0, 10);
                    let desc = item.description.substring(0, 21).padEnd(21, ' ');
                    let qoh = Number(item.qoh).toFixed(0).padStart(4, ' ');
                    let precioIva = Number(item.precio_base) * (1 + (Number(item.tasa_iva) / 100));
                    let pEst = "$" + precioIva.toFixed(2).padStart(5, ' ');

                    ticket += `[${stockId}]\n`;
                    ticket += `${desc} ${qoh} ${pEst}\n`;
                });

                ticket += "--------------------------------\n";
                ticket += "   CONTROL DE INVENTARIO RUTA   \n";
                ticket += "     ¡GRACIAS POR SU GESTION!   \n\n\n\n";

                // Codificar de manera segura para URL
                let textoCodificado = encodeURIComponent(ticket);

                // Obtener el paquete configurado en localStorage (o usar el default por defecto)
                let defaultPackage = "ru.a402d.rawbtprinter";
                let customPackage = localStorage.getItem('pos_printer_package');
                let targetPackage = (customPackage && customPackage !== "") ? customPackage : defaultPackage;

                // Enviar usando el Intent nativo hacia RawBT
                window.location.href = `intent:/${textoCodificado}#Intent;scheme=rawbt;package=${targetPackage};end;`;

            } else {
                // Formato Normal / Ancho (A4 / Carta)
                const ventanaImpresion = window.open('', '_blank', 'width=800,height=600');
                let contenidoHtml = `
                    <html>
                        <head>
                            <title>Inventario Bodega Móvil - Reporte Normal</title>
                            <style>
                                body { font-family: monospace; padding: 20px; font-size: 12px; color: black; }
                                h2, h3 { text-align: center; margin: 2px 0; }
                                .header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
                                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                                th, td { border: 1px solid #000; padding: 6px 10px; text-align: left; }
                                th { background-color: #eee; }
                                .text-center { text-align: center; }
                                .text-right { text-align: right; }
                            </style>
                        </head>
                        <body onload="window.print(); window.close();">
                            <div class="header">
                                <h2>${nombreEmpresa}</h2>
                                <p class="text-center" style="margin:2px 0;">RUC: ${rucEmpresa}</p>
                                <h3>REPORTE DE STOCK - BODEGA MÓVIL (VAN)</h3>
                                <p><strong>Ubicación / Van:</strong> ${nombreBodega} (Código: ${codigoBodega})</p>
                                <p><strong>Fecha y Hora de Emisión:</strong> ${fechaHoraStr}</p>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Código ID</th>
                                        <th>Descripción del Artículo</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-center">Stock Físico (QOH)</th>
                                        <th class="text-right">Precio Unit. (Est.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                datosStock.forEach(item => {
                    let precioIva = parseFloat(item.precio_base) * (1 + (parseFloat(item.tasa_iva) / 100));
                    contenidoHtml += `
                        <tr>
                            <td><b>${item.stock_id}</b></td>
                            <td>${item.description}</td>
                            <td class="text-center">${item.units || 'Und'}</td>
                            <td class="text-center"><b>${parseFloat(item.qoh).toFixed(0)}</b></td>
                            <td class="text-right">$${precioIva.toFixed(2)}</td>
                        </tr>
                    `;
                });

                contenidoHtml += `
                                </tbody>
                            </table>
                            <div style="margin-top: 30px; font-size: 10px; text-align: center;">
                                <p>_________________________________________</p>
                                <p>Firma de Responsable / Conductor en Ruta</p>
                            </div>
                        </body>
                    </html>
                `;
                ventanaImpresion.document.write(contenidoHtml);
                ventanaImpresion.document.close();
            }
        }
    </script>
</body>
</html>