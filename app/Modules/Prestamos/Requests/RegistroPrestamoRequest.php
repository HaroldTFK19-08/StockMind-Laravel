<?php

namespace App\Modules\Prestamos\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RegistroPrestamoRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->exists('fecha_prestamo')) {
            $this->merge(['fecha_prestamo' => now()->toDateTimeString()]);
        }
    }

    public function rules(): array
    {
        return [
            'element_id' => [
                'required',
                'integer',
                Rule::exists('elements', 'id'),
            ],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'fecha_prestamo' => 'sometimes|required|date',
            'fecha_devolucion_esperada' => 'required|date|after_or_equal:fecha_prestamo',
            'fecha_devolucion_real' => 'sometimes|nullable|date',
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['activo', 'devuelto', 'vencido']),
            ],
            'observaciones' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'element_id.required' => 'El elemento es obligatorio.',
            'element_id.integer' => 'El elemento seleccionado no es vÃ¡lido.',
            'element_id.exists' => 'El elemento seleccionado no existe.',
            'teacher_id.required' => 'El instructor es obligatorio.',
            'teacher_id.integer' => 'El instructor seleccionado no es vÃ¡lido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'fecha_prestamo.required' => 'La fecha del prÃ©stamo es obligatoria.',
            'fecha_prestamo.date' => 'La fecha del prÃ©stamo no es vÃ¡lida.',
            'fecha_devolucion_esperada.required' => 'La fecha de devoluciÃ³n esperada es obligatoria.',
            'fecha_devolucion_esperada.date' => 'La fecha de devoluciÃ³n esperada no es vÃ¡lida.',
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devoluciÃ³n debe ser igual o posterior a la fecha del prÃ©stamo.',
            'fecha_devolucion_real.date' => 'La fecha de devoluciÃ³n real no es vÃ¡lida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, devuelto o vencido.',
            'observaciones.string' => 'Las observaciones deben ser un texto vÃ¡lido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ];
    }
}
