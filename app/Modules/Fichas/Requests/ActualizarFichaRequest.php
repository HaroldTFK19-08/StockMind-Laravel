<?php

namespace App\Modules\Fichas\Requests;

use App\Modules\Fichas\Models\Coursegroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarFichaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('ficha');
        $ficha = Coursegroup::find($id);
        $fechaInicio = $this->input(
            'fecha_inicio',
            $ficha?->fecha_inicio?->toDateString()
        );

        return [
            'teacher_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'codigo' => [
                'sometimes',
                'required',
                'string',
                'max:15',
                Rule::unique('coursegroups', 'codigo')->ignore($id),
            ],
            'programa' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'fecha_inicio' => [
                'sometimes',
                'required',
                'date',
            ],
            'fecha_fin' => [
                'sometimes',
                'required',
                'date',
                ...($fechaInicio === null
                    ? []
                    : ['after_or_equal:'.$fechaInicio]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'El instructor es obligatorio.',
            'teacher_id.integer' => 'El instructor seleccionado no es válido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'codigo.required' => 'El código de la ficha es obligatorio.',
            'codigo.string' => 'El código debe ser un texto válido.',
            'codigo.max' => 'El código no puede superar los 15 caracteres.',
            'codigo.unique' => 'El código de ficha ya está registrado.',
            'programa.required' => 'El programa es obligatorio.',
            'programa.string' => 'El programa debe ser un texto válido.',
            'programa.max' => 'El programa no puede superar los 150 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
