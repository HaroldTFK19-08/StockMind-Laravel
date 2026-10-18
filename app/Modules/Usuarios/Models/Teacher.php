<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Prestamos\Models\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'tipo_instructor',
        'especialidad',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coursegroups(): HasMany
    {
        return $this->hasMany(Coursegroup::class, 'teacher_id');
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class, 'teacher_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }
}
