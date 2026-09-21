<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionHardware;
use Illuminate\Http\Request;

class ConfiguracionHardwareController extends Controller
{
    public function edit()
    {
        $config = ConfiguracionHardware::actual();

        return view('admin.hardware.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'impresora_nombre'        => 'nullable|string|max:255',
            'impresora_tipo'          => 'required|in:usb,red',
            'impresora_ip'            => 'nullable|ip',
            'cajon_comando_apertura'  => 'required|string|regex:/^\d+(,\d+)*$/',
            'bascula_activada'        => 'nullable|boolean',
            'bascula_baud_rate'       => 'required|integer|min:1200|max:115200',
            'modo_simulado'           => 'nullable|boolean',
        ], [
            'cajon_comando_apertura.regex' => 'El comando debe ser números separados por comas, ej: 27,112,0,25,250',
        ]);

        $data['bascula_activada'] = $request->boolean('bascula_activada');
        $data['modo_simulado']    = $request->boolean('modo_simulado');

        ConfiguracionHardware::actual()->update($data);

        return back()->with('success', 'Configuración de hardware actualizada correctamente');
    }

    // Toggle rápido para mostrar/ocultar stock desde el inventario
    public function toggleStock()
    {
        $config = ConfiguracionHardware::actual();
        $config->mostrar_stock = ! $config->mostrar_stock;
        $config->save();

        return response()->json([
            'mostrar_stock' => $config->mostrar_stock,
        ]);
    }
}