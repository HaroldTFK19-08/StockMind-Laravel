<?php

namespace App\Modules\Movimientos\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Traslados\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Motion extends Model
{
    use HasFactory;

    protected $table = 'motions';

    protected $fillable = [
        'transfer_id',
        'tipo',
        'motivo',
        'fecha_movimiento',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    // 1:1 con traslados (nullable: solo los movimientos tipo "traslado")
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    public function elements(): BelongsToMany
    {
        return $this->belongsToMany(Element::class, 'element_motion', 'motion_id', 'element_id')
            ->withTimestamps();
    }
}
