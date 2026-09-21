<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaEspera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        return view('ventas.index');
    }

    public function buscarProducto(Request $request)
    {
        $producto = Producto::where('codigo_barras', $request->codigo)
            ->where('activo', true)
            ->first();

        if (! $producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    public function buscarPorNombre(Request $request)
    {
        $q = $request->query('q');
        $productos = Producto::where('descripcion', 'LIKE', "%{$q}%")
            ->where('activo', true)
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    public function finalizarVenta(Request $request)
    {
        // Validamos que el carrito no llegue vacío desde el JS
        if (! $request->productos || count($request->productos) == 0) {
            return response()->json(['status' => 'error', 'mensaje' => 'Carrito vacío'], 400);
        }

        // BUG 9 CORREGIDO: Validar que en efectivo el monto recibido cubra el total
        $metodoPago = $request->metodo_pago ?? 'efectivo';
        if ($metodoPago === 'efectivo') {
            $montoRecibido = floatval($request->monto_recibido ?? 0);
            $total = floatval($request->total ?? 0);
            if ($montoRecibido < $total) {
                return response()->json([
                    'status' => 'error',
                    'mensaje' => 'El monto recibido ($'.number_format($montoRecibido, 2).') es menor al total ($'.number_format($total, 2).').',
                ], 400);
            }
        }

        try {
            $ventaFinalizada = DB::transaction(function () use ($request, $metodoPago) {

                // BUG 7 CORREGIDO: Verificar stock suficiente ANTES de crear la venta
                foreach ($request->productos as $item) {
                    $prod = Producto::lockForUpdate()->find($item['id']);
                    if (! $prod) {
                        throw new \Exception("Producto con ID {$item['id']} no encontrado.");
                    }
                    if (! $prod->es_granel && $prod->stock_actual < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para \"{$prod->descripcion}\". Disponible: {$prod->stock_actual}, solicitado: {$item['cantidad']}.");
                    }
                }

                // 1. Crear la venta
                $venta = Venta::create([
                    'folio'            => 'V-'.strtoupper(uniqid()),
                    'fecha'            => now(),
                    'usuario_id'       => Auth::id(),
                    'cliente_id'       => $request->cliente_id ?? null,
                    'subtotal'         => $request->total,
                    'descuento'        => 0,
                    'total'            => $request->total,
                    'tipo_pago'        => $metodoPago,
                    'referencia_pago'  => $request->referencia_pago,
                    'pago_cliente'     => $request->monto_recibido,
                    'cambio'           => $request->cambio,
                    'estado'           => 'completada',
                ]);

                // 2. Registrar detalles y descontar stock
                foreach ($request->productos as $item) {
                    VentaDetalle::create([
                        'venta_id'       => $venta->id,
                        'producto_id'    => $item['id'],
                        'descripcion'    => $item['descripcion'],
                        'cantidad'       => $item['cantidad'],
                        'precio_unitario'=> $item['precio'],
                        'subtotal'       => $item['subtotal'] ?? ($item['precio'] * $item['cantidad']),
                    ]);

                    Producto::where('id', $item['id'])->decrement('stock_actual', $item['cantidad']);
                }

                return $venta;
            });

            return response()->json([
                'status'   => 'success',
                'venta_id' => $ventaFinalizada->id,
                'folio'    => $ventaFinalizada->folio,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function imprimirTicket($id)
    {
        // Intentamos cargar la venta con sus relaciones
        $venta = Venta::with(['detalles.producto', 'usuario'])->findOrFail($id);

        return view('ventas.ticket', compact('venta'));
    }

    public function pausarVenta(Request $request)
    {
        try {
            if (! $request->productos || count($request->productos) == 0) {
                return response()->json(['status' => 'error', 'mensaje' => 'No hay productos para pausar'], 400);
            }

            VentaEspera::create([
                'usuario_id' => Auth::id(),
                'fecha_pausa' => now(),
                'carrito_data' => $request->productos,
            ]);

            return response()->json(['status' => 'success', 'mensaje' => 'Venta pausada']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'mensaje' => $e->getMessage()], 500);
        }
    }

    public function recuperarVenta($id)
    {
        $venta = VentaEspera::findOrFail($id);
        $datos = $venta->carrito_data;

        $venta->delete();

        return response()->json([
            'status' => 'success',
            'carrito' => $datos,
        ]);
    }

    public function cancelarVenta($id)
    {
        $venta = Venta::findOrFail($id);

        foreach ($venta->detalles as $detalle) {
            if ($detalle->producto) {
                $detalle->producto->increment('stock_actual', $detalle->cantidad);
            }
        }

        $venta->delete();

        return back()->with('success', 'Venta eliminada correctamente.');
    }

    public function abrirCajonManual()
    {
        // Esta función solo sirve para cargar la "página fantasma" que imprime
        return view('admin.impresion.abrir_cajon');
    }
}