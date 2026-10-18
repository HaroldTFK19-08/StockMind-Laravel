<?php

namespace App\Modules\Usuarios\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Modules\Roles\Models\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'foto_perfil',
        'nombre_1',
        'nombre_2',
        'apellido_1',
        'apellido_2',
        'tipo_identidad',
        'numero_identidad',
        'telefono',
        'email',
        'password',
        'estado',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    // El modelo no está en App\Models, así que se indica la factory a mano
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
    // Pivote user_rol (hasta 2 roles por usuario)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'user_rol', 'users_id', 'rol_id')
            ->withTimestamps();
    }
    public function apprentice(): HasOne
    {
        return $this->hasOne(Apprentice::class, 'user_id');
    }
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }
    // user_id no es unique en danteaccounts: un usuario puede tener varios periodos/ambientes
    public function danteaccounts(): HasMany
    {
        return $this->hasMany(Danteaccount::class, 'user_id');
    }
}
