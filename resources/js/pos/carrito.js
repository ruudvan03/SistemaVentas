import { state } from './state.js';
import { notify } from '../shared/notify.js';

export function agregarAlCarrito(producto) {
    if (producto.unidad_medida === 'kg' || producto.unidad_medida === 'granel') {
        state.productoPendientePeso = producto;
        document.getElementById('peso-producto-nombre').innerText = producto.descripcion;
        document.getElementById('modal-peso').classList.remove('hidden');
        setTimeout(() => document.getElementById('input-peso-valor').focus(), 200);
    } else {
        ejecutarAgregado(producto, 1);
    }
}

export function confirmarPeso() {
    const peso = parseFloat(document.getElementById('input-peso-valor').value);
    if (peso > 0) {
        ejecutarAgregado(state.productoPendientePeso, peso);
        cerrarModalPeso();
    }
}

function ejecutarAgregado(p, cant) {
    const item = state.carrito.find(i => i.id === p.id);
    const precio = parseFloat(p.precio_venta);
    if (item && p.unidad_medida !== 'kg') {
        item.cantidad += cant;
        item.subtotal = item.cantidad * item.precio;
        notify('success', `+1 · ${p.descripcion}`);
    } else {
        state.carrito.push({ id: p.id, descripcion: p.descripcion, precio, cantidad: cant, subtotal: precio * cant });
        notify('success', `✓ ${p.descripcion}`);
    }
    renderizarTabla();
}

export function renderizarTabla() {
    let html = '';
    state.totalVenta = 0;

    state.carrito.forEach((item, index) => {
        state.totalVenta += item.subtotal;
        const esKg = item.unidad_medida === 'kg' || item.unidad_medida === 'granel';
        html += `
        <tr class="border-b border-zinc-100 dark:border-white/5">
            <td class="p-4 pl-6 w-36">
                <div class="flex items-center gap-1">
                    ${!esKg ? `<button data-dec="${index}" class="w-7 h-7 flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-white/10 hover:bg-red-500 hover:text-white text-zinc-500 dark:text-gray-400 font-black text-base transition cursor-pointer select-none">−</button>` : ''}
                    <span
                        data-editar-cant="${index}"
                        title="Clic para editar"
                        class="min-w-[2rem] text-center font-black text-zinc-900 dark:text-white cursor-pointer hover:text-red-600 dark:hover:text-red-500 transition select-none px-1"
                    >${item.cantidad}</span>
                    ${!esKg ? `<button data-inc="${index}" class="w-7 h-7 flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-white/10 hover:bg-emerald-500 hover:text-white text-zinc-500 dark:text-gray-400 font-black text-base transition cursor-pointer select-none">+</button>` : ''}
                </div>
            </td>
            <td class="p-4 uppercase text-zinc-600 dark:text-gray-400 italic">${item.descripcion}</td>
            <td class="p-4 text-center text-green-600 dark:text-green-500 font-black">$${item.precio.toFixed(2)}</td>
            <td class="p-4 text-right text-red-600 dark:text-red-500 font-black">$${item.subtotal.toFixed(2)}</td>
            <td class="p-4 pr-6 text-right w-12">
                <button data-eliminar-item="${index}" class="text-zinc-400 hover:text-red-600 dark:hover:text-red-500 transition-colors text-xl font-black cursor-pointer">&times;</button>
            </td>
        </tr>`;
    });

    document.getElementById('lista-productos').innerHTML = html;
    document.getElementById('total-venta').innerText = state.totalVenta.toFixed(2);
}

export function cambiarCantidad(index, delta) {
    const item = state.carrito[index];
    if (!item) return;
    const nuevaCant = item.cantidad + delta;
    if (nuevaCant <= 0) {
        if (confirm(`¿Quitar "${item.descripcion}" del carrito?`)) {
            state.carrito.splice(index, 1);
        }
    } else {
        item.cantidad = nuevaCant;
        item.subtotal = item.cantidad * item.precio;
    }
    renderizarTabla();
}

export function editarCantidadManual(index) {
    const item = state.carrito[index];
    if (!item) return;

    // Reemplazar el span por un input temporal
    const span = document.querySelector(`[data-editar-cant="${index}"]`);
    if (!span) return;

    const input = document.createElement('input');
    input.type = 'number';
    input.min = '0.001';
    input.step = item.unidad_medida === 'kg' ? '0.001' : '1';
    input.value = item.cantidad;
    input.className = 'w-14 text-center font-black text-red-600 dark:text-red-500 bg-zinc-100 dark:bg-black border-2 border-red-500 rounded-lg p-1 outline-none text-sm';

    span.replaceWith(input);
    input.focus();
    input.select();

    const confirmar = () => {
        const nueva = parseFloat(input.value);
        if (!isNaN(nueva) && nueva > 0) {
            item.cantidad = nueva;
            item.subtotal = nueva * item.precio;
        }
        renderizarTabla();
    };

    input.addEventListener('blur', confirmar);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') confirmar();
        if (e.key === 'Escape') renderizarTabla();
    });
}

export function eliminarItem(i) {
    state.carrito.splice(i, 1);
    renderizarTabla();
}

export function initCarrito() {
    document.getElementById('lista-productos').addEventListener('click', (e) => {
        const btnEliminar = e.target.closest('[data-eliminar-item]');
        const btnInc      = e.target.closest('[data-inc]');
        const btnDec      = e.target.closest('[data-dec]');
        const spanEditar  = e.target.closest('[data-editar-cant]');

        if (btnEliminar) eliminarItem(parseInt(btnEliminar.dataset.eliminarItem));
        if (btnInc)      cambiarCantidad(parseInt(btnInc.dataset.inc), 1);
        if (btnDec)      cambiarCantidad(parseInt(btnDec.dataset.dec), -1);
        if (spanEditar)  editarCantidadManual(parseInt(spanEditar.dataset.editarCant));
    });

    document.getElementById('confirmar-peso-btn')?.addEventListener('click', confirmarPeso);
}

function cerrarModalPeso() {
    document.getElementById('modal-peso').classList.add('hidden');
}