<?php

namespace App\Modules\Roles\Models;

use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    // La columna en la migración se llama "rol", no "nombre"
    protected $fillable = [
        'rol',
        'descripcion',
    ];

    // Pivote user_rol: las columnas son users_id y rol_id (no siguen la convención de Laravel)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_rol', 'rol_id', 'users_id')
            ->withTimestamps();
    }
}
