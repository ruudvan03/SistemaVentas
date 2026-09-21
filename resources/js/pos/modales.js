export function abrirModalProveedor() {
    const modal = document.getElementById('modal-proveedor');
    if (!modal) return;
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('busqueda-prod-prov')?.focus(), 200);
}
export function cerrarModalProveedor() {
    document.getElementById('modal-proveedor')?.classList.add('hidden');
}

export function abrirModalBusqueda() {
    const modal = document.getElementById('modal-busqueda');
    if (!modal) return;
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('input-busqueda-nombre')?.focus(), 200);
}
export function cerrarModalBusqueda() {
    document.getElementById('modal-busqueda')?.classList.add('hidden');
}

export function cerrarModalPeso() {
    document.getElementById('modal-peso')?.classList.add('hidden');
}

export function cerrarModalCobro() {
    document.getElementById('modal-cobro')?.classList.add('hidden');
}

export function cerrarModalRecuperar() {
    document.getElementById('modalVentasEspera')?.classList.add('hidden');
}

export function cerrarTodosLosModales() {
    cerrarModalBusqueda();
    cerrarModalCobro();
    cerrarModalPeso();
    cerrarModalProveedor();
    cerrarModalRecuperar();
    document.getElementById('scanner')?.focus();
}

export function initModales() {
    document.getElementById('btn-abrir-modal-proveedor')?.addEventListener('click', abrirModalProveedor);
    document.getElementById('btn-cerrar-modal-proveedor')?.addEventListener('click', cerrarModalProveedor);
    document.getElementById('btn-abrir-modal-busqueda')?.addEventListener('click', abrirModalBusqueda);
    document.getElementById('btn-cerrar-modal-busqueda')?.addEventListener('click', cerrarModalBusqueda);
    document.getElementById('btn-cerrar-modal-cobro')?.addEventListener('click', cerrarModalCobro);
    document.getElementById('btn-cerrar-modal-recuperar')?.addEventListener('click', cerrarModalRecuperar);
}