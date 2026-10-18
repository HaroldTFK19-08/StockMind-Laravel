<?php

namespace App\Modules\Reports\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Usuarios\Models\Apprentice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'apprentice_id',
        'tipo',
        'descripcion',
        'fecha_reporte',
        'foto_probatorio',
        'estado',
    ];

    protected $casts = [
        'fecha_reporte' => 'datetime',
    ];

    public function apprentice(): BelongsTo
    {
        return $this->belongsTo(Apprentice::class, 'apprentice_id');
    }

    public function elements(): BelongsToMany
    {
        return $this->belongsToMany(Element::class, 'element_report', 'report_id', 'element_id')
            ->withTimestamps();
    }
}
