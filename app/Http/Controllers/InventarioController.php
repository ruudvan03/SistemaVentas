<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventarioController extends Controller
{
    public function agregarStock(Request $request)
    {
        // 1. Validamos que venga el proveedor y la lista de productos
        $request->validate([
            'proveedor' => 'required|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:0.001',
            'productos.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $totalCompraEntrada = 0;
                $detallesGasto = '';

                foreach ($request->productos as $p) {
                    $producto = Producto::findOrFail($p['id']);
                    $subtotal = $p['cantidad'] * $p['costo_unitario'];
                    $totalCompraEntrada += $subtotal;

                    // 2. Registrar cada producto en el historial de Compras
                    Compra::create([
                        'producto_id' => $p['id'],
                        'proveedor' => strtoupper($request->proveedor),
                        'cantidad' => $p['cantidad'],
                        'costo_total' => $subtotal,
                        'metodo_pago' => $request->metodo_pago ?? 'efectivo',
                    ]);

                    // 3. Actualizar el stock de cada producto
                    $producto->increment('stock_actual', $p['cantidad']);

                    // Concatenamos para la descripción del gasto
                    $detallesGasto .= $producto->descripcion.' ('.$p['cantidad'].'), ';
                }

                // 4. REGISTRO ÚNICO EN TABLA DE GASTOS por toda la entrada
                DB::table('gastos')->insert([
                    'descripcion' => 'COMPRA MULT. PROV: '.strtoupper($request->proveedor).' - PRODS: '.substr($detallesGasto, 0, 150),
                    'monto' => $totalCompraEntrada,
                    'categoria' => 'INVENTARIO',
                    'usuario' => auth()->user()->nombre ?? 'Admin', // BUG 1 CORREGIDO: campo es 'nombre', no 'name'
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'status' => 'success',
                    'mensaje' => 'Entrada registrada y stock actualizado',
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Error en agregarStock: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'mensaje' => 'Nos se pudo agregar el stock: '.$e->getMessage(),
            ], 500);
        }
    }
}