<?php

namespace App\Modules\Centros\Models;

use App\Modules\Sedes\Models\Headquarter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainingcenter extends Model
{
    use HasFactory;

    protected $table = 'trainingcenters';

    protected $fillable = [
        'nombre',
        'foto_centro',
        'foto_centro_public_id',
        'regional',
        'ciudad',
    ];

    // Un centro de formación tiene muchas sedes
    public function headquarters(): HasMany
    {
        return $this->hasMany(Headquarter::class, 'trainingcenter_id');
    }
}
