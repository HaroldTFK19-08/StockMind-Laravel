<?php

namespace App\Modules\Sedes\Models;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Ambientes\Models\Environment;
use App\Modules\Centros\Models\Trainingcenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Headquarter extends Model
{
    use HasFactory;

    protected $table = 'headquarters';

    protected $fillable = [
        'trainingcenter_id',
        'nombre',
        'direccion',
        'zona',
    ];

    public function trainingcenter(): BelongsTo
    {
        return $this->belongsTo(Trainingcenter::class, 'trainingcenter_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'headquarter_id');
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class, 'headquarter_id');
    }
}
