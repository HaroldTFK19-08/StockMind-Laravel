<?php

namespace App\Modules\Movimientos\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transfer_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('transfers', 'id'),
                Rule::unique('motions', 'transfer_id'),
            ],
            'tipo' => [
                'required',
                Rule::in(['traslado', 'Ingreso', 'Retiro', 'Baja']),
            ],
            'motivo' => [
                'required',
                'string',
                'max:255',
            ],
            'fecha_movimiento' => [
                'sometimes',
                'required',
                'date',
            ],
            'element_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'element_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('elements', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'transfer_id.integer' => 'El traslado seleccionado no es válido.',
            'transfer_id.exists' => 'El traslado seleccionado no existe.',
            'transfer_id.unique' => 'Este traslado ya tiene un movimiento asociado.',
            'tipo.required' => 'El tipo de movimiento es obligatorio.',
            'tipo.in' => 'El tipo debe ser traslado, Ingreso, Retiro o Baja.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.string' => 'El motivo debe ser un texto válido.',
            'motivo.max' => 'El motivo no puede superar los 255 caracteres.',
            'fecha_movimiento.required' => 'La fecha del movimiento es obligatoria.',
            'fecha_movimiento.date' => 'La fecha del movimiento no es válida.',
            'element_ids.required' => 'Debes asociar al menos un elemento.',
            'element_ids.array' => 'Los elementos deben enviarse en una lista.',
            'element_ids.min' => 'Debes asociar al menos un elemento.',
            'element_ids.*.required' => 'Cada elemento asociado es obligatorio.',
            'element_ids.*.integer' => 'Un elemento seleccionado no es válido.',
            'element_ids.*.distinct' => 'No puedes repetir elementos en un movimiento.',
            'element_ids.*.exists' => 'Uno de los elementos seleccionados no existe.',
        ];
    }
}
