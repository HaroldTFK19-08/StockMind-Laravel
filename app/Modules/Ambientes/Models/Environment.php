<?php

namespace App\Modules\Ambientes\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Traslados\Models\Transfer;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Environment extends Model
{
    use HasFactory;

    protected $table = 'environments';

    protected $fillable = [
        'headquarter_id',
        'teacher_id',
        'nombre',
        'capacidad',
        'tipo',
        'estado',
    ];

    public function headquarter(): BelongsTo
    {
        return $this->belongsTo(Headquarter::class, 'headquarter_id');
    }

    // Instructor responsable (nullable)
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class, 'environment_id');
    }

    public function danteaccounts(): HasMany
    {
        return $this->hasMany(Danteaccount::class, 'environment_id');
    }

    // Traslados que salen de este ambiente
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'ambiente_origen_id');
    }

    // Traslados que llegan a este ambiente
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'ambiente_destino_id');
    }
}
