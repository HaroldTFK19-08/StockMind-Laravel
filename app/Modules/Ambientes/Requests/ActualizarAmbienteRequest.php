<?php

namespace App\Modules\Ambientes\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarAmbienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'headquarter_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('headquarters', 'id'),
            ],
            'teacher_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'capacidad' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],
            'tipo' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
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
            'headquarter_id.integer' => 'La sede seleccionada no es válida.',
            'headquarter_id.exists' => 'La sede seleccionada no existe.',
            'teacher_id.integer' => 'El instructor seleccionado no es válido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'nombre.required' => 'El nombre del ambiente es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.integer' => 'La capacidad debe ser un número entero.',
            'capacidad.min' => 'La capacidad no puede ser negativa.',
            'capacidad.max' => 'La capacidad no puede superar 65535.',
            'tipo.required' => 'El tipo de ambiente es obligatorio.',
            'tipo.string' => 'El tipo debe ser un texto válido.',
            'tipo.max' => 'El tipo no puede superar los 50 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
        ];
    }
}
