<?php

namespace App\Modules\Almacenes\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RegistroAlmacenRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'headquarter_id' => [
                'required',
                'integer',
                Rule::exists('headquarters', 'id'),
            ],
            'nombre' => 'required|string|max:100',
            'capacidad_elementos' => 'required|integer|min:0',
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['activo', 'inactivo']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'headquarter_id.required' => 'La sede es obligatoria.',
            'headquarter_id.integer' => 'La sede seleccionada no es vÃ¡lida.',
            'headquarter_id.exists' => 'La sede seleccionada no existe.',
            'nombre.required' => 'El nombre del almacÃ©n es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto vÃ¡lido.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'capacidad_elementos.required' => 'La capacidad es obligatoria.',
            'capacidad_elementos.integer' => 'La capacidad debe ser un nÃºmero entero.',
            'capacidad_elementos.min' => 'La capacidad no puede ser negativa.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ];
    }
}
