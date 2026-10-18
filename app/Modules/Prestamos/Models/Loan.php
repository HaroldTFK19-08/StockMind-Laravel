<?php

namespace App\Modules\Prestamos\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Usuarios\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';

    protected $fillable = [
        'element_id',
        'teacher_id',
        'fecha_prestamo',
        'fecha_devolucion_esperada',
        'fecha_devolucion_real',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_prestamo' => 'datetime',
        'fecha_devolucion_esperada' => 'date',
        'fecha_devolucion_real' => 'datetime',
    ];

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'element_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
