<?php

namespace App\Modules\Fichas\Models;

use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coursegroup extends Model
{
    use HasFactory;

    protected $table = 'coursegroups';

    protected $fillable = [
        'teacher_id',
        'codigo',
        'programa',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function apprentices(): HasMany
    {
        return $this->hasMany(Apprentice::class, 'coursegroup_id');
    }
}
