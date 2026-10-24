<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Reportes\Models\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apprentice extends Model
{
    use HasFactory;

    protected $table = 'apprentices';

    protected $fillable = [
        'user_id',
        'coursegroup_id',
        'estado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coursegroup(): BelongsTo
    {
        return $this->belongsTo(Coursegroup::class, 'coursegroup_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'apprentice_id');
    }

    // La FK apprentice_id está en reports, así que es hasMany (no belongsTo)
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'apprentice_id');
    }
}
