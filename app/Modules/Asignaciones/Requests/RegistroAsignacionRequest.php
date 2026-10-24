<?php

namespace App\Modules\Asignaciones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'element_id' => [
                'required',
                'integer',
                Rule::exists('elements', 'id'),
            ],
            'apprentice_id' => [
                'required',
                'integer',
                Rule::exists('apprentices', 'id'),
            ],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'fecha_asignacion' => [
                'sometimes',
                'required',
                'date',
            ],
            'fecha_devolucion_esperada' => [
                'required',
                'date',
            ],
            'fecha_devolucion_real' => [
                'sometimes',
                'nullable',
                'date',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['activa', 'devuelta', 'vencida']),
            ],
            'observaciones' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'element_id.required' => 'El elemento es obligatorio.',
            'element_id.integer' => 'El elemento seleccionado no es válido.',
            'element_id.exists' => 'El elemento seleccionado no existe.',
            'apprentice_id.required' => 'El aprendiz es obligatorio.',
            'apprentice_id.integer' => 'El aprendiz seleccionado no es válido.',
            'apprentice_id.exists' => 'El aprendiz seleccionado no existe.',
            'teacher_id.required' => 'El instructor es obligatorio.',
            'teacher_id.integer' => 'El instructor seleccionado no es válido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'fecha_asignacion.required' => 'La fecha de asignación es obligatoria.',
            'fecha_asignacion.date' => 'La fecha de asignación no es válida.',
            'fecha_devolucion_esperada.required' => 'La fecha de devolución esperada es obligatoria.',
            'fecha_devolucion_esperada.date' => 'La fecha de devolución esperada no es válida.',
            'fecha_devolucion_real.date' => 'La fecha de devolución real no es válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activa, devuelta o vencida.',
            'observaciones.string' => 'Las observaciones deben ser un texto válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ];
    }
}
