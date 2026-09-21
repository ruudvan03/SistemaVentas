@extends('layouts.cajero')

@section('title', 'Gastos del Día | Admin')

@section('content')
<div class="p-8 bg-zinc-50 dark:bg-[#0a0a0a] min-h-screen transition-colors duration-300">

    <header class="mb-10">
        <div class="flex items-center space-x-4">
            <h1 class="text-4xl font-black italic text-zinc-900 dark:text-white uppercase tracking-tighter">GASTOS</h1>
            <h1 class="text-4xl font-black italic text-red-600 uppercase tracking-tighter">DEL DÍA</h1>
        </div>
        <p class="text-zinc-500 dark:text-gray-500 text-[10px] font-bold uppercase tracking-[0.3em] mt-1 ml-1">
            Egresos y gastos registrados hoy — {{ now()->format('d/m/Y') }}
        </p>
    </header>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-bold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 px-4 py-3 rounded-xl text-sm font-bold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tarjeta total --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-red-600 p-6 rounded-2xl text-white shadow-lg col-span-1">
            <p class="text-xs font-black uppercase opacity-70 tracking-widest">Total Gastos Hoy</p>
            <p class="text-4xl font-black italic mt-1">${{ number_format($totalDia, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-zinc-200 dark:border-white/5 shadow-xl col-span-2 flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase text-zinc-400 dark:text-gray-500 tracking-widest mb-1">Registros hoy</p>
                <p class="text-3xl font-black text-zinc-900 dark:text-white">{{ $gastos->count() }}</p>
            </div>
            {{-- Formulario nuevo gasto --}}
            <button onclick="document.getElementById('modal-gasto').classList.remove('hidden')"
                class="bg-red-600 hover:bg-red-500 text-white px-5 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition cursor-pointer">
                + Registrar Gasto
            </button>
        </div>
    </div>

    {{-- Tabla de gastos --}}
    <div class="bg-white dark:bg-[#0d0d0d] rounded-2xl border border-zinc-200 dark:border-white/5 shadow-2xl overflow-hidden">
        <div class="h-1 w-full bg-gradient-to-r from-red-600 to-orange-500"></div>
        <div class="p-6 overflow-x-auto">
            @if($gastos->isEmpty())
                <p class="text-center text-zinc-400 dark:text-gray-600 font-bold italic py-10">Sin gastos registrados hoy.</p>
            @else
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-[10px] text-zinc-400 dark:text-gray-600 uppercase font-black tracking-widest">
                            <th class="px-4 py-3">Hora</th>
                            <th class="px-4 py-3">Descripción</th>
                            <th class="px-4 py-3 text-center">Categoría</th>
                            <th class="px-4 py-3 text-center">Usuario</th>
                            <th class="px-4 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gastos as $gasto)
                        <tr class="bg-zinc-50 dark:bg-white/[0.02] hover:bg-zinc-100 dark:hover:bg-white/5 transition rounded-xl">
                            <td class="px-4 py-3 text-xs font-bold text-zinc-500 dark:text-gray-400 rounded-l-xl">
                                {{ \Carbon\Carbon::parse($gasto->created_at)->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-zinc-800 dark:text-white">
                                {{ $gasto->descripcion }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-orange-500/10 text-orange-600 dark:text-orange-400 text-[10px] font-black px-3 py-1 rounded-full border border-orange-500/20 uppercase">
                                    {{ $gasto->categoria }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-bold text-zinc-500 dark:text-gray-400 text-center">
                                {{ $gasto->usuario ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-red-600 dark:text-red-500 rounded-r-xl">
                                ${{ number_format($gasto->monto, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-right text-xs font-black uppercase text-zinc-400 dark:text-gray-500 tracking-widest">Total del día</td>
                            <td class="px-4 py-4 text-right font-black text-xl text-red-600 dark:text-red-500">${{ number_format($totalDia, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Modal nuevo gasto --}}
<div id="modal-gasto" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-[#0d0d0d] border border-zinc-200 dark:border-white/10 w-full max-w-md rounded-3xl shadow-2xl p-6 relative">
        <button onclick="document.getElementById('modal-gasto').classList.add('hidden')"
            class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-700 dark:hover:text-white font-black text-xl cursor-pointer">✕</button>

        <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white mb-6 uppercase">Registrar Gasto</h2>

        <form action="{{ route('gastos.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-zinc-400 dark:text-gray-500 uppercase tracking-widest mb-1">Descripción</label>
                <input type="text" name="descripcion" required
                    class="w-full bg-zinc-100 dark:bg-black border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white focus:outline-none focus:border-red-500 transition"
                    placeholder="Ej. Pago de luz, compra de bolsas...">
            </div>
            <div>
                <label class="block text-[10px] font-black text-zinc-400 dark:text-gray-500 uppercase tracking-widest mb-1">Monto ($)</label>
                <input type="number" name="monto" step="0.01" min="0.01" required
                    class="w-full bg-zinc-100 dark:bg-black border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white focus:outline-none focus:border-red-500 transition"
                    placeholder="0.00">
            </div>
            <div>
                <label class="block text-[10px] font-black text-zinc-400 dark:text-gray-500 uppercase tracking-widest mb-1">Categoría</label>
                <select name="categoria"
                    class="w-full bg-zinc-100 dark:bg-black border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm font-bold text-zinc-900 dark:text-white focus:outline-none focus:border-red-500 transition cursor-pointer">
                    <option value="GENERAL">General</option>
                    <option value="SERVICIOS">Servicios (luz, agua, internet)</option>
                    <option value="INVENTARIO">Inventario / Mercancía</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>
            <button type="submit"
                class="w-full bg-red-600 hover:bg-red-500 text-white py-3 rounded-xl font-black uppercase tracking-widest text-sm transition cursor-pointer mt-2">
                Guardar Gasto
            </button>
        </form>
    </div>
</div>
@endsection