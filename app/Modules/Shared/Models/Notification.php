<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Usuarios\Models\User;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'usuario_id',
        'titulo',
        'tipo',
        'mensaje',
        'leida',
    ];
    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
