<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ConfiguracionHardwareController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\VerificarCajaAbierta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas & Autenticación
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::get('/logout-especial', 'logoutEspecial')->name('logout.especial');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (General - Requiere Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Ajustes de Usuario
    Route::post('/user/theme', function (Request $request) {
        Auth::user()->update(['tema' => $request->theme]);
        return response()->json(['res' => 'ok', 'nuevo_tema' => $request->theme]);
    })->name('user.theme');

    /*
    |--------------------------------------------------------------------------
    | MÓDULO DE CAJA (Apertura de Turno)
    |--------------------------------------------------------------------------
    */
    Route::prefix('caja')->name('caja.')->controller(CajaController::class)->group(function () {
        Route::get('/apertura', 'aperturaIndex')->name('apertura');
        Route::post('/apertura', 'aperturaStore')->name('apertura.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas que requieren turno de caja abierto (cajeros; admins exentos)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['caja.abierta'])->group(function () {

        // Punto de Venta (POS)
        Route::prefix('ventas')->name('ventas.')->controller(VentaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/pausar', 'pausarVenta')->name('pausar');
            Route::get('/buscar-producto', 'buscarProducto')->name('buscar');
            Route::get('/buscar-nombre', 'buscarPorNombre')->name('buscarNombre');
            Route::post('/finalizar', 'finalizarVenta')->name('finalizar');
            Route::get('/ticket/{id}', 'imprimirTicket')->name('ticket');
            Route::get('/recuperar/{id}', 'recuperarVenta')->name('recuperar');
        });

        // Inventario para Cajeros (solo lectura)
        Route::get('/ventas/inventario', [AdminController::class, 'inventarioCajero'])
            ->name('ventas.inventario')
            ->middleware('permiso:inventario.ver');

        Route::post('/inventario/agregar-stock', [InventarioController::class, 'agregarStock'])->name('inventario.agregar-stock');

        // Impresión / Cajón (vista vieja de window.print, sin permiso especial)
        Route::get('/admin/impresion/abrir-cajon', fn() => view('admin.impresion.abrir_cajon'))->name('impresion.abrir-cajon');

        // Corte de Caja — cualquier cajero con turno abierto
        Route::get('/admin/corte', [CajaController::class, 'corteIndex'])->name('admin.corte');
        Route::post('/admin/corte/guardar', [CajaController::class, 'corteStore'])->name('admin.corte.store');

        // Gastos manuales del turno — se movió aquí desde soloAdmin, cualquier cajero con turno abierto lo usa
        Route::get('/admin/gastos', [GastoController::class, 'index'])->name('gastos.index')->middleware('permiso:reportes.ver');
        Route::post('/admin/gastos', [GastoController::class, 'store'])->name('gastos.store');
    });

    /*
    |--------------------------------------------------------------------------
    | CRUD Productos — por permiso, no exclusivo de soloAdmin
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/productos')->name('productos.')->controller(AdminController::class)->group(function () {
        Route::get('/', 'productos')->name('index')->middleware('permiso:productos.gestionar');
        Route::post('/', 'storeProducto')->name('store')->middleware('permiso:productos.gestionar');
        Route::put('/{id}', 'updateProducto')->name('update')->middleware('permiso:productos.gestionar');
        Route::delete('/{id}', 'destroyProducto')->name('destroy')->middleware('permiso:productos.gestionar');
    });

    // Departamentos / Categorías — por permiso
    Route::middleware('permiso:departamentos.gestionar')->group(function () {
        Route::resource('admin/departamentos', DepartamentoController::class)
            ->except(['create', 'edit', 'show'])
            ->parameters(['departamentos' => 'departamento'])
            ->names('departamentos');
    });

    // Abrir cajón manual — por permiso
    Route::get('/abrir-cajon-manual', [VentaController::class, 'abrirCajonManual'])
        ->name('admin.cajon.abrir')
        ->middleware('permiso:cajon.abrir');

    // Historial y Detalle de Cajas — por permiso
    Route::prefix('admin/cajas')->name('admin.cajas.')->controller(AdminController::class)->group(function () {
        Route::get('/', 'historialCajas')->name('index')->middleware('permiso:caja.historial');
        Route::get('/{id}', 'detalleCaja')->name('show')->middleware('permiso:caja.detalle');
    });

    // Compras — por permiso
    Route::get('/admin/compras', [AdminController::class, 'historialCompras'])
        ->name('admin.compras.index')
        ->middleware('permiso:compras.ver');

    // Reportes — por permiso
    Route::get('/admin/reportes', [AdminController::class, 'reportes'])
        ->name('admin.reportes')
        ->middleware('permiso:reportes.ver');

    Route::get('/admin/reporte-excel-general', [GastoController::class, 'descargarReporte'])
        ->name('admin.reporte.excel')
        ->middleware('permiso:reportes.descargar');

    // Dashboard — por permiso
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard')
        ->middleware('permiso:dashboard.ver');

    // Usuarios / Cajeros — por permiso (incluye asignar permisos)
    Route::prefix('admin/usuarios')->name('admin.usuarios.')->controller(AdminController::class)->middleware('permiso:usuarios.gestionar')->group(function () {
        Route::get('/', 'usuariosIndex')->name('index');
        Route::post('/guardar', 'usuariosStore')->name('store');
        Route::put('/{id}', 'updateUsuario')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Configuración de Hardware — por permiso
    Route::middleware('permiso:hardware.configurar')->group(function () {
        Route::get('/admin/configuracion-hardware', [ConfiguracionHardwareController::class, 'edit'])->name('admin.hardware.edit');
        Route::put('/admin/configuracion-hardware', [ConfiguracionHardwareController::class, 'update'])->name('admin.hardware.update');
    });

    // Toggle mostrar/ocultar stock en inventario — solo admin
    Route::post('/admin/toggle-stock', [ConfiguracionHardwareController::class, 'toggleStock'])
        ->name('admin.toggle.stock')
        ->middleware('permiso:productos.gestionar');

    /*
    |--------------------------------------------------------------------------
    | Rutas que se quedan exclusivas de soloAdmin (no están en la lista de
    | permisos — control operativo de ventas, no gestión administrativa)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['soloAdmin'])->prefix('admin')->group(function () {
        Route::get('/ventas-espera/listar', [AdminController::class, 'listarVentasEspera'])->name('admin.ventas.espera');
        Route::delete('/ventas/cancelar/{id}', [AdminController::class, 'cancelarVenta'])->name('ventas.cancelar');
        Route::post('/ventas/sincronizar-offline', [VentaController::class, 'sincronizar'])->name('admin.ventas.sincronizar');
    });
});