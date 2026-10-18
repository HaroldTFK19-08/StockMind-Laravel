<?php

namespace App\Modules\Categorias\Models;

use App\Modules\Elementos\Models\Element;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class, 'category_id');
    }
}
