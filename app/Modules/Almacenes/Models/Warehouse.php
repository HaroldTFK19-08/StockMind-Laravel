<?php

namespace App\Modules\Almacenes\Models;

use App\Modules\Elementos\Models\Element;
use App\Modules\Sedes\Models\Headquarter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    protected $fillable = [
        'headquarter_id',
        'nombre',
        'capacidad_elementos',
        'estado',
    ];

    // La migración relaciona el almacén con la sede (headquarter_id), no con el centro
    public function headquarter(): BelongsTo
    {
        return $this->belongsTo(Headquarter::class, 'headquarter_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class, 'warehouse_id');
    }
}
