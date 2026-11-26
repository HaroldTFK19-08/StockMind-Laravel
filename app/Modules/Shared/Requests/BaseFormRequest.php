<?php

namespace App\Modules\Shared\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base común de las validaciones de la API.
 *
 * La autorización no se decide aquí: los middleware de roles son la única
 * fuente de control de acceso, así que no repetimos authorize() 30 veces.
 */
abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
