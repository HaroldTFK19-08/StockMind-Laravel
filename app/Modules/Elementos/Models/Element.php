<?php

namespace App\Modules\Elementos\Models;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Ambientes\Models\Environment;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Categorias\Models\Category;
use App\Modules\Movimientos\Models\Motion;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Reportes\Models\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Element extends Model
{
    use HasFactory;

    protected $table = 'elements';

    protected $fillable = [
        'category_id',
        'environment_id',
        'warehouse_id',
        'codigo_inventario',
        'nombre',
        'img_elemento',
        'img_elemento_public_id',
        'descripcion',
        'serial',
        'marca',
        'modelo',
        'estado',
        'condicion',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Ubicación: ambiente (nullable)
    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class, 'environment_id');
    }

    // Ubicación: almacén (nullable)
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'element_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'element_id');
    }

    // Pivote element_motion
    public function motions(): BelongsToMany
    {
        return $this->belongsToMany(Motion::class, 'element_motion', 'element_id', 'motion_id')
            ->withTimestamps();
    }

    // Pivote element_report
    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'element_report', 'element_id', 'report_id')
            ->withTimestamps();
    }
}
