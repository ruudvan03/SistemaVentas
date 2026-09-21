<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionHardware extends Model
{
    protected $table = 'configuracion_hardware';

    protected $fillable = [
        'impresora_nombre', 'impresora_tipo', 'impresora_ip',
        'cajon_comando_apertura', 'bascula_activada', 'bascula_baud_rate', 'modo_simulado',
        'mostrar_stock',
    ];

    protected $casts = [
        'bascula_activada' => 'boolean',
        'modo_simulado'    => 'boolean',
        'mostrar_stock'    => 'boolean',
    ];

    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}