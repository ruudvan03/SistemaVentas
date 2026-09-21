import { getConfig } from './state.js';
import { buscarLocal } from './sync.js';
import { agregarAlCarrito } from './carrito.js';
import { cerrarModalBusqueda } from './modales.js';
import { notify } from '../shared/notify.js';

// ── Cámara / ZXing ────────────────────────────────────────────────
let lectorCamara = null;
let streamActivo = null;
let camaraIdx    = 0;
let dispositivos  = [];

async function listarCamaras() {
    try {
        const todos = await navigator.mediaDevices.enumerateDevices();
        dispositivos = todos.filter(d => d.kind === 'videoinput');
        const select = document.getElementById('selector-camara');
        if (!select) return;
        select.innerHTML = dispositivos.map((d, i) =>
            `<option value="${i}">${d.label || 'Cámara ' + (i + 1)}</option>`
        ).join('');
    } catch {}
}

async function abrirCamara(idx = 0) {
    const video  = document.getElementById('camara-video');
    const estado = document.getElementById('camara-estado');
    if (!video) return;

    if (streamActivo) {
        streamActivo.getTracks().forEach(t => t.stop());
        streamActivo = null;
    }

    try {
        const constraints = dispositivos.length
            ? { video: { deviceId: { exact: dispositivos[idx]?.deviceId } } }
            : { video: { facingMode: { ideal: 'environment' } } };

        streamActivo = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = streamActivo;
        await video.play();

        if (estado) estado.textContent = 'Apunta al código de barras';

        if (!window.ZXing) {
            await new Promise((res, rej) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js';
                s.onload = res; s.onerror = rej;
                document.head.appendChild(s);
            });
        }

        const hints = new Map();
        const formatos = [
            window.ZXing.BarcodeFormat.EAN_13,
            window.ZXing.BarcodeFormat.EAN_8,
            window.ZXing.BarcodeFormat.CODE_128,
            window.ZXing.BarcodeFormat.CODE_39,
            window.ZXing.BarcodeFormat.UPC_A,
            window.ZXing.BarcodeFormat.UPC_E,
            window.ZXing.BarcodeFormat.QR_CODE,
        ];
        hints.set(window.ZXing.DecodeHintType.POSSIBLE_FORMATS, formatos);
        hints.set(window.ZXing.DecodeHintType.TRY_HARDER, true);

        lectorCamara = new window.ZXing.BrowserMultiFormatReader(hints);

        lectorCamara.decodeFromVideoElement(video, (resultado) => {
            if (!resultado) return;
            const codigo = resultado.getText();
            cerrarCamara();
            procesarCodigoEscaneado(codigo);
        });

    } catch (err) {
        const msg = err.name === 'NotAllowedError'
            ? 'Permiso de cámara denegado'
            : 'No se pudo acceder a la cámara';
        if (estado) estado.textContent = msg;
        notify('error', msg);
    }
}

function cerrarCamara() {
    if (lectorCamara) { try { lectorCamara.reset(); } catch {} lectorCamara = null; }
    if (streamActivo)  { streamActivo.getTracks().forEach(t => t.stop()); streamActivo = null; }
    document.getElementById('modal-camara')?.classList.add('hidden');
    document.getElementById('scanner')?.focus();
}

function procesarCodigoEscaneado(codigo) {
    const { rutaBuscarProducto } = getConfig();
    notify('info', `Código: ${codigo}`);

    if (!navigator.onLine) {
        const p = buscarLocal(codigo)[0];
        if (p) agregarAlCarrito(p);
        else notify('error', 'No encontrado (Offline)');
        return;
    }

    fetch(`${rutaBuscarProducto}?codigo=${codigo}`)
        .then(res => res.json())
        .then(p => agregarAlCarrito(p))
        .catch(() => {
            const p = buscarLocal(codigo)[0];
            if (p) agregarAlCarrito(p);
            else notify('error', 'Producto no encontrado');
        });
}

function initCamara() {
    document.getElementById('btn-camara')?.addEventListener('click', async () => {
        document.getElementById('modal-camara')?.classList.remove('hidden');
        await listarCamaras();
        await abrirCamara(camaraIdx);
    });

    document.getElementById('btn-cerrar-camara')?.addEventListener('click', cerrarCamara);

    document.getElementById('btn-flip-camara')?.addEventListener('click', async () => {
        if (!dispositivos.length) return;
        camaraIdx = (camaraIdx + 1) % dispositivos.length;
        document.getElementById('selector-camara').value = camaraIdx;
        await abrirCamara(camaraIdx);
    });

    document.getElementById('selector-camara')?.addEventListener('change', async (e) => {
        camaraIdx = parseInt(e.target.value);
        await abrirCamara(camaraIdx);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !document.getElementById('modal-camara')?.classList.contains('hidden')) {
            cerrarCamara();
        }
    });
}

// ── Búsqueda por teclado / nombre ─────────────────────────────────
export function initBusqueda() {
    const scanner     = document.getElementById('scanner');
    const inputNombre = document.getElementById('input-busqueda-nombre');

    scanner?.addEventListener('keypress', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const codigo = scanner.value;
        if (!codigo) return;

        const { rutaBuscarProducto } = getConfig();

        if (!navigator.onLine) {
            const p = buscarLocal(codigo)[0];
            if (p) { agregarAlCarrito(p); scanner.value = ''; }
            else { notify('error', 'No encontrado (Offline)'); scanner.value = ''; }
            return;
        }

        fetch(`${rutaBuscarProducto}?codigo=${codigo}`)
            .then(res => res.json())
            .then(p => { agregarAlCarrito(p); scanner.value = ''; })
            .catch(() => {
                const p = buscarLocal(codigo)[0];
                if (p) agregarAlCarrito(p);
                else notify('error', 'Producto no encontrado');
                scanner.value = '';
            });
    });

    inputNombre?.addEventListener('input', function () {
        const q = this.value;
        if (q.length < 2) return;

        const render = (productos) => {
            let html = '';
            productos.forEach(p => {
                const pData = btoa(JSON.stringify(p));
                html += `<tr class="border-b border-white/5">
                    <td class="p-4 uppercase italic font-black">${p.descripcion}</td>
                    <td class="p-4 text-center text-green-500 font-black">$${parseFloat(p.precio_venta).toFixed(2)}</td>
                    <td class="p-4 text-right">
                        <button data-seleccionar-venta="${pData}" class="bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-black italic">Seleccionar</button>
                    </td>
                </tr>`;
            });
            document.getElementById('resultados-busqueda').innerHTML = html;
        };

        const { rutaBuscarNombre } = getConfig();
        if (!navigator.onLine) render(buscarLocal(q));
        else fetch(`${rutaBuscarNombre}?q=${q}`).then(res => res.json()).then(render).catch(() => render(buscarLocal(q)));
    });

    document.getElementById('resultados-busqueda')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-seleccionar-venta]');
        if (!btn) return;
        agregarAlCarrito(JSON.parse(atob(btn.dataset.seleccionarVenta)));
        cerrarModalBusqueda();
    });

    scanner?.focus();

    document.addEventListener('click', (e) => {
        const modalAbierto = document.querySelector('[id^="modal-"]:not(.hidden)');
        if (modalAbierto) return;
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        scanner?.focus();
    });

    initCamara();
}