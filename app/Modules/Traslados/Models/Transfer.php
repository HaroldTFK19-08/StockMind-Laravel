<?php

namespace App\Modules\Traslados\Models;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Movimientos\Models\Motion;
use App\Modules\Usuarios\Models\Danteaccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';

    protected $fillable = [
        'ambiente_origen_id',
        'ambiente_destino_id',
        'danteaccount_id',
        'motivo',
        'fecha_solicitud',
        'fecha_traslado',
        'estado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_traslado' => 'datetime',
    ];

    // Dos FK hacia environments: hay que indicar la columna en cada relación
    public function originEnvironment(): BelongsTo
    {
        return $this->belongsTo(Environment::class, 'ambiente_origen_id');
    }

    public function destinationEnvironment(): BelongsTo
    {
        return $this->belongsTo(Environment::class, 'ambiente_destino_id');
    }

    public function danteaccount(): BelongsTo
    {
        return $this->belongsTo(Danteaccount::class, 'danteaccount_id');
    }

    public function motion(): HasOne
    {
        return $this->hasOne(Motion::class, 'transfer_id');
    }
}
