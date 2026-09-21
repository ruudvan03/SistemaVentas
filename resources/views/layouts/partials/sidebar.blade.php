@php
    $userRol = strtolower(Auth::user()->rol ?? '');
    $esAdmin = ($userRol === 'admin' || $userRol === 'administrador');
    $user = Auth::user();

    $active = "bg-red-600 text-white shadow-[0_6px_14px_rgba(220,38,38,0.3)]";
    $inactive = "text-zinc-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-white/5";

    // El admin siempre pasa (bypass en Usuario::tienePermiso()); esto solo decide
    // si mostramos el encabezado "Administración" para un cajero con al menos un permiso admin.
    $tieneAlgoAdmin = $esAdmin || collect([
        'dashboard.ver', 'departamentos.gestionar', 'productos.gestionar',
        'caja.historial', 'reportes.ver', 'compras.ver', 'hardware.configurar', 'usuarios.gestionar',
    ])->contains(fn($slug) => $user->tienePermiso($slug));
@endphp

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Tooltips para sidebar colapsado */
    .nav-tooltip { position: relative; }
    .nav-tooltip .tooltip-label {
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        background: #18181b;
        color: #fff;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .1em;
        white-space: nowrap;
        padding: 5px 10px;
        border-radius: 8px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .15s, transform .15s;
        transform: translateY(-50%) translateX(-4px);
        z-index: 9999;
        border: 1px solid rgba(255,255,255,.08);
    }
    .nav-tooltip:hover .tooltip-label { opacity: 1; transform: translateY(-50%) translateX(0); }
</style>

<aside
    x-data="{ 
        colapsado: localStorage.getItem('sidebar_colapsado') === 'true',
        esOscuro: localStorage.getItem('theme') === 'dark' || document.documentElement.classList.contains('dark'),
        toggleTema() {
            this.esOscuro = !this.esOscuro;
            document.documentElement.classList.toggle('dark', this.esOscuro);
            localStorage.setItem('theme', this.esOscuro ? 'dark' : 'light');

            fetch(document.body.dataset.rutaUserTheme, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.body.dataset.csrf
                },
                body: JSON.stringify({ tema: this.esOscuro ? 'oscuro' : 'claro' })
            }).catch(() => {});
        }
    }"
    x-init="$watch('colapsado', v => localStorage.setItem('sidebar_colapsado', v))"
    :class="colapsado ? 'w-20' : 'w-64'"
    class="min-w-0 bg-white dark:bg-[#0d0d0d] flex flex-col border-r border-zinc-200 dark:border-white/5 transition-[width] duration-300 relative z-20 flex-shrink-0 h-screen overflow-hidden select-none"
>
    {{-- HEADER --}}
    <div class="p-4 flex items-center justify-between h-16 border-b border-zinc-100 dark:border-white/5 shrink-0">
        <h2 x-show="!colapsado" x-transition class="text-zinc-800 dark:text-white text-lg font-black italic uppercase tracking-tighter leading-none whitespace-nowrap pl-2">
            F1 <span class="text-red-600">{{ $esAdmin ? 'PANEL' : 'CAJERO' }}</span>
        </h2>
        <h2 x-show="colapsado" x-transition class="text-zinc-800 dark:text-white text-lg font-black italic uppercase tracking-tighter leading-none mx-auto">
            <span class="text-red-600">F1</span>
        </h2>

        <button @click="colapsado = !colapsado"
            class="p-2 text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-white rounded-lg hover:bg-zinc-100 dark:hover:bg-white/5 transition-colors focus:outline-none cursor-pointer"
            :class="colapsado ? 'mx-auto mt-1' : ''"
            :title="colapsado ? 'Expandir menú' : 'Colapsar menú'">
            <i class="fas fa-bars text-base"></i>
        </button>
    </div>

    {{-- NAVEGACIÓN --}}
    <nav class="flex-1 px-3 space-y-1.5 overflow-y-auto overflow-x-hidden no-scrollbar pt-4">
        @if($user->tienePermiso('cajon.abrir'))
        <button id="btn-abrir-cajon"
            :title="colapsado ? 'Abrir Cajón' : ''"
            class="w-full flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all border border-yellow-600/30 text-yellow-600 hover:bg-yellow-600 hover:text-white group mb-3 cursor-pointer"
            :class="colapsado ? 'justify-center' : 'space-x-3'">
            <i class="fas fa-key text-sm flex-shrink-0"></i>
            <span x-show="!colapsado" x-transition class="whitespace-nowrap">Abrir Cajón</span>
        </button>
        @endif

        <p x-show="!colapsado" x-transition class="text-[9px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1 border-b border-zinc-100 dark:border-white/5 pb-1 whitespace-nowrap">Operación</p>

    @if(!$esAdmin && !\App\Models\CorteCaja::turnoActivo(Auth::id()))
        <a href="{{ route('caja.apertura') }}"
            :title="colapsado ? 'Apertura de Caja' : ''"
            :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('caja.apertura') ? "'$active'" : "'$inactive'" }}]"
            class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
            <i class="fas fa-door-open text-sm flex-shrink-0"></i>
            <span x-show="!colapsado" x-transition class="whitespace-nowrap">Apertura de Caja</span>
        </a>
    @endif

        <a href="{{ route('ventas.index') }}"
            :title="colapsado ? 'Punto de Venta' : ''"
            :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('ventas.index') ? "'$active'" : "'$inactive'" }}]"
            class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all relative">
            <i class="fas fa-cash-register text-sm flex-shrink-0"></i>
            <span x-show="!colapsado" x-transition class="whitespace-nowrap">Punto de Venta</span>
            <span x-show="colapsado" class="tooltip-label">Punto de Venta</span>
        </a>

        @if(!$esAdmin && $user->tienePermiso('inventario.ver'))
            <a href="{{ route('ventas.inventario') }}"
                :title="colapsado ? 'Inventario' : ''"
                :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('ventas.inventario') ? "'$active'" : "'$inactive'" }}]"
                class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                <i class="fas fa-boxes text-sm flex-shrink-0"></i>
                <span x-show="!colapsado" x-transition class="whitespace-nowrap">Inventario</span>
            </a>
        @endif

        <a href="{{ route('admin.corte') }}"
            :title="colapsado ? 'Corte de Caja' : ''"
            :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.corte') ? "'$active'" : "'$inactive'" }}]"
            class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
            <i class="fas fa-calculator text-sm flex-shrink-0"></i>
            <span x-show="!colapsado" x-transition class="whitespace-nowrap">Flujo de caja</span>
        </a>

        @if($tieneAlgoAdmin)
            <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-white/5 space-y-1.5">
                <p x-show="!colapsado" x-transition class="text-[9px] font-black text-red-600 uppercase tracking-[0.2em] mb-2 ml-1 whitespace-nowrap">Administración</p>

                @if($user->tienePermiso('dashboard.ver'))
                <a href="{{ route('admin.dashboard') }}" :title="colapsado ? 'Dashboard' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.dashboard') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-chart-pie text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Dashboard</span>
                </a>
                @endif

                {{-- DEPARTAMENTOS --}}
                @if($user->tienePermiso('departamentos.gestionar'))
                <a href="{{ route('departamentos.index') }}" :title="colapsado ? 'Categoría' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('departamentos.*') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-tag text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Categoría</span>
                </a>
                @endif

                {{-- PRODUCTOS --}}
                @if($user->tienePermiso('productos.gestionar'))
                <a href="{{ route('productos.index') }}" :title="colapsado ? 'Productos' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('productos.*') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-box text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Productos</span>
                </a>
                @endif

                {{-- HISTORIAL DE CAJA --}}
                @if($user->tienePermiso('caja.historial'))
                <a href="{{ route('admin.cajas.index') }}" :title="colapsado ? 'Historial de Caja' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.cajas.*') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-history text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Historial de Caja</span>
                </a>
                @endif

                {{-- REPORTES GENERAL --}}
                @if($user->tienePermiso('reportes.ver'))
                <a href="{{ route('admin.reportes') }}" :title="colapsado ? 'Reportes General' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.reportes') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-chart-bar text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Reportes General</span>
                </a>
                @endif

                {{-- COMPRAS --}}
                @if($user->tienePermiso('compras.ver'))
                <a href="{{ route('admin.compras.index') }}" :title="colapsado ? 'Compras' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.compras.index') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-shopping-bag text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Proveedores</span>
                </a>
                @endif

                {{-- CONFIGURACIÓN DE HARDWARE --}}
                @if($user->tienePermiso('hardware.configurar'))
                <a href="{{ route('admin.hardware.edit') }}" :title="colapsado ? 'Config. Hardware' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.hardware.*') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-microchip text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Configuracion</span>
                </a>
                @endif

                {{-- GESTIONAR CAJEROS --}}
                @if($user->tienePermiso('usuarios.gestionar'))
                <a href="{{ route('admin.usuarios.index') }}" :title="colapsado ? 'Gestionar Cajeros' : ''" :class="[colapsado ? 'justify-center nav-tooltip' : 'space-x-3', {{ request()->routeIs('admin.usuarios.*') ? "'$active'" : "'$inactive'" }}]" class="flex items-center p-3 rounded-xl font-bold italic uppercase text-xs tracking-wide transition-all">
                    <i class="fas fa-users-cog text-sm flex-shrink-0"></i>
                    <span x-show="!colapsado" x-transition class="whitespace-nowrap">Gestionar Cajeros</span>
                </a>
                @endif
            </div>
        @endif

        <div class="pt-3" x-show="!colapsado" x-transition>
            <div class="p-3 bg-zinc-100 dark:bg-[#1a1a1a] border-l-4 border-red-600 rounded-r-xl">
                <p class="text-[9px] font-black text-zinc-400 uppercase tracking-widest mb-0.5 whitespace-nowrap">Terminal Activa</p>
                <span class="text-zinc-900 dark:text-white font-black italic uppercase text-xs whitespace-nowrap">{{ Auth::user()->username }}</span>
            </div>
        </div>

        {{-- Indicador de turno activo --}}
        @php $turnoActivo = \App\Models\CorteCaja::turnoActivo(Auth::id()); @endphp
        @if($turnoActivo)
        <div x-show="!colapsado" x-transition
            class="mx-2 mb-2 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl"
            id="indicador-turno">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    Turno activo
                </span>
                <span class="text-[9px] font-black text-zinc-400 uppercase" id="reloj-turno">--:--</span>
            </div>
            <p class="text-[9px] text-zinc-400 font-bold uppercase">Desde {{ $turnoActivo->fecha_apertura->format('H:i') }}</p>
        </div>
        <script>
            (function() {
                const apertura = new Date('{{ $turnoActivo->fecha_apertura->toIso8601String() }}');
                function actualizarReloj() {
                    const diff = Math.floor((Date.now() - apertura) / 1000);
                    const h = Math.floor(diff / 3600).toString().padStart(2,'0');
                    const m = Math.floor((diff % 3600) / 60).toString().padStart(2,'0');
                    const el = document.getElementById('reloj-turno');
                    if (el) el.textContent = `${h}h ${m}m`;
                }
                actualizarReloj();
                setInterval(actualizarReloj, 30000);
            })();
        </script>
        @endif
    </nav>

    {{-- FOOTER CON BOTÓN DE MODO Y CERRAR SESIÓN --}}
    <div class="p-3 border-t border-zinc-200 dark:border-white/5 space-y-2 shrink-0">

        {{-- SWITCH DE TEMA (expandido) --}}
        <button x-show="!colapsado" x-transition @click="toggleTema()"
            title="Cambiar Modo Claro / Oscuro"
            class="relative w-full h-10 flex items-center rounded-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 transition-colors cursor-pointer focus:outline-none overflow-hidden">
            <span class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] rounded-full bg-white dark:bg-zinc-800 shadow-md transition-transform duration-300 ease-out"
                :class="esOscuro ? 'translate-x-[calc(100%+4px)]' : 'translate-x-0'"></span>
            <span class="relative z-10 flex-1 flex items-center justify-center gap-1.5 py-1.5 text-[10px] font-black uppercase tracking-widest transition-colors"
                :class="esOscuro ? 'text-zinc-400' : 'text-amber-500'">
                <i class="fas fa-sun text-xs"></i>
            </span>
            <span class="relative z-10 flex-1 flex items-center justify-center gap-1.5 py-1.5 text-[10px] font-black uppercase tracking-widest transition-colors"
                :class="esOscuro ? 'text-indigo-400' : 'text-zinc-400'">
                <i class="fas fa-moon text-xs"></i>
            </span>
        </button>

        {{-- BOTÓN DE MODO (colapsado, solo ícono) --}}
        <button x-show="colapsado" x-transition @click="toggleTema()"
            title="Cambiar Modo Claro / Oscuro"
            class="w-full flex items-center justify-center h-10 rounded-full bg-zinc-100 dark:bg-white/5 border border-zinc-200 dark:border-white/10 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-white/10 transition-colors cursor-pointer focus:outline-none">
            <i :class="esOscuro ? 'fa-moon text-indigo-400' : 'fa-sun text-amber-500'" class="fas text-sm"></i>
        </button>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                :title="colapsado ? 'Cerrar Sesión' : ''"
                :class="colapsado ? 'justify-center' : 'space-x-2 px-2'"
                class="w-full text-zinc-400 hover:text-red-600 text-[10px] font-black uppercase tracking-[0.15em] flex items-center transition-colors py-2 cursor-pointer">
                <i class="fas fa-power-off text-xs flex-shrink-0"></i>
                <span x-show="!colapsado" x-transition class="whitespace-nowrap">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>