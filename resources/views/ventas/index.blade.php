@extends('layouts.cajero')
@section('title', 'Punto de Venta')

@section('content')

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 10px; }
</style>

<div id="pos-app" class="grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[calc(100vh-120px)] pb-10"
    data-ruta-buscar-producto="{{ url('/ventas/buscar-producto') }}"
    data-ruta-buscar-nombre="{{ url('/ventas/buscar-nombre') }}"
    data-ruta-finalizar="{{ route('ventas.finalizar') }}"
    data-ruta-agregar-stock="{{ route('inventario.agregar-stock') }}"
    data-ruta-ticket="{{ url('/ventas/ticket') }}"
    data-csrf="{{ csrf_token() }}"
>

    {{-- LISTA DE COMPRA --}}
    <div class="lg:col-span-8 bg-white dark:bg-[#0d0d0d] border border-zinc-200 dark:border-white/5 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-zinc-900 dark:text-white transition-all max-h-[calc(100vh-140px)]">
        <div class="p-5 border-b border-zinc-100 dark:border-white/5 bg-zinc-50 dark:bg-white/[0.02] flex justify-between items-center shrink-0">
            <h3 class="text-zinc-500 dark:text-gray-500 font-black uppercase text-xs tracking-[0.3em]">Lista de Compra</h3>
            <span class="bg-red-600/10 text-red-500 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-600/20">
                F1 Activo
            </span>
        </div>
        
        <div class="flex-1 overflow-y-auto custom-scrollbar relative">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white dark:bg-[#0d0d0d] z-20 border-b border-zinc-100 dark:border-white/5 shadow-sm">
                    <tr class="text-zinc-400 dark:text-gray-600 uppercase font-black text-[10px] tracking-[0.2em]">
                        <th class="p-4 pl-6">Cant.</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 text-center">Precio</th>
                        <th class="p-4 text-right">Subtotal</th>
                        <th class="p-4 pr-6 text-right w-16"></th>
                    </tr>
                </thead>
                <tbody id="lista-productos" class="divide-y divide-zinc-100 dark:divide-white/5 font-bold italic text-sm">
                    {{-- Dinámico JS --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- LATERAL DERECHO --}}
    <div class="lg:col-span-4 flex flex-col gap-5 sticky top-4">
        {{-- SCANNER --}}
        <div class="bg-white dark:bg-[#0d0d0d] border border-red-600/30 p-5 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between mb-2">
                <label class="text-red-600 text-[10px] font-black uppercase tracking-[0.4em]">Escáner de Código</label>
                <button id="btn-camara" type="button" title="Escanear con cámara"
                    class="flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white border border-red-600/30 px-3 py-1.5 rounded-xl transition-all cursor-pointer text-[10px] font-black uppercase tracking-widest">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Cámara
                </button>
            </div>
            <input type="text" id="scanner" autofocus autocomplete="off"
                class="w-full bg-zinc-100 dark:bg-black border-b-2 border-red-600 text-red-600 dark:text-red-500 text-4xl p-3 focus:outline-none font-black placeholder-zinc-300 dark:placeholder-zinc-900 transition-all focus:bg-red-600/5 rounded-t-lg"
                placeholder="||||||||||||||||">
        </div>

        {{-- TOTAL --}}
        <div class="bg-red-600 p-6 rounded-2xl text-white shadow-[0_20px_50px_rgba(220,38,38,0.25)] relative overflow-hidden group select-none">
            <div class="absolute -right-4 -top-4 text-white/10 text-9xl font-black italic rotate-12 group-hover:rotate-0 transition-transform pointer-events-none">$</div>
            <p class="text-xs font-black uppercase opacity-70 italic tracking-widest relative z-10">Total a Cobrar</p>
            <div class="flex items-baseline gap-2 mt-1 relative z-10">
                <span class="text-3xl font-bold opacity-80">$</span>
                <span id="total-venta" class="text-6xl xl:text-7xl font-black italic tracking-tighter tabular-nums text-white">0.00</span>
            </div>
        </div>

        {{-- ACCIONES --}}
        <div class="grid grid-cols-1 gap-3">
            <button id="btn-cobrar" type="button" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white p-5 rounded-xl font-black text-xl transition active:scale-95 uppercase italic shadow-lg flex justify-between items-center px-8 cursor-pointer">
                <span>COBRAR</span>
                <span class="opacity-60 text-xs bg-black/20 px-2 py-1 rounded">[F9]</span>
            </button>

            <button id="btn-abrir-recuperar" type="button" class="w-full bg-orange-600 hover:bg-orange-500 text-white p-3.5 rounded-xl font-black text-base transition active:scale-95 uppercase italic shadow-lg flex justify-between items-center px-8 border border-orange-400/20 cursor-pointer">
                <span>RECUPERAR VENTA</span>
                <span class="opacity-60 text-xs bg-black/20 px-2 py-1 rounded">[F2]</span>
            </button>

            <div class="grid grid-cols-2 gap-3">
                <button id="btn-abrir-modal-busqueda" type="button" class="bg-zinc-100 dark:bg-white/5 hover:bg-zinc-200 dark:hover:bg-white/10 text-zinc-600 dark:text-gray-300 p-3 rounded-xl font-black uppercase transition border border-zinc-200 dark:border-white/5 tracking-widest text-[11px] cursor-pointer">[F10] BUSCAR</button>
                <button id="btn-pausar-venta" type="button" class="bg-orange-600/10 hover:bg-orange-600/20 text-orange-600 dark:text-orange-500 p-3 rounded-xl font-black uppercase transition border border-orange-600/20 tracking-widest text-[11px] cursor-pointer">[F4] ESPERA</button>
            </div>

            <button id="btn-abrir-modal-proveedor" type="button" class="w-full bg-blue-600/10 dark:bg-blue-900/20 hover:bg-blue-600/20 dark:hover:bg-blue-900/40 text-blue-600 dark:text-blue-400 p-3 rounded-xl font-black text-xs transition uppercase tracking-[0.2em] border border-blue-500/20 cursor-pointer">
                [F8] ENTRADA PROVEEDOR
            </button>
        </div>
    </div>

</div>

@endsection

{{-- INYECCIÓN DE MODALES FUERA DEL MAIN Y DEL SIDEBAR --}}
@push('modals')

{{-- 1. MODAL COBRO --}}
<div id="modal-cobro" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-white dark:bg-[#0d0d0d] border border-zinc-200 dark:border-white/10 w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl p-6 relative mx-auto my-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <p class="text-zinc-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">Artículos: <span id="resumen-articulos" class="text-zinc-900 dark:text-white">0</span></p>
                <h2 class="text-3xl font-black text-zinc-900 dark:text-white italic">TOTAL:</h2>
            </div>
            <div class="text-right">
                <span class="text-5xl font-black text-emerald-600 dark:text-emerald-500 italic">$<span id="resumen-total">0.00</span></span>
            </div>
        </div>

        <div class="bg-zinc-50 dark:bg-white/5 border border-zinc-100 dark:border-white/5 rounded-2xl p-4 mb-6 flex justify-between items-center">
            <span class="text-zinc-400 dark:text-gray-400 font-black uppercase text-xs tracking-widest">Su Cambio:</span>
            <span id="display-cambio" class="text-3xl font-black text-amber-500 italic">$0.00</span>
        </div>

        <p class="text-[10px] font-black text-zinc-400 dark:text-gray-600 uppercase mb-2 tracking-widest">Método de Pago</p>
        <div class="grid grid-cols-3 gap-3 mb-6">
            <button id="btn-efectivo" type="button" class="border-2 border-emerald-500 bg-emerald-500/10 p-3 rounded-2xl flex flex-col items-center transition cursor-pointer">
                <span class="text-emerald-600 dark:text-emerald-400 font-black uppercase italic text-xs">Efectivo</span>
            </button>
            <button id="btn-tarjeta" type="button" class="border-2 border-zinc-100 dark:border-white/5 bg-zinc-50 dark:bg-white/5 p-3 rounded-2xl flex flex-col items-center transition text-zinc-500 dark:text-white cursor-pointer">
                <span class="text-zinc-400 dark:text-gray-400 font-black uppercase italic text-xs">Tarjeta</span>
            </button>
            <button id="btn-transferencia" type="button" class="border-2 border-zinc-100 dark:border-white/5 bg-zinc-50 dark:bg-white/5 p-3 rounded-2xl flex flex-col items-center transition text-zinc-500 dark:text-white cursor-pointer">
                <span class="text-zinc-400 dark:text-gray-400 font-black uppercase italic text-xs">Transf.</span>
            </button>
        </div>

        <div class="mb-6">
            <div id="container-monto">
                <p class="text-[10px] font-black text-zinc-400 dark:text-gray-600 uppercase mb-2 tracking-widest">Monto Recibido</p>
                <input type="number" id="monto-recibido" step="any"
                    class="w-full bg-zinc-100 dark:bg-black border-2 border-zinc-200 dark:border-white/10 rounded-2xl p-4 text-4xl font-black text-zinc-900 dark:text-white focus:border-emerald-500 outline-none transition text-center italic"
                    placeholder="0.00">
            </div>
            <div id="container-folio" class="hidden">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-500 uppercase mb-2 tracking-widest">Folio de Operación</p>
                <input type="text" id="folio-pago"
                    class="w-full bg-zinc-100 dark:bg-black border-2 border-blue-500/30 rounded-2xl p-4 text-3xl font-black text-zinc-900 dark:text-white focus:border-blue-500 outline-none transition text-center italic uppercase"
                    placeholder="EJ. 123456">
            </div>
        </div>

        <div id="atajos-dinero" class="grid grid-cols-4 gap-2 mb-6 text-xs font-black">
            <button data-sumar-monto="50" type="button" class="bg-zinc-100 dark:bg-white/5 p-3 rounded-xl text-zinc-600 dark:text-gray-300 hover:text-red-600 transition cursor-pointer">$50</button>
            <button data-sumar-monto="100" type="button" class="bg-zinc-100 dark:bg-white/5 p-3 rounded-xl text-zinc-600 dark:text-gray-300 hover:text-red-600 transition cursor-pointer">$100</button>
            <button data-sumar-monto="200" type="button" class="bg-zinc-100 dark:bg-white/5 p-3 rounded-xl text-zinc-600 dark:text-gray-300 hover:text-red-600 transition cursor-pointer">$200</button>
            <button data-sumar-monto="500" type="button" class="bg-zinc-100 dark:bg-white/5 p-3 rounded-xl text-zinc-600 dark:text-gray-300 hover:text-red-600 transition cursor-pointer">$500</button>
        </div>

        <div class="flex gap-3">
            <button id="btn-cerrar-modal-cobro" type="button" class="flex-1 bg-zinc-100 dark:bg-white/5 text-zinc-500 dark:text-gray-400 p-4 rounded-2xl font-black uppercase hover:bg-zinc-200 dark:hover:bg-white/10 transition cursor-pointer">Cancelar</button>
            <button id="btn-finalizar-cobro" type="button" class="flex-[2] bg-emerald-600 text-white p-4 rounded-2xl font-black uppercase shadow-lg hover:bg-emerald-500 transition italic cursor-pointer">FINALIZAR VENTA</button>
        </div>
    </div>
</div>

{{-- 2. MODAL BÚSQUEDA --}}
<div id="modal-busqueda" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-white dark:bg-[#0d0d0d] border-2 border-red-600/50 w-full max-w-2xl rounded-3xl p-6 shadow-2xl relative mx-auto my-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-red-600 font-black text-xl uppercase italic">Buscador <span class="text-zinc-900 dark:text-white">Express</span></h3>
            <button id="btn-cerrar-modal-busqueda" type="button" class="text-zinc-400 dark:text-gray-500 font-black uppercase text-xs hover:text-red-600 cursor-pointer">CERRAR [ESC]</button>
        </div>
        <input type="text" id="input-busqueda-nombre"
            class="w-full bg-zinc-100 dark:bg-black border border-zinc-200 dark:border-white/10 text-zinc-900 dark:text-white text-2xl p-4 rounded-2xl focus:border-red-600 outline-none uppercase font-black italic mb-4"
            placeholder="ESCRIBE NOMBRE...">
        <div class="max-h-[300px] overflow-y-auto custom-scrollbar">
            <table class="w-full text-left">
                <tbody id="resultados-busqueda" class="divide-y divide-zinc-100 dark:divide-white/5 text-zinc-900 dark:text-white font-bold text-sm"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- 3. MODAL PROVEEDOR --}}
<div id="modal-proveedor" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-white dark:bg-[#1a1a1a] w-full max-w-5xl rounded-[2.5rem] overflow-hidden shadow-2xl border border-gray-100 dark:border-white/5 relative mx-auto my-auto">
        <div class="p-6 flex justify-between items-center border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
            <h2 class="text-2xl font-black italic uppercase tracking-tighter text-zinc-800 dark:text-white">
                Entrada de <span class="text-red-600">Mercancía</span>
            </h2>
            <button id="btn-cerrar-modal-proveedor" type="button" class="text-gray-400 hover:text-red-600 transition-all p-2 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex flex-col md:flex-row gap-6 p-6">
            <div class="w-full md:w-1/3 space-y-4">
                <div>
                    <label class="block text-xs font-black uppercase text-zinc-400 mb-2 italic tracking-widest">Proveedor / Factura</label>
                    <input type="text" id="prov-nombre"
                        class="w-full bg-blue-50 dark:bg-blue-900/20 border-0 rounded-2xl p-3 font-black uppercase italic text-zinc-700 dark:text-blue-200 text-lg focus:ring-4 focus:ring-blue-500/20 shadow-sm outline-none"
                        placeholder="EJ. BIMBO">
                </div>
                <div class="relative">
                    <label class="block text-xs font-black uppercase text-zinc-400 mb-2 italic tracking-widest">Buscar Producto</label>
                    <input type="text" id="busqueda-prod-prov"
                        class="w-full bg-zinc-100 dark:bg-white/5 border-0 rounded-2xl p-3 font-black uppercase italic text-zinc-700 dark:text-white text-lg focus:ring-4 focus:ring-red-500/20 shadow-sm outline-none"
                        placeholder="ESCRIBE NOMBRE...">
                    <div id="sugerencias-prov" class="absolute z-20 w-full bg-white dark:bg-[#2a2a2a] shadow-2xl rounded-2xl mt-2 hidden max-h-60 overflow-y-auto p-2 border border-gray-100 dark:border-white/10"></div>
                </div>
                <div id="info-producto-prov" class="hidden bg-white dark:bg-white/5 border-2 border-red-500 rounded-[2rem] p-4 shadow-2xl space-y-3">
                    <p id="prov-nombre-display" class="font-black uppercase italic text-red-600 dark:text-red-500 text-base border-b border-gray-100 dark:border-white/10 pb-2"></p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-zinc-400 uppercase mb-1">Cantidad</label>
                            <input type="number" id="prov-cantidad" value="1" step="0.001"
                                class="w-full bg-zinc-50 dark:bg-white/5 border-0 rounded-xl p-3 font-black text-center text-xl text-zinc-800 dark:text-white focus:ring-0 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-zinc-400 uppercase mb-1">Costo Unit.</label>
                            <input type="number" id="prov-costo-total" step="0.01"
                                class="w-full bg-zinc-50 dark:bg-white/5 border-0 rounded-xl p-3 font-black text-center text-xl text-emerald-600 dark:text-emerald-400 focus:ring-0 outline-none"
                                placeholder="0.00">
                        </div>
                    </div>
                    <button id="btn-agregar-lista-prov" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-black italic py-3 rounded-xl transition-all shadow-lg active:scale-95 uppercase text-sm cursor-pointer">
                        + AGREGAR A LA LISTA
                    </button>
                </div>
            </div>
            <div class="w-full md:w-2/3 bg-white dark:bg-white/5 rounded-[2rem] border border-gray-100 dark:border-white/5 overflow-hidden flex flex-col shadow-inner">
                <div class="bg-zinc-900 p-3 flex justify-between items-center">
                    <span class="text-xs font-black uppercase text-zinc-400 italic tracking-widest">Lista de Carga</span>
                    <span id="contador-items-prov" class="bg-red-600 text-white text-[10px] px-3 py-1 rounded-full font-black uppercase italic">0 PRODUCTOS</span>
                </div>
                <div class="flex-1 overflow-y-auto min-h-[250px] max-h-[350px]">
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-zinc-50 dark:bg-[#252525] text-[10px] font-black uppercase text-zinc-400 border-b border-gray-100 dark:border-white/5">
                            <tr>
                                <th class="p-3 italic">CANT</th>
                                <th class="p-3 italic">DESCRIPCIÓN</th>
                                <th class="p-3 text-right italic">COSTO U.</th>
                                <th class="p-3 text-right italic">SUBTOTAL</th>
                                <th class="p-3 w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="lista-items-prov" class="text-base font-black uppercase italic text-zinc-800 dark:text-zinc-200"></tbody>
                    </table>
                </div>
                <div class="p-4 bg-zinc-50 dark:bg-black/20 border-t border-gray-100 dark:border-white/5">
                    <button id="btn-guardar-stock" type="button" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black italic py-4 rounded-2xl transition-all shadow-xl uppercase tracking-widest active:scale-95 text-lg cursor-pointer">
                        GUARDAR EN INVENTARIO
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. MODAL ESPERA --}}
<div id="modalVentasEspera" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-white dark:bg-[#0d0d0d] w-full max-w-xl rounded-[2.5rem] shadow-2xl p-6 border border-zinc-200 dark:border-white/10 relative mx-auto my-auto">
        <h3 class="text-2xl font-black italic text-zinc-900 dark:text-white uppercase mb-4 tracking-tighter">
            Ventas en <span class="text-red-600">Espera</span>
        </h3>
        <div class="bg-zinc-50 dark:bg-white/5 rounded-3xl p-2 border border-zinc-100 dark:border-white/5 max-h-[350px] overflow-y-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <tbody id="listaVentasEspera" class="divide-y divide-zinc-200 dark:divide-white/5"></tbody>
            </table>
        </div>
        <button id="btn-cerrar-modal-recuperar" type="button" class="mt-4 w-full bg-zinc-100 dark:bg-white/10 p-4 text-zinc-500 dark:text-zinc-400 font-black uppercase rounded-2xl hover:bg-red-600 hover:text-white transition-all text-base italic tracking-widest cursor-pointer">
            Cerrar Ventana
        </button>
    </div>
</div>

{{-- 5. MODAL PESO --}}
<div id="modal-peso" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-white dark:bg-[#111] border border-zinc-200 dark:border-white/10 w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl relative mx-auto my-auto">
        <h2 id="peso-producto-nombre" class="text-base font-black text-zinc-900 dark:text-white uppercase italic mb-3">Producto Granel</h2>

        <input type="number" id="input-peso-valor" step="0.001"
            class="w-full bg-zinc-100 dark:bg-black border-2 border-zinc-200 dark:border-white/10 rounded-2xl p-4 text-4xl font-black text-emerald-600 dark:text-emerald-500 text-center italic mb-4 outline-none focus:border-red-600"
            placeholder="0.000">

        <div class="flex items-center justify-between mb-4 px-1">
            <span id="bascula-estado" class="text-xs font-bold uppercase text-zinc-400 dark:text-zinc-500">
                Báscula desconectada
            </span>
            <button id="bascula-conectar-btn" type="button"
                class="text-xs font-black uppercase italic text-emerald-600 dark:text-emerald-500 hover:underline cursor-pointer">
                Conectar báscula
            </button>
        </div>

        <div class="flex gap-3">
            <button id="confirmar-peso-btn" type="button" class="w-full bg-red-600 text-white p-3.5 rounded-2xl font-black uppercase italic shadow-lg hover:bg-red-700 transition cursor-pointer">Confirmar Peso</button>
        </div>
    </div>
</div>

{{-- 6. MODAL CÁMARA --}}
<div id="modal-camara" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4 w-screen h-screen">
    <div class="bg-[#0d0d0d] border border-white/10 w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl relative mx-auto my-auto">

        <div class="flex justify-between items-center px-5 py-4 border-b border-white/5">
            <div>
                <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">Cámara activa</p>
                <h3 class="text-white font-black italic uppercase text-lg">Escanear código</h3>
            </div>
            <button id="btn-cerrar-camara" type="button"
                class="text-zinc-500 hover:text-white transition text-2xl font-black cursor-pointer leading-none">&times;</button>
        </div>

        {{-- Visor de cámara --}}
        <div class="relative bg-black" style="aspect-ratio: 4/3;">
            <video id="camara-video" class="w-full h-full object-cover" playsinline muted></video>

            {{-- Línea de escaneo animada --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-56 h-40">
                    {{-- Esquinas --}}
                    <span class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-red-500 rounded-tl-lg"></span>
                    <span class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-red-500 rounded-tr-lg"></span>
                    <span class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-red-500 rounded-bl-lg"></span>
                    <span class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-red-500 rounded-br-lg"></span>
                    {{-- Línea de escaneo --}}
                    <div id="linea-escaneo" class="absolute left-2 right-2 h-0.5 bg-red-500/70 shadow-[0_0_6px_rgba(239,68,68,0.8)]" style="top: 50%; animation: scan 2s ease-in-out infinite;"></div>
                </div>
            </div>

            {{-- Estado --}}
            <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                <span id="camara-estado" class="bg-black/60 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full">
                    Apunta al código de barras
                </span>
            </div>
        </div>

        {{-- Selector de cámara si hay varias --}}
        <div class="p-4 flex items-center justify-between gap-3">
            <select id="selector-camara"
                class="flex-1 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-white text-xs font-bold outline-none cursor-pointer">
            </select>
            <button id="btn-flip-camara" type="button" title="Cambiar cámara"
                class="p-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-zinc-400 hover:text-white transition cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes scan {
    0%, 100% { top: 10%; }
    50%       { top: 85%; }
}
</style>

@endpush