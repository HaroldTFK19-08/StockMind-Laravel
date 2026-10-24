<?php

namespace App\Modules\Prestamos\Requests;

use App\Modules\Prestamos\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPrestamoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $prestamo = Loan::find($this->route('prestamo'));
        $fechaPrestamo = $this->input(
            'fecha_prestamo',
            $prestamo?->fecha_prestamo?->toDateTimeString()
        );

        return [
            'element_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('elements', 'id'),
            ],
            'teacher_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
            ],
            'fecha_prestamo' => [
                'sometimes',
                'required',
                'date',
            ],
            'fecha_devolucion_esperada' => [
                'sometimes',
                'required',
                'date',
                ...($fechaPrestamo === null
                    ? []
                    : ['after_or_equal:'.$fechaPrestamo]),
            ],
            'fecha_devolucion_real' => [
                'sometimes',
                'nullable',
                'date',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['activo', 'devuelto', 'vencido']),
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
            'teacher_id.required' => 'El instructor es obligatorio.',
            'teacher_id.integer' => 'El instructor seleccionado no es válido.',
            'teacher_id.exists' => 'El instructor seleccionado no existe.',
            'fecha_prestamo.required' => 'La fecha del préstamo es obligatoria.',
            'fecha_prestamo.date' => 'La fecha del préstamo no es válida.',
            'fecha_devolucion_esperada.required' => 'La fecha de devolución esperada es obligatoria.',
            'fecha_devolucion_esperada.date' => 'La fecha de devolución esperada no es válida.',
            'fecha_devolucion_esperada.after_or_equal' => 'La fecha de devolución debe ser igual o posterior a la fecha del préstamo.',
            'fecha_devolucion_real.date' => 'La fecha de devolución real no es válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, devuelto o vencido.',
            'observaciones.string' => 'Las observaciones deben ser un texto válido.',
            'observaciones.max' => 'Las observaciones no pueden superar los 255 caracteres.',
        ];
    }
}
