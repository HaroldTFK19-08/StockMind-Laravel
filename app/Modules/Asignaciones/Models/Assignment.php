<?php

namespace App\Modules\Asignaciones\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = [
        'element_id',
        'apprentice_id',
        'teacher_id',
        'fecha_asignacion',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_devolucion_esperada' => 'date',
        'fecha_devolucion_real' => 'datetime',
    ];

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'element_id');
    }

    public function apprentice(): BelongsTo
    {
        return $this->belongsTo(Apprentice::class, 'apprentice_id');
    }

    // Instructor que hace la asignación
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
