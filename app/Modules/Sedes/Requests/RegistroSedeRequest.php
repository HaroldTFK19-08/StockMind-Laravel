<?php

namespace App\Modules\Sedes\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trainingcenter_id' => [
                'required',
                'integer',
                Rule::exists('trainingcenters', 'id'),
            ],
            'nombre' => [
                'required',
                'string',
                'max:100',
            ],
            'direccion' => [
                'required',
                'string',
                'max:150',
            ],
            'zona' => [
                'required',
                Rule::in(['urbana', 'rural']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'trainingcenter_id.required' => 'El centro de formación es obligatorio.',
            'trainingcenter_id.integer' => 'El centro seleccionado no es válido.',
            'trainingcenter_id.exists' => 'El centro de formación seleccionado no existe.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.string' => 'La dirección debe ser un texto válido.',
            'direccion.max' => 'La dirección no puede superar los 150 caracteres.',
            'zona.required' => 'La zona es obligatoria.',
            'zona.in' => 'La zona debe ser urbana o rural.',
        ];
    }
}
