// ==========================================
// 1. BASE DE DATOS DESDE EL BACKEND UNIFICADO
// ==========================================
const inventario = window.POS_PRODUCTOS || [];

// ==========================================
// 2. ESTADO DEL CARRITO (Persistencia)
// ==========================================
let carrito = JSON.parse(localStorage.getItem('pos_carrito_actual')) || [];

// ==========================================
// 3. RENDERIZADO DEL CATÁLOGO EN EL POS
// ==========================================
function cargarCatalogo(productosA_Mostrar = inventario) {
    const grid = document.getElementById('grid-productos');
    if (!grid) return;

    if (productosA_Mostrar.length === 0) {
        grid.innerHTML = `<div class="col-span-full text-center py-10 text-gray-500 text-xs">No se encontraron productos coincidentes.</div>`;
        return;
    }

    grid.innerHTML = productosA_Mostrar.map(p => {
        // Renderizado condicional de imagen o icono por defecto
        let elementoVisual = '';
        if (p.foto) {
            elementoVisual = `<div class="w-16 h-16 rounded-xl bg-contain bg-no-repeat bg-center bg-[#111827] mb-2" style="background-image: url('${p.foto}');"></div>`;
        } else {
            const iconoTipo = p.tipo === 'kit' ? 'boxes' : 'package';
            elementoVisual = `
                <div class="p-2.5 bg-[#111827] rounded-xl group-hover:scale-110 transition-transform mb-2 border border-[#374151]">
                    <i data-lucide="${iconoTipo}" class="w-6 h-6 text-cyan-400"></i>
                </div>
            `;
        }

        return `
            <button onclick="agregarAlCarrito('${p.id}')" class="flex flex-col items-center justify-between p-3 bg-[#1F2937]/40 border border-[#374151] rounded-xl hover:border-cyan-500 transition-all text-center aspect-square shadow-sm group active:scale-95">
                ${elementoVisual}
                <div class="w-full flex-1 flex flex-col justify-between">
                    <h3 class="text-[11px] font-bold text-white leading-tight mb-1 line-clamp-2">${p.name}</h3>
                    <div>
                        <p class="text-sm font-black text-cyan-400">$${p.precio.toFixed(2)}</p>
                        <div class="text-[9px] font-bold text-gray-500 bg-[#111827] border border-[#374151] px-2 py-0.5 rounded mt-1 inline-block">
                            Stock: ${p.stock}
                        </div>
                    </div>
                </div>
            </button>
        `;
    }).join('');
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ==========================================
// 4. BÚSQUEDA INSTANTÁNEA Y AUTODETECCIÓN DE SUCURSAL
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    cargarCatalogo();
    actualizarInterfazCarrito();

    // Autodetección y pintado forzado de sucursal por URL (?branch_code=...)
    const urlParams = new URLSearchParams(window.location.search);
    const branchCodeUrl = urlParams.get('branch_code');

    if (branchCodeUrl && typeof window.POS_SUCURSALES !== 'undefined') {
        const sucursalEncontrada = window.POS_SUCURSALES.find(s => parseInt(s.branch_code) === parseInt(branchCodeUrl));
        if (sucursalEncontrada) {
            const selectCliente = document.getElementById('select-cliente');
            const selectSucursal = document.getElementById('select-sucursal');

            if (selectCliente) {
                // 1. Asignar el cliente principal
                selectCliente.value = sucursalEncontrada.debtor_no;
                
                // 2. Construir manualmente las opciones de sucursales para este cliente
                if (selectSucursal) {
                    const sucursalesDelCliente = window.POS_SUCURSALES.filter(s => parseInt(s.debtor_no) === parseInt(sucursalEncontrada.debtor_no));
                    
                    selectSucursal.innerHTML = '<option value="">Seleccione sucursal de entrega</option>';
                    sucursalesDelCliente.forEach(suc => {
                        const selectedAttr = parseInt(suc.branch_code) === parseInt(branchCodeUrl) ? 'selected' : '';
                        selectSucursal.innerHTML += `<option value="${suc.branch_code}" ${selectedAttr}>${suc.branch_ref} - ${suc.br_address || 'Sin dirección'}</option>`;
                    });
                    
                    selectSucursal.removeAttribute('disabled');
                    selectSucursal.value = branchCodeUrl;
                }

                // 3. Ejecutar funciones de lógica global si existen en el entorno
                if (typeof actualizarSucursales === 'function') {
                    // Sobrescribimos por seguridad si la función global limpia el DOM
                    setTimeout(() => {
                        selectCliente.value = sucursalEncontrada.debtor_no;
                        if (typeof actualizarSucursales === 'function') {
                            // Si la función requiere evento change, lo simulamos
                            selectCliente.dispatchEvent(new Event('change'));
                        }
                        const selSuc = document.getElementById('select-sucursal');
                        if (selSuc) {
                            selSuc.value = branchCodeUrl;
                            selSuc.removeAttribute('disabled');
                        }
                    }, 50);
                }
            }
        }
    }

    const inputBusq = document.getElementById('input-busqueda-producto');
    if (inputBusq) {
        inputBusq.addEventListener('input', (e) => {
            const termino = e.target.value.toLowerCase().trim();
            const filtrados = inventario.filter(p => 
                p.name.toLowerCase().includes(termino) || p.id.toLowerCase().includes(termino)
            );
            cargarCatalogo(filtrados);
        });
    }
});

// ==========================================
// 5. LÓGICA DE OPERACIONES DEL CARRITO
// ==========================================
function agregarAlCarrito(id) {
    const producto = inventario.find(p => p.id === id);
    if (!producto) return;

    const itemExistente = carrito.find(item => item.id === id);

    if (itemExistente) {
        if (itemExistente.cantidad < producto.stock) {
            itemExistente.cantidad++;
        } else {
            alert(`Stock máximo alcanzado: ${producto.stock}`);
            return;
        }
    } else {
        carrito.push({ ...producto, cantidad: 1 });
    }
    
    guardarYActualizar();
}

function cambiarCantidad(id, delta) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;

    item.cantidad += delta;

    if (item.cantidad <= 0) {
        carrito = carrito.filter(i => i.id !== id);
    } else {
        const productoBase = inventario.find(p => p.id === id);
        if (item.cantidad > productoBase.stock) {
            item.cantidad = productoBase.stock;
        }
    }

    guardarYActualizar();
}

function guardarYActualizar() {
    localStorage.setItem('pos_carrito_actual', JSON.stringify(carrito));
    actualizarInterfazCarrito();
}

// ==========================================
// 6. RENDERIZADO DEL CARRITO Y TOTALES
// ==========================================
function actualizarInterfazCarrito() {
    const contenedor = document.getElementById('contenedor-carrito');
    const contenedorMovil = document.getElementById('contenedor-carrito-movil');
    
    const totalMonto = document.getElementById('total-monto');
    const totalMontoMovil = document.getElementById('total-monto-movil');
    
    const btnPagar = document.getElementById('btn-pagar');
    const btnPagarMovil = document.getElementById('btn-pagar-movil');
    
    const badgeMovil = document.getElementById('badge-carrito');

    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const totalDinero = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

    if (badgeMovil) {
        if (totalItems > 0) {
            badgeMovil.innerText = totalItems;
            badgeMovil.classList.remove('hidden');
        } else {
            badgeMovil.classList.add('hidden');
        }
    }

    if (carrito.length === 0) {
        const contenidoVacio = `
            <div class="flex flex-col items-center justify-center text-center py-10 text-gray-500 h-full">
                <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-30"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Pedido Vacío</h4>
                <p class="text-[10px] mt-1">Selecciona productos para comenzar</p>
            </div>
        `;
        if (contenedor) contenedor.innerHTML = contenidoVacio;
        if (contenedorMovil) contenedorMovil.innerHTML = contenidoVacio;

        if (totalMonto) totalMonto.innerText = "$0.00";
        if (totalMontoMovil) totalMontoMovil.innerText = "$0.00";

        const btnVacioHtml = 'Carrito Vacío';
        [btnPagar, btnPagarMovil].forEach(btn => {
            if (btn) {
                btn.disabled = true;
                btn.className = "w-full py-4 bg-[#1F2937] text-gray-500 border border-[#374151] font-bold text-xs uppercase rounded-xl cursor-not-allowed";
                btn.innerHTML = btnVacioHtml;
            }
        });
    } else {
        const htmlItems = carrito.map(item => `
            <div class="p-3 bg-[#1F2937]/80 rounded-xl border border-[#374151] shadow-sm flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <div class="pr-2">
                        <h5 class="text-xs font-bold text-white leading-tight">${item.name}</h5>
                        <span class="text-[10px] text-gray-400 font-medium">$${item.precio.toFixed(2)} c/u</span>
                    </div>
                    <button onclick="cambiarCantidad('${item.id}', -${item.cantidad})" class="text-red-400 hover:text-red-300 p-1.5 bg-red-950/30 rounded-lg transition-colors shrink-0">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                
                <div class="flex items-center justify-between border-t border-[#374151]/50 pt-2">
                    <span class="text-sm font-black text-cyan-400">$${(item.precio * item.cantidad).toFixed(2)}</span>
                    
                    <div class="flex items-center bg-[#111827] rounded-lg border border-[#374151] overflow-hidden shadow-inner">
                        <button onclick="cambiarCantidad('${item.id}', -1)" class="w-8 h-7 flex items-center justify-center text-gray-400 hover:bg-[#374151] hover:text-white transition-colors">
                            <i data-lucide="minus" class="w-3 h-3"></i>
                        </button>
                        <span class="w-8 text-center text-xs font-bold text-white border-x border-[#374151] bg-[#1F2937] py-1">${item.cantidad}</span>
                        <button onclick="cambiarCantidad('${item.id}', 1)" class="w-8 h-7 flex items-center justify-center text-cyan-400 hover:bg-[#374151] hover:text-cyan-300 transition-colors">
                            <i data-lucide="plus" class="w-3 h-3"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        if (contenedor) contenedor.innerHTML = htmlItems;
        if (contenedorMovil) contenedorMovil.innerHTML = htmlItems;

        const montoStr = `$${totalDinero.toFixed(2)}`;
        if (totalMonto) totalMonto.innerText = montoStr;
        if (totalMontoMovil) totalMontoMovil.innerText = montoStr;

        [btnPagar, btnPagarMovil].forEach(btn => {
            if (btn) {
                btn.disabled = false;
                btn.removeAttribute('onclick');
                btn.onclick = () => toggleModalCheckout();
                btn.className = "w-full py-4 bg-cyan-400 text-black font-black text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-cyan-500/20 hover:bg-cyan-300 transition-all flex items-center justify-center gap-2 active:scale-95 cursor-pointer";
                btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i> Facturar Pedido';
            }
        });
    }
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}