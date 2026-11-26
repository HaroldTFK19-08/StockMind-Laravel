<?php

namespace App\Modules\Sedes\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RegistroSedeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'trainingcenter_id' => [
                'required',
                'integer',
                Rule::exists('trainingcenters', 'id'),
            ],
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:150',
            'zona' => [
                'required',
                Rule::in(['urbana', 'rural']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'trainingcenter_id.required' => 'El centro de formaciÃ³n es obligatorio.',
            'trainingcenter_id.integer' => 'El centro seleccionado no es vÃ¡lido.',
            'trainingcenter_id.exists' => 'El centro de formaciÃ³n seleccionado no existe.',
            'nombre.required' => 'El nombre de la sede es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto vÃ¡lido.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'direccion.required' => 'La direcciÃ³n es obligatoria.',
            'direccion.string' => 'La direcciÃ³n debe ser un texto vÃ¡lido.',
            'direccion.max' => 'La direcciÃ³n no puede superar los 150 caracteres.',
            'zona.required' => 'La zona es obligatoria.',
            'zona.in' => 'La zona debe ser urbana o rural.',
        ];
    }
}
