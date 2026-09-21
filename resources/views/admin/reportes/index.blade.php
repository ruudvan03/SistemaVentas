@extends('layouts.cajero')
@section('title', 'Reporte de Ventas | Admin')
@section('content')
<div class="p-8 bg-zinc-50 dark:bg-[#0a0a0a] min-h-screen transition-colors duration-300">

    <header class="mb-8">
        <div class="flex items-center space-x-4">
            <h1 class="text-4xl font-black italic text-zinc-900 dark:text-white uppercase tracking-tighter">REPORTES DE</h1>
            <h1 class="text-4xl font-black italic text-red-600 uppercase tracking-tighter">VENTAS</h1>
        </div>
        <p class="text-zinc-500 dark:text-gray-500 text-[10px] font-bold uppercase tracking-[0.3em] mt-1 ml-1">Historial detallado y auditoría de transacciones</p>
    </header>

    {{-- Filtros conectados --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        {{-- Buscador --}}
        <div class="relative flex-1">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" id="buscador-reporte" placeholder="BUSCAR FOLIO, CAJERO..."
                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-[#111] border border-zinc-200 dark:border-white/5 rounded-2xl font-black italic uppercase text-sm text-zinc-800 dark:text-white placeholder-zinc-300 dark:placeholder-zinc-700 focus:outline-none focus:border-red-500 transition shadow-sm">
        </div>
        {{-- Filtro fecha --}}
        <div class="bg-white dark:bg-[#111] border border-zinc-200 dark:border-white/5 rounded-2xl px-4 py-3 shadow-sm flex items-center gap-2">
            <span class="text-[9px] font-black text-zinc-400 uppercase shrink-0">Fecha</span>
            <input type="date" id="filtro-fecha"
                class="bg-transparent text-zinc-800 dark:text-white font-bold text-xs outline-none cursor-pointer">
        </div>
        {{-- Filtro método --}}
        <div class="bg-white dark:bg-[#111] border border-zinc-200 dark:border-white/5 rounded-2xl px-4 py-3 shadow-sm flex items-center gap-2">
            <span class="text-[9px] font-black text-zinc-400 uppercase shrink-0">Método</span>
            <select id="filtro-metodo" class="bg-transparent text-zinc-800 dark:text-white font-bold text-xs outline-none cursor-pointer">
                <option value="">Todos</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>
        {{-- Limpiar --}}
        <button onclick="limpiarFiltros()" class="px-4 py-3 bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/5 rounded-2xl text-[10px] font-black uppercase text-zinc-500 hover:text-red-600 hover:border-red-500/30 transition cursor-pointer">
            Limpiar
        </button>
    </div>

    {{-- Contador de resultados --}}
    <p id="contador-resultados" class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-4 ml-1"></p>

    <div class="bg-white dark:bg-[#0d0d0d] rounded-2xl border border-zinc-200 dark:border-white/5 shadow-2xl relative overflow-hidden transition-colors">
        <div class="h-1 w-full bg-gradient-to-r from-red-600 to-blue-600"></div>
        <div class="p-6 overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-y-2">
                <thead>
                    <tr class="text-[10px] text-zinc-400 dark:text-gray-600 uppercase font-black tracking-widest">
                        <th class="px-4 py-3">Folio / ID</th>
                        <th class="px-4 py-3">Fecha y Hora</th>
                        <th class="px-4 py-3 text-center">Cajero</th>
                        <th class="px-4 py-3 text-center">Método</th>
                        <th class="px-4 py-3 text-right">Total Venta</th>
                        <th class="p-4 text-right italic text-red-600">Zona de Peligro</th>
                    </tr>
                </thead>
                <tbody id="tabla-reportes" class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse($reportes as $venta)
                    @php $metodo = strtolower($venta->tipo_pago ?? 'efectivo'); @endphp
                    <tr class="fila-reporte hover:bg-zinc-50 dark:hover:bg-white/[0.03] transition-all group"
                        data-folio="{{ strtolower($venta->folio ?? $venta->id) }}"
                        data-cajero="{{ strtolower($venta->usuario->nombre ?? '') }}"
                        data-fecha="{{ \Carbon\Carbon::parse($venta->fecha)->format('Y-m-d') }}"
                        data-metodo="{{ $metodo }}">
                        <td class="px-4 py-5 font-mono text-blue-600 dark:text-blue-500 text-xs font-bold italic">
                            #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-5">
                            <div class="text-zinc-900 dark:text-white font-black text-sm uppercase tracking-tighter">
                                {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                            </div>
                            <div class="text-[10px] text-zinc-400 dark:text-gray-500 font-bold italic">
                                {{ \Carbon\Carbon::parse($venta->fecha)->format('H:i A') }}
                            </div>
                        </td>
                        <td class="px-4 py-5 text-center">
                            <span class="inline-flex items-center space-x-2 px-3 py-1 bg-zinc-100 dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-white/10">
                                <i class="fas fa-user text-[10px] text-red-500"></i>
                                <span class="text-zinc-800 dark:text-white font-black text-[10px] uppercase">
                                    {{ $venta->usuario->nombre ?? 'N/A' }}
                                </span>
                            </span>
                        </td>
                        <td class="px-4 py-5 text-center">
                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase
                                {{ $metodo === 'tarjeta' ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20' :
                                   ($metodo === 'transferencia' ? 'bg-purple-500/10 text-purple-500 border border-purple-500/20' :
                                   'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20') }}">
                                {{ $metodo }}
                            </span>
                        </td>
                        <td class="px-4 py-5 text-right">
                            <span class="text-xl font-black text-zinc-900 dark:text-white italic tracking-tighter group-hover:text-green-600 transition-colors">
                                ${{ number_format($venta->total, 2) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @if(auth()->user()->esAdmin())
                                <form id="form-eliminar-{{ $venta->id }}" action="{{ route('ventas.cancelar', $venta->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmarCancelacion({{ $venta->id }})"
                                        class="bg-zinc-100 dark:bg-white/5 hover:bg-red-600 text-zinc-400 dark:text-gray-600 hover:text-white p-3 rounded-xl transition-all font-black text-[10px] uppercase tracking-widest">
                                        Anular
                                    </button>
                                </form>
                            @else
                                <span class="text-[9px] font-black text-zinc-300 dark:text-zinc-700 italic uppercase">Bloqueado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-20 text-center text-zinc-400 italic font-bold">No hay ventas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <p id="sin-resultados-reporte" class="hidden text-center py-12 text-zinc-400 font-black italic uppercase text-sm">🔍 Sin resultados para esos filtros</p>
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <div class="bg-red-600 p-8 rounded-3xl shadow-2xl shadow-red-900/40 text-right min-w-[300px]">
            <span class="block text-[10px] font-black text-white/60 uppercase mb-1 tracking-[0.2em]">Venta Total Acumulada</span>
            <span class="text-4xl font-black text-white italic uppercase tracking-tighter">
                ${{ number_format($reportes->sum('total'), 2) }}
            </span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const filas = document.querySelectorAll('.fila-reporte');
    const contador = document.getElementById('contador-resultados');
    const sinResultados = document.getElementById('sin-resultados-reporte');

    function filtrar() {
        const texto  = document.getElementById('buscador-reporte').value.toLowerCase().trim();
        const fecha  = document.getElementById('filtro-fecha').value;
        const metodo = document.getElementById('filtro-metodo').value.toLowerCase();
        let visibles = 0;

        filas.forEach(f => {
            const ok = (!texto  || f.dataset.folio.includes(texto) || f.dataset.cajero.includes(texto))
                    && (!fecha  || f.dataset.fecha === fecha)
                    && (!metodo || f.dataset.metodo === metodo);
            f.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        });

        contador.textContent = visibles + ' resultado' + (visibles !== 1 ? 's' : '');
        sinResultados.classList.toggle('hidden', visibles > 0);
    }

    function limpiarFiltros() {
        document.getElementById('buscador-reporte').value = '';
        document.getElementById('filtro-fecha').value = '';
        document.getElementById('filtro-metodo').value = '';
        filtrar();
    }

    document.getElementById('buscador-reporte').addEventListener('input', filtrar);
    document.getElementById('filtro-fecha').addEventListener('change', filtrar);
    document.getElementById('filtro-metodo').addEventListener('change', filtrar);
    filtrar();

    function confirmarCancelacion(id) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: '¿ANULAR VENTA?',
            text: 'Esta acción borrará el registro para siempre',
            icon: 'warning',
            showCancelButton: true,
            background: isDark ? '#0d0d0d' : '#ffffff',
            color: isDark ? '#ffffff' : '#18181b',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#27272a' : '#e4e4e7',
            confirmButtonText: 'SÍ, BORRAR',
            cancelButtonText: 'MEJOR NO',
            heightAuto: false,
            customClass: {
                popup: isDark ? 'border border-white/10 rounded-3xl' : 'border border-zinc-200 rounded-3xl',
                title: 'font-black italic uppercase tracking-tighter text-2xl',
                confirmButton: 'font-black uppercase tracking-widest text-xs py-3 px-6 rounded-xl',
                cancelButton: 'font-black uppercase tracking-widest text-xs py-3 px-6 rounded-xl'
            }
        }).then(r => { if (r.isConfirmed) document.getElementById('form-eliminar-' + id).submit(); });
    }
</script>
@endpush
@endsection