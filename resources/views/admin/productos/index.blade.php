@extends('layouts.cajero')

@section('content')
<div class="p-6">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-black italic text-zinc-800 dark:text-white uppercase tracking-tighter">
                Inventario <span class="text-orange-500">Admin</span>
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium uppercase tracking-widest italic">
                Control total por categorias
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Toggle conteo de inventario --}}
            <button id="btn-toggle-stock" onclick="toggleStock()"
                class="flex items-center gap-2 {{ $mostrarStock ? 'bg-zinc-100 dark:bg-white/5 border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-300' : 'bg-orange-500/10 border-orange-500/40 text-orange-500' }} border hover:opacity-80 font-black italic px-4 py-3 rounded-2xl transition-all uppercase text-xs">
                <svg id="icon-ojo" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    @if($mostrarStock)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    @endif
                </svg>
                <span id="label-toggle-stock">{{ $mostrarStock ? 'Conteo activo' : 'Sin conteo' }}</span>
            </button>

            <button onclick="document.getElementById('modalProducto').classList.remove('hidden')"
                class="bg-orange-600 hover:bg-orange-500 text-white font-black italic px-6 py-3 rounded-2xl transition-all shadow-[0_0_20px_rgba(234,88,12,0.3)] uppercase text-xs">
                + Nuevo Producto
            </button>
        </div>
    </div>

    {{-- Buscador y filtro --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input
                type="text"
                id="buscador-inventario"
                placeholder="BUSCAR PRODUCTO O CÓDIGO..."
                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-zinc-900/50 border border-zinc-200 dark:border-white/10 rounded-2xl font-black italic uppercase text-sm text-zinc-800 dark:text-white placeholder-zinc-300 dark:placeholder-zinc-700 focus:outline-none focus:border-orange-500 transition shadow-sm"
            >
        </div>

        {{-- Dropdown custom de departamentos --}}
        <div class="relative" id="wrapper-depto">
            <button type="button" id="btn-depto"
                onclick="toggleDropdownDepto()"
                class="flex items-center gap-3 bg-white dark:bg-zinc-900/50 border border-zinc-200 dark:border-white/10 rounded-2xl px-5 py-3 font-black italic uppercase text-sm text-zinc-700 dark:text-white shadow-sm hover:border-orange-500 transition min-w-[220px] justify-between cursor-pointer">
                <span id="label-depto">Todos los departamentos</span>
                <svg id="chevron-depto" class="w-4 h-4 text-zinc-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="dropdown-depto"
                class="absolute right-0 top-[calc(100%+8px)] z-50 hidden bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden min-w-[240px] max-h-72 overflow-y-auto">

                <button type="button" data-depto-val="" data-depto-label="Todos los departamentos"
                    onclick="seleccionarDepto(this)"
                    class="opcion-depto w-full text-left px-5 py-3 text-xs font-black italic uppercase text-orange-500 bg-orange-500/5 hover:bg-orange-500/10 transition cursor-pointer border-b border-zinc-100 dark:border-white/5">
                    Todos los departamentos
                </button>

                @foreach($departamentos as $depto)
                <button type="button"
                    data-depto-val="{{ $depto->id }}"
                    data-depto-label="{{ $depto->nombre }}"
                    onclick="seleccionarDepto(this)"
                    class="opcion-depto w-full text-left px-5 py-3 text-xs font-black italic uppercase text-zinc-700 dark:text-zinc-300 hover:bg-orange-500/10 hover:text-orange-500 transition cursor-pointer border-b border-zinc-100 dark:border-white/5 last:border-0">
                    {{ $depto->nombre }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- input oculto para el valor real del filtro --}}
        <input type="hidden" id="filtro-departamento" value="">
    </div>

    {{-- Mensaje sin resultados --}}
    <div id="sin-resultados" class="hidden text-center py-16 text-zinc-400 dark:text-zinc-600">
        <p class="text-4xl mb-3">🔍</p>
        <p class="font-black italic uppercase tracking-widest text-sm">Sin resultados para tu búsqueda</p>
    </div>

    {{-- Listado por Departamentos --}}
    @foreach($productos->groupBy('departamento_id') as $deptoId => $productosDepto)
    <div class="mb-10 bloque-depto" data-depto-id="{{ $deptoId }}">
        <h2 class="flex items-center gap-2 mb-4 text-orange-500 font-black italic uppercase tracking-widest text-sm">
            <span class="h-[2px] w-8 bg-orange-500"></span>
            Departamento: {{ $productosDepto->first()->departamento->nombre ?? 'Sin Clasificar' }} 
            <span class="text-zinc-500 text-[10px]">({{ $productosDepto->count() }} items)</span>
        </h2>

        <div class="bg-white dark:bg-zinc-900/50 rounded-3xl border border-zinc-200 dark:border-white/5 overflow-hidden shadow-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-zinc-400 dark:text-zinc-500 font-black uppercase italic text-[10px] tracking-widest border-b border-zinc-200 dark:border-white/5">
                        <th class="px-6 py-4">Descripción / Código</th>
                        <th class="px-6 py-4 text-center">Costo</th>
                        <th class="px-6 py-4 text-center">Venta</th>
                        @if($mostrarStock)
                        <th class="px-6 py-4 text-center">Stock Actual</th>
                        @endif
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($productosDepto as $producto)
                    <tr class="fila-producto hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-all border-b border-zinc-100 dark:border-white/5"
                        data-nombre="{{ strtolower($producto->descripcion) }}"
                        data-codigo="{{ strtolower($producto->codigo_barras) }}"
                    >
                        <td class="px-6 py-4">
                            <div class="font-black italic text-zinc-800 dark:text-white uppercase leading-tight">
                                {{ $producto->descripcion }}
                            </div>
                            <div class="text-[10px] text-zinc-500 font-mono">{{ $producto->codigo_barras }}</div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-zinc-500 italic">
                            ${{ number_format($producto->precio_costo, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-zinc-700 dark:text-zinc-300">
                            ${{ number_format($producto->precio_venta, 2) }}
                        </td>
                        @if($mostrarStock)
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 rounded-full font-bold {{ $producto->stock_actual <= $producto->stock_minimo ? 'bg-red-500/10 text-red-500' : 'bg-emerald-500/10 text-emerald-500' }}">
                                    {{ number_format($producto->stock_actual, 2) }} {{ $producto->unidad_medida }}
                                </span>
                                @if($producto->es_granel)
                                    <span class="text-[9px] text-orange-500 font-black italic uppercase mt-1 tracking-tighter">Granel</span>
                                @endif
                            </div>
                        </td>
                        @endif
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 text-zinc-400">
                                <button onclick='abrirModalEditar(@json($producto))' 
                                    class="hover:text-orange-500 transition-colors font-black italic uppercase text-[10px]">Editar</button>
                                <span class="opacity-20 font-light">|</span>
                                <button onclick="confirmarBaja({{ $producto->id }}, '{{ $producto->descripcion }}')" 
                                    class="hover:text-red-500 transition-colors font-black italic uppercase text-[10px]">Baja</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

{{-- MODAL NUEVO PRODUCTO --}}
<div id="modalProducto" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 p-8 rounded-3xl w-full max-w-2xl shadow-2xl overflow-y-auto max-h-[95vh]">
        <h2 class="text-2xl font-black italic text-zinc-800 dark:text-white uppercase tracking-tighter mb-6">Nuevo <span class="text-orange-500">Producto</span></h2>
        
        <form action="{{ route('productos.store') }}" method="POST" class="grid grid-cols-2 gap-4">
            @csrf
            <div class="col-span-2 sm:col-span-1">
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Código de Barras (Opcional)</label>
                <div class="relative mt-1">
                    <input type="text" name="codigo_barras" id="nuevo_codigo_barras"
                        class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 pr-12 text-zinc-900 dark:text-white focus:border-orange-500 outline-none">
                    <button type="button" onclick="abrirCamaraInventario('nuevo_codigo_barras')"
                        title="Escanear con cámara"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 bg-orange-500/10 hover:bg-orange-600 text-orange-600 hover:text-white rounded-lg transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Departamento</label>
                <select name="departamento_id" required class="w-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
                    @foreach($departamentos ?? [] as $depto)
                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Descripción</label>
                <input type="text" name="descripcion" required class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white focus:border-orange-500 outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Precio Costo</label>
                <input type="number" step="0.01" name="precio_costo" required class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Precio Venta</label>
                <input type="number" step="0.01" name="precio_venta" required class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Stock Actual</label>
                <input type="number" step="0.01" name="stock_actual" required class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Stock Mínimo</label>
                <input type="number" step="0.01" name="stock_minimo" value="0" class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Unidad de Medida</label>
                <select name="unidad_medida" required class="w-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
                    <option value="pieza">Pieza</option>
                    <option value="kg">Kilogramo (kg)</option>
                    <option value="litro">Litro (L)</option>
                </select>
            </div>
            <div class="flex items-center gap-3 ml-2 mt-6">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="es_granel" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none dark:bg-zinc-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500 rounded-full"></div>
                    <span class="ml-3 text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">¿Venta a Granel?</span>
                </label>
            </div>
            <div class="col-span-2 pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modalProducto').classList.add('hidden')" class="flex-1 px-6 py-3 bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-white font-black italic rounded-xl hover:bg-zinc-300 dark:hover:bg-zinc-700 transition-all uppercase text-xs">Cerrar</button>
                <button type="submit" class="flex-1 px-6 py-3 bg-orange-600 text-white font-black italic rounded-xl hover:bg-orange-500 transition-all uppercase text-xs shadow-[0_0_20px_rgba(234,88,12,0.3)]">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDITAR PRODUCTO --}}
<div id="modalEditar" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 p-8 rounded-3xl w-full max-w-2xl shadow-2xl overflow-y-auto max-h-[95vh]">
        <h2 class="text-2xl font-black italic text-zinc-800 dark:text-white uppercase tracking-tighter mb-6">Editar <span class="text-orange-500">Producto</span></h2>
        <form id="formEditar" method="POST" class="grid grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Código de Barras</label>
            <div class="col-span-2 sm:col-span-1">
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Código de Barras (Opcional)</label>
                <div class="relative mt-1">
                    <input type="text" name="codigo_barras" id="edit_codigo"
                        class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 pr-12 text-zinc-900 dark:text-white focus:border-orange-500 outline-none">
                    <button type="button" onclick="abrirCamaraInventario('edit_codigo')"
                        title="Escanear con cámara"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 bg-orange-500/10 hover:bg-orange-600 text-orange-600 hover:text-white rounded-lg transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Departamento</label>
                <select name="departamento_id" id="edit_depto" class="w-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none focus:border-orange-500">
                    @foreach($departamentos ?? [] as $depto)
                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Descripción</label>
                <input type="text" name="descripcion" id="edit_descripcion" required class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white focus:border-orange-500 outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Precio Costo</label>
                <input type="number" step="0.01" name="precio_costo" id="edit_costo" class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Precio Venta</label>
                <input type="number" step="0.01" name="precio_venta" id="edit_venta" class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Stock Actual</label>
                <input type="number" step="0.01" name="stock_actual" id="edit_stock_actual" class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Stock Mínimo</label>
                <input type="number" step="0.01" name="stock_minimo" id="edit_stock_minimo" class="w-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest ml-2">Unidad de Medida</label>
                <select name="unidad_medida" id="edit_unidad" class="w-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-900 dark:text-white outline-none">
                    <option value="pieza">Pieza</option>
                    <option value="kg">Kilogramo (kg)</option>
                    <option value="litro">Litro (L)</option>
                </select>
            </div>
            <div class="flex items-center gap-3 ml-2 mt-6">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="es_granel" value="1" id="edit_es_granel" class="sr-only peer">
                    <div class="w-11 h-6 bg-zinc-200 peer-focus:outline-none dark:bg-zinc-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500 rounded-full"></div>
                    <span class="ml-3 text-[10px] font-black italic text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">¿Venta a Granel?</span>
                </label>
            </div>
            <div class="col-span-2 pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('modalEditar').classList.add('hidden')" class="flex-1 px-6 py-3 bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-white font-black italic rounded-xl hover:bg-zinc-300 dark:hover:bg-zinc-700 transition-all uppercase text-xs">Cancelar</button>
                <button type="submit" class="flex-1 px-6 py-3 bg-orange-600 text-white font-black italic rounded-xl hover:bg-orange-500 transition-all uppercase text-xs shadow-[0_0_20px_rgba(234,88,12,0.3)]">Actualizar</button>
            </div>
        </form>
    </div>
</div>

{{-- FORMULARIO OCULTO PARA BAJAS --}}
<form id="formBaja" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@push('scripts')
<script>
    // ── Dropdown custom de departamentos ─────────────────────────
    function toggleDropdownDepto() {
        const dd = document.getElementById('dropdown-depto');
        const ch = document.getElementById('chevron-depto');
        dd.classList.toggle('hidden');
        ch.style.transform = dd.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    function seleccionarDepto(btn) {
        document.getElementById('filtro-departamento').value = btn.dataset.deptoVal;
        document.getElementById('label-depto').textContent   = btn.dataset.deptoLabel;
        document.getElementById('dropdown-depto').classList.add('hidden');
        document.getElementById('chevron-depto').style.transform = '';

        // Resaltar opción activa
        document.querySelectorAll('.opcion-depto').forEach(o => {
            o.classList.remove('text-orange-500', 'bg-orange-500/5');
        });
        btn.classList.add('text-orange-500', 'bg-orange-500/5');

        filtrar();
    }

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!document.getElementById('wrapper-depto').contains(e.target)) {
            document.getElementById('dropdown-depto').classList.add('hidden');
            document.getElementById('chevron-depto').style.transform = '';
        }
    });

    // ── Buscador predictivo + filtro departamento ─────────────────
    const buscador       = document.getElementById('buscador-inventario');
    const filtrodepto    = document.getElementById('filtro-departamento');
    const sinResultados  = document.getElementById('sin-resultados');

    function filtrar() {
        const texto = buscador.value.toLowerCase().trim();
        const deptoId = filtrodepto.value;
        let hayAlgo = false;

        document.querySelectorAll('.bloque-depto').forEach(bloque => {
            // Filtro por departamento
            if (deptoId && bloque.dataset.deptoId !== deptoId) {
                bloque.style.display = 'none';
                return;
            }

            let filaVisible = false;
            bloque.querySelectorAll('.fila-producto').forEach(fila => {
                const nombre = fila.dataset.nombre ?? '';
                const codigo = fila.dataset.codigo ?? '';
                const coincide = texto === '' || nombre.includes(texto) || codigo.includes(texto);
                fila.style.display = coincide ? '' : 'none';
                if (coincide) { filaVisible = true; hayAlgo = true; }
            });

            bloque.style.display = filaVisible ? '' : 'none';
        });

        sinResultados.classList.toggle('hidden', hayAlgo || (texto === '' && deptoId === ''));
    }

    buscador.addEventListener('input', filtrar);
    filtrodepto.addEventListener('change', filtrar);

    // Atajo: Ctrl+F o / abre el buscador
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey && e.key === 'f') || e.key === '/') {
            if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT') {
                e.preventDefault();
                buscador.focus();
                buscador.select();
            }
        }
        if (e.key === 'Escape') { buscador.value = ''; filtrodepto.value = ''; filtrar(); }
    });
    async function toggleStock() {
        const btn = document.getElementById('btn-toggle-stock');
        btn.disabled = true;
        btn.style.opacity = '0.5';

        await fetch('{{ route('admin.toggle.stock') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        });

        // Recargar la página para reflejar el nuevo estado desde el servidor
        window.location.reload();
    }

    // ── Cámara para inventario ────────────────────────────────────
    let _streamInv = null;
    let _lectorInv = null;
    let _inputDestino = null;

    window.abrirCamaraInventario = async function(inputId) {
        _inputDestino = inputId;
        document.getElementById('modal-camara-inv').classList.remove('hidden');

        if (_streamInv) { _streamInv.getTracks().forEach(t => t.stop()); _streamInv = null; }
        if (_lectorInv) { try { _lectorInv.reset(); } catch {} _lectorInv = null; }

        const video  = document.getElementById('camara-inv-video');
        const estado = document.getElementById('camara-inv-estado');

        try {
            _streamInv = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }
            });
            video.srcObject = _streamInv;
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
            hints.set(window.ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                window.ZXing.BarcodeFormat.EAN_13, window.ZXing.BarcodeFormat.EAN_8,
                window.ZXing.BarcodeFormat.CODE_128, window.ZXing.BarcodeFormat.CODE_39,
                window.ZXing.BarcodeFormat.UPC_A, window.ZXing.BarcodeFormat.UPC_E,
            ]);
            hints.set(window.ZXing.DecodeHintType.TRY_HARDER, true);

            _lectorInv = new window.ZXing.BrowserMultiFormatReader(hints);
            _lectorInv.decodeFromVideoElement(video, (resultado) => {
                if (!resultado) return;
                cerrarCamaraInv();
                const input = document.getElementById(_inputDestino);
                if (input) {
                    input.value = resultado.getText();
                    input.focus();
                    // Feedback visual
                    input.classList.add('border-orange-500', 'bg-orange-500/5');
                    setTimeout(() => input.classList.remove('border-orange-500', 'bg-orange-500/5'), 1500);
                }
            });

        } catch (err) {
            if (estado) estado.textContent = err.name === 'NotAllowedError'
                ? 'Permiso de cámara denegado'
                : 'No se pudo acceder a la cámara';
        }
    };

    function cerrarCamaraInv() {
        if (_lectorInv) { try { _lectorInv.reset(); } catch {} _lectorInv = null; }
        if (_streamInv) { _streamInv.getTracks().forEach(t => t.stop()); _streamInv = null; }
        document.getElementById('modal-camara-inv').classList.add('hidden');
    }

    document.getElementById('btn-cerrar-camara-inv')?.addEventListener('click', cerrarCamaraInv);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') cerrarCamaraInv();
    });
    function abrirModalEditar(producto) {
        document.getElementById('edit_codigo').value        = producto.codigo_barras ?? '';
        document.getElementById('edit_descripcion').value   = producto.descripcion ?? '';
        document.getElementById('edit_costo').value         = producto.precio_costo ?? '';
        document.getElementById('edit_venta').value         = producto.precio_venta ?? '';
        document.getElementById('edit_stock_actual').value  = producto.stock_actual ?? '';
        document.getElementById('edit_stock_minimo').value  = producto.stock_minimo ?? '';
        document.getElementById('edit_depto').value         = producto.departamento_id ?? '';
        document.getElementById('edit_unidad').value        = producto.unidad_medida ?? 'pieza';
        document.getElementById('edit_es_granel').checked   = producto.es_granel == 1;
        document.getElementById('formEditar').action        = `/admin/productos/${producto.id}`;
        document.getElementById('modalEditar').classList.remove('hidden');
    }

    function confirmarBaja(id, nombre) {
        if (confirm(`¿Eliminar el producto "${nombre}"? Esta acción no se puede deshacer.`)) {
            const form = document.getElementById('formBaja');
            form.action = `/admin/productos/${id}`;
            form.submit();
        }
    }
</script>
{{-- MODAL CÁMARA INVENTARIO --}}
<div id="modal-camara-inv" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-[99999] hidden flex items-center justify-center p-4">
    <div class="bg-[#0d0d0d] border border-white/10 w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl">
        <div class="flex justify-between items-center px-5 py-4 border-b border-white/5">
            <div>
                <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Cámara activa</p>
                <h3 class="text-white font-black italic uppercase text-lg">Escanear código</h3>
            </div>
            <button id="btn-cerrar-camara-inv" type="button"
                class="text-zinc-500 hover:text-white transition text-2xl font-black cursor-pointer leading-none">&times;</button>
        </div>

        <div class="relative bg-black" style="aspect-ratio: 4/3;">
            <video id="camara-inv-video" class="w-full h-full object-cover" playsinline muted></video>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-56 h-40">
                    <span class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-orange-500 rounded-tl-lg"></span>
                    <span class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-orange-500 rounded-tr-lg"></span>
                    <span class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-orange-500 rounded-bl-lg"></span>
                    <span class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-orange-500 rounded-br-lg"></span>
                    <div class="absolute left-2 right-2 h-0.5 bg-orange-500/70 shadow-[0_0_6px_rgba(249,115,22,0.8)]"
                        style="top:50%; animation: scan 2s ease-in-out infinite;"></div>
                </div>
            </div>
            <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                <span id="camara-inv-estado" class="bg-black/60 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full">
                    Iniciando cámara...
                </span>
            </div>
        </div>

        <div class="p-4 text-center">
            <p class="text-zinc-500 text-[10px] font-bold uppercase tracking-widest">El código se llenará automáticamente al detectarlo</p>
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

@endsection