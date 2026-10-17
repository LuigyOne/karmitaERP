// ==========================================
// LÓGICA DE CLIENTES Y SUCURSALES (POS)
// ==========================================
const sucursalesData = window.POS_SUCURSALES || [];

function actualizarSucursales() {
    const debtorNo = document.getElementById('select-cliente').value;
    const selectSucursal = document.getElementById('select-sucursal');
    const tarjeta = document.getElementById('info-tarjeta-cliente');
    
    if (!selectSucursal) return;
    
    selectSucursal.innerHTML = '<option value="">Seleccione sucursal de entrega</option>';
    
    if (!debtorNo) {
        selectSucursal.disabled = true;
        if (tarjeta) tarjeta.classList.add('hidden');
        return;
    }

    // Caso Consumidor Final
    if (debtorNo === '999') {
        selectSucursal.innerHTML = '<option value="999" data-comercial="CONSUMIDOR FINAL" data-contacto="N/D" data-dir="Matriz Principal" data-tel="0999999999" data-foto="" selected>Principal (Consumidor Final)</option>';
        selectSucursal.disabled = false;
        
        const cardComercial = document.getElementById('card-nombre-comercial');
        if (cardComercial) {
            cardComercial.innerText = "CONSUMIDOR FINAL";
            document.getElementById('card-contacto').innerText = "👤 Contacto: N/D";
            document.getElementById('card-telefono').innerText = "📞 0999999999";
            document.getElementById('card-direccion').innerText = "📍 Matriz Principal";
            document.getElementById('card-foto-local').src = "assets/img/favicon.png";
            tarjeta.classList.remove('hidden');
        }
        return;
    }

    // Filtrar sucursales del cliente seleccionado
    const filtradas = sucursalesData.filter(s => s.debtor_no == debtorNo);
    
    if (filtradas.length > 0) {
        filtradas.forEach(suc => {
            const opt = document.createElement('option');
            opt.value = suc.branch_code;
            opt.textContent = `${suc.branch_ref} — ${suc.customer_name}`;
            opt.setAttribute('data-comercial', suc.branch_ref || suc.br_name);
            opt.setAttribute('data-contacto', suc.contact_name || 'Sin contacto registrado');
            opt.setAttribute('data-dir', suc.br_address || 'Sin dirección');
            opt.setAttribute('data-tel', suc.phone || suc.phone2 || 'Sin teléfono');
            opt.setAttribute('data-foto', suc.foto_1 ? `../company/1/images/branches/${suc.foto_1}` : '');
            selectSucursal.appendChild(opt);
        });
        selectSucursal.disabled = false;
    } else {
        selectSucursal.innerHTML = '<option value="">El cliente no tiene sucursales registradas</option>';
        selectSucursal.disabled = true;
    }
    
    if (tarjeta) tarjeta.classList.add('hidden');
}

function mostrarDetallesSucursal() {
    const selectSuc = document.getElementById('select-sucursal');
    const tarjeta = document.getElementById('info-tarjeta-cliente');
    if (!selectSuc || !tarjeta) return;
    
    const optSelected = selectSuc.options[selectSuc.selectedIndex];

    if (!selectSuc.value) {
        tarjeta.classList.add('hidden');
        return;
    }

    document.getElementById('card-nombre-comercial').innerText = optSelected.getAttribute('data-comercial');
    document.getElementById('card-contacto').innerText = "👤 " + optSelected.getAttribute('data-contacto');
    document.getElementById('card-telefono').innerText = "📞 " + optSelected.getAttribute('data-tel');
    document.getElementById('card-direccion').innerText = "📍 " + optSelected.getAttribute('data-dir');
    
    const fotoUrl = optSelected.getAttribute('data-foto');
    const imgEl = document.getElementById('card-foto-local');
    
    if (imgEl) {
        if (fotoUrl && fotoUrl !== '') {
            imgEl.src = fotoUrl;
            imgEl.style.display = 'block';
        } else {
            imgEl.src = "assets/img/favicon.png";
        }
    }
    
    tarjeta.classList.remove('hidden');
}

// Autodetección robusta al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    const preBranch = window.PRESELECTED_BRANCH;
    if (preBranch && preBranch > 0) {
        const encontrada = sucursalesData.find(s => parseInt(s.branch_code) === parseInt(preBranch));
        if (encontrada) {
            const selectCliente = document.getElementById('select-cliente');
            const selectSucursal = document.getElementById('select-sucursal');
            
            if (selectCliente) {
                // 1. Asignar el cliente al selector superior
                selectCliente.value = encontrada.debtor_no;
                
                // 2. Disparar la carga de sucursales para llenar el combo inferior
                actualizarSucursales();
                
                // 3. Forzar la selección de la sucursal exacta por su ID
                if (selectSucursal) {
                    selectSucursal.value = preBranch;
                    selectSucursal.removeAttribute('disabled');
                    mostrarDetallesSucursal();
                }
            }
        }
    }
});
