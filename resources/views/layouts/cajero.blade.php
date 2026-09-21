<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 SISTEMA - @yield('title')</title>

    <script>
        (function () {
            const guardado = localStorage.getItem('theme');
            const esOscuro = guardado
                ? guardado === 'dark'
                : ("{{ Auth::user()->tema ?? 'claro' }}" === 'oscuro');
            document.documentElement.classList.toggle('dark', esOscuro);
        })();
    </script>

    <script src="{{ asset('js/sweetalert2.js') }}"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .font-digital { font-family: 'Orbitron', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Personalización de barras de scroll generales */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 10px; }
        input:focus, button:focus, a:focus { outline: none !important; ring: 0 !important; }
    </style>
</head>
<body
    class="bg-zinc-100 dark:bg-black text-zinc-900 dark:text-white h-screen w-screen overflow-hidden transition-colors duration-300 relative select-none"
    data-ruta-user-theme="{{ route('user.theme') }}"
    data-ruta-cajon-abrir="{{ route('admin.cajon.abrir') }}"
    data-ruta-ventas-index="{{ route('ventas.index') }}"
    data-csrf="{{ csrf_token() }}"
    data-tema-actual="{{ Auth::user()->tema ?? 'claro' }}"
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') ?? ($errors->any() ? $errors->first() : '') }}"
    data-impresora-nombre="{{ $configHardware->impresora_nombre }}"
    data-impresora-tipo="{{ $configHardware->impresora_tipo }}"
    data-impresora-ip="{{ $configHardware->impresora_ip }}"
    data-cajon-comando="{{ $configHardware->cajon_comando_apertura }}"
    data-bascula-activada="{{ $configHardware->bascula_activada ? 'true' : 'false' }}"
    data-bascula-baud="{{ $configHardware->bascula_baud_rate }}"
    data-modo-simulado="{{ $configHardware->modo_simulado ? 'true' : 'false' }}"
>

    {{-- WRAPPER PRINCIPAL CONTENEDOR (Fijo al 100% del viewport) --}}
    <div class="flex h-screen w-screen overflow-hidden">

        {{-- SIDEBAR CON SCROLL INDEPENDIENTE --}}
        @include('layouts.partials.sidebar')

        {{-- ÁREA PRINCIPAL CON SCROLL INDEPENDIENTE --}}
        <main class="flex-1 min-w-0 h-full overflow-y-auto overflow-x-hidden p-4 md:p-6 bg-zinc-50 dark:bg-black relative shadow-inner custom-scrollbar">

            {{-- DECORACIÓN BACKGROUND --}}
            <div class="absolute top-0 right-0 p-12 opacity-[0.02] dark:opacity-[0.05] pointer-events-none overflow-hidden select-none">
                <i class="fas fa-bolt text-[300px] -rotate-12"></i>
            </div>

            {{-- CONTENIDO DE LA VISTA --}}
            <div class="relative z-10 w-full min-h-full flex flex-col">
                @yield('content')
            </div>

        </main>

    </div>


    {{-- STACKS PARA MODALES Y SCRIPTS SECUNDARIOS --}}
    @stack('modals')
    @stack('scripts')

</body>
</html>