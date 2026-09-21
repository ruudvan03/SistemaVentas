<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'username',
        'password_hash',
        'rol',
        'activo',
        'tema',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'usuario_permiso', 'usuario_id', 'permission_id');
    }

    public function esAdmin(): bool
    {
        // BUG 7 CORREGIDO: el enum en BD solo permite 'administrador' y 'cajero'.
        // El valor 'admin' nunca puede existir, se elimina para evitar confusión.
        return strtolower($this->rol ?? '') === 'administrador';
    }

    /**
     * Verifica si el usuario cuenta con un permiso específico (individual, no por rol).
     */
    public function tienePermiso(string $slug): bool
    {
        // Super admin: bypass total
        if ($this->esAdmin()) {
            return true;
        }

        return $this->permissions()->where('slug', $slug)->exists();
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}