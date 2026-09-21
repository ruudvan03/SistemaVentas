@extends('layouts.cajero')
@section('title', 'Historial de Caja')
@section('content')

<div class="w-full space-y-8 p-4 md:p-8 transition-colors duration-300">
    {{-- ENCABEZADO --}}
    <div class="border-b border-zinc-200 dark:border-white/5 pb-6">
        <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter uppercase text-zinc-900 dark:text-white">
            HISTORIAL DE <span class="text-red-600">CAJA</span>
        </h2>
        <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em] mt-1 ml-1">
            Cortes realizados por todos los operadores
        </p>
    </div>

    {{-- BUSCADOR --}}
    <div class="flex gap-3 mb-2">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input type="text" id="buscador-caja" placeholder="BUSCAR CAJERO O FECHA..."
                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-[#0d0d0d] border border-zinc-200 dark:border-white/10 rounded-2xl font-black italic uppercase text-sm text-zinc-800 dark:text-white placeholder-zinc-300 dark:placeholder-zinc-700 focus:outline-none focus:border-red-500 transition shadow-sm">
        </div>
    </div>

    {{-- TABLA --}}
    <div class="w-full bg-white dark:bg-[#0d0d0d] rounded-3xl border border-zinc-200 dark:border-white/5 overflow-hidden shadow-2xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-red-600 via-red-900 to-black"></div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-[#0d0d0d] text-zinc-400 dark:text-zinc-500 uppercase text-[9px] tracking-[0.2em] border-b border-zinc-100 dark:border-white/5">
                        <th class="p-5 pl-8 font-black">Operativo</th>
                        <th class="p-5 font-black">Apertura</th>
                        <th class="p-5 font-black">Cierre</th>
                        <th class="p-5 text-right font-black">Esperado</th>
                        <th class="p-5 text-right font-black">Contado</th>
                        <th class="p-5 text-right font-black">Diferencia</th>
                        <th class="p-5 pr-8 text-right font-black">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse($cortes as $corte)
                        <tr class="fila-caja group hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors"
                            data-cajero="{{ strtolower($corte->usuario->nombre ?? '') }}"
                            data-fecha="{{ \Carbon\Carbon::parse($corte->fecha_apertura)->format('d/m/Y') }}">
                            <td class="p-5 pl-8 font-black italic uppercase text-zinc-900 dark:text-white">
                                {{ $corte->usuario->nombre ?? 'N/A' }}
                            </td>
                            <td class="p-5 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                {{ \Carbon\Carbon::parse($corte->fecha_apertura)->format('d/m/Y') }}
                            </td>
                            <td class="p-5 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                {{ $corte->fecha_cierre ? \Carbon\Carbon::parse($corte->fecha_cierre)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="p-5 text-right font-bold text-zinc-700 dark:text-zinc-300">
                                ${{ number_format($corte->total_esperado, 2) }}
                            </td>
                            <td class="p-5 text-right font-bold text-zinc-700 dark:text-zinc-300">
                                ${{ number_format($corte->total_contado, 2) }}
                            </td>
                            <td class="p-5 text-right font-black italic {{ $corte->difference == 0 ? 'text-emerald-500' : ($corte->difference > 0 ? 'text-blue-500' : 'text-red-500') }}">
                                {{ $corte->difference > 0 ? '+' : '' }}${{ number_format($corte->difference, 2) }}
                            </td>
                            <td class="p-5 pr-8 text-right">
                                <a href="{{ route('admin.cajas.show', $corte->id) }}"
                                   class="inline-block px-5 py-2 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-300 rounded-xl text-[10px] font-black uppercase tracking-widest transition-colors hover:bg-red-600 hover:text-white">
                                    VER
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-zinc-400 dark:text-zinc-600 italic text-xs font-bold uppercase">
                                <i class="fas fa-history text-3xl mb-3 block opacity-30"></i>
                                Aún no hay cortes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('buscador-caja').addEventListener('input', function() {
        const texto = this.value.toLowerCase().trim();
        document.querySelectorAll('.fila-caja').forEach(f => {
            const ok = !texto || f.dataset.cajero.includes(texto) || f.dataset.fecha.includes(texto);
            f.style.display = ok ? '' : 'none';
        });
    });
</script>
@endpush
@endsection