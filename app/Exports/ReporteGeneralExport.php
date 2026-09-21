<?php

namespace App\Exports;

use App\Models\CorteCaja;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReporteGeneralExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new VentasSheet,      // Pestaña 1: Solo Ventas
            new GastosSheet,      // Pestaña 2: Solo Gastos (Egresos)
            new CortesCajaSheet,  // Pestaña 3: Cortes de Caja
            new InventarioSheet,  // Pestaña 4: Inventario
        ];
    }
}

// PESTAÑA 1: VENTAS (INGRESOS)
class VentasSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // Quitamos 'status' porque no existe en tu tabla
        return Venta::select('fecha', 'total', 'tipo_pago')
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha/Hora', 'Monto ($)', 'Método de Pago'];
    }

    public function title(): string
    {
        return 'Ventas';
    }
}

// PESTAÑA 2: GASTOS (EGRESOS)
class GastosSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // Ajustado a monto y descripcion segun tu Excel
        return Gasto::select('created_at', 'monto', 'descripcion')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Fecha/Hora', 'Monto ($)', 'Descripción/Proveedor'];
    }

    public function title(): string
    {
        return 'Gastos - Egresos';
    }
}

// PESTAÑA 3: CORTES DE CAJA
class CortesCajaSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        // BUG 8 CORREGIDO: cargar la relación usuario para mostrar el nombre, no solo el ID
        return CorteCaja::with('usuario')
            ->orderBy('fecha_cierre', 'desc')
            ->get()
            ->map(fn ($corte) => [
                'id'            => $corte->id,
                'fecha_cierre'  => $corte->fecha_cierre?->format('d/m/Y H:i') ?? 'Abierto',
                'total_esperado'=> $corte->total_esperado,
                'total_contado' => $corte->total_contado,
                'difference'    => $corte->difference,
                'cajero'        => $corte->usuario?->nombre ?? 'N/A',
            ]);
    }

    public function headings(): array
    {
        return ['ID Corte', 'Fecha Cierre', 'Esperado ($)', 'Contado ($)', 'Diferencia ($)', 'Cajero'];
    }

    public function title(): string
    {
        return 'Cortes de Caja';
    }
}

// PESTAÑA 4: INVENTARIO
class InventarioSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Producto::select('codigo_barras', 'descripcion', 'precio_costo', 'precio_venta', 'stock_actual', 'stock_minimo')->get();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Costo ($)', 'Venta ($)', 'Stock Actual', 'Mínimo'];
    }

    public function title(): string
    {
        return 'Inventario';
    }
}