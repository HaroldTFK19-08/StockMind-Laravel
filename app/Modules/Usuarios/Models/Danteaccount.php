<?php

namespace App\Modules\Usuarios\Models;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Traslados\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Danteaccount extends Model
{
    use HasFactory;

    protected $table = 'danteaccounts';

    protected $fillable = [
        'user_id',
        'environment_id',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class, 'environment_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'danteaccount_id');
    }
}
