import { state, getConfig } from './state.js';
import { notify } from '../shared/notify.js';
import { renderizarTabla } from './carrito.js';
import { cerrarModalCobro } from './modales.js';

export function cobrar() {
    if (state.carrito.length === 0) { notify('info', 'Carrito vacío'); return; }
    document.getElementById('resumen-articulos').innerText = state.carrito.reduce((a, b) => a + (b.cantidad || 1), 0);
    document.getElementById('resumen-total').innerText = state.totalVenta.toFixed(2);
    document.getElementById('modal-cobro').classList.remove('hidden');
    setMetodo('efectivo');
}

export function setMetodo(t) {
    state.metodoSeleccionado = t;
    const btnE = document.getElementById('btn-efectivo');
    const btnT = document.getElementById('btn-tarjeta');
    const btnTr = document.getElementById('btn-transferencia');

    [btnE, btnT, btnTr].forEach(b => {
        if (b) b.className = 'border-2 border-white/5 bg-white/5 p-4 rounded-2xl flex flex-col items-center transition text-white opacity-50';
    });

    const btnSel = document.getElementById(`btn-${t}`);
    if (btnSel) {
        btnSel.classList.remove('opacity-50', 'border-white/5', 'bg-white/5');
        btnSel.classList.add(t === 'efectivo' ? 'border-green-500' : 'border-blue-500', 'bg-white/10');
    }

    const contMonto = document.getElementById('container-monto');
    const contFolio = document.getElementById('container-folio');
    const atajos = document.getElementById('atajos-dinero');

    if (t === 'efectivo') {
        contMonto.classList.remove('hidden');
        contFolio.classList.add('hidden');
        atajos.classList.remove('hidden');
        setTimeout(() => document.getElementById('monto-recibido').focus(), 200);
    } else {
        contMonto.classList.add('hidden');
        contFolio.classList.remove('hidden');
        atajos.classList.add('hidden');
        document.getElementById('folio-pago').focus();
    }
}

export function calcularCambio() {
    const rec = parseFloat(document.getElementById('monto-recibido').value) || 0;
    const cambio = rec - state.totalVenta;
    document.getElementById('display-cambio').innerText = '$' + (cambio > 0 ? cambio.toFixed(2) : '0.00');
}

export function sumarMonto(v) {
    const inp = document.getElementById('monto-recibido');
    inp.value = ((parseFloat(inp.value) || 0) + v).toFixed(2);
    calcularCambio();
}

export function finalizarProcesoCobro() {
    const { rutaFinalizar, rutaTicket, csrf } = getConfig();
    let montoRecibido = 0;
    const folio = document.getElementById('folio-pago').value;

    if (state.metodoSeleccionado === 'efectivo') {
        montoRecibido = parseFloat(document.getElementById('monto-recibido').value);
        if (isNaN(montoRecibido) || montoRecibido < state.totalVenta) { notify('error', 'Monto insuficiente'); return; }
    } else {
        montoRecibido = state.totalVenta;
        if (!folio) { notify('warning', 'INGRESE EL FOLIO'); return; }
    }

    const payloadVenta = {
        total: state.totalVenta,
        metodo_pago: state.metodoSeleccionado,
        referencia_pago: folio,
        monto_recibido: montoRecibido,
        cambio: montoRecibido - state.totalVenta,
        productos: state.carrito,
        fecha_local: new Date().toLocaleString()
    };

    const guardarOffline = () => {
        const cola = JSON.parse(localStorage.getItem('cola_ventas_abarrotes')) || [];
        cola.push(payloadVenta);
        localStorage.setItem('cola_ventas_abarrotes', JSON.stringify(cola));
        window.abrirCajonAutomatico?.();
        ejecutarLimpiezaPostVenta();
    };

    if (!navigator.onLine) {
        guardarOffline();
        notify('warning', 'VENTA GUARDADA OFFLINE');
        return;
    }

    fetch(rutaFinalizar, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(payloadVenta)
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.open(`${rutaTicket}/${data.venta_id}`, 'Ticket', 'width=400,height=600');
                window.abrirCajonAutomatico?.();
                ejecutarLimpiezaPostVenta();
            }
        })
        .catch(guardarOffline);
}

function ejecutarLimpiezaPostVenta() {
    state.carrito = [];
    renderizarTabla();
    cerrarModalCobro();
    document.getElementById('scanner').focus();
}

export function initCobro() {
    document.getElementById('monto-recibido')?.addEventListener('input', calcularCambio);

    document.querySelectorAll('[data-sumar-monto]').forEach(btn => {
        btn.addEventListener('click', () => sumarMonto(parseFloat(btn.dataset.sumarMonto)));
    });

    document.getElementById('btn-efectivo')?.addEventListener('click', () => setMetodo('efectivo'));
    document.getElementById('btn-tarjeta')?.addEventListener('click', () => setMetodo('tarjeta'));
    document.getElementById('btn-transferencia')?.addEventListener('click', () => setMetodo('transferencia'));

    document.getElementById('btn-cobrar')?.addEventListener('click', cobrar);
    document.getElementById('btn-finalizar-cobro')?.addEventListener('click', finalizarProcesoCobro);
}