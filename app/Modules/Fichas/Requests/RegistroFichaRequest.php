<?php

namespace App\Modules\Fichas\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RegistroFichaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'codigo' => [
                'required',
                'string',
                'max:15',
                Rule::unique('coursegroups', 'codigo'),
            ],
            'programa' => 'required|string|max:150',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'El instructor es obligatorio.',
            'teacher_id.integer' => 'El instructor seleccionado no es vÃ¡lido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'codigo.required' => 'El cÃ³digo de la ficha es obligatorio.',
            'codigo.string' => 'El cÃ³digo debe ser un texto vÃ¡lido.',
            'codigo.max' => 'El cÃ³digo no puede superar los 15 caracteres.',
            'codigo.unique' => 'El cÃ³digo de ficha ya estÃ¡ registrado.',
            'programa.required' => 'El programa es obligatorio.',
            'programa.string' => 'El programa debe ser un texto vÃ¡lido.',
            'programa.max' => 'El programa no puede superar los 150 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio no es vÃ¡lida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin no es vÃ¡lida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
