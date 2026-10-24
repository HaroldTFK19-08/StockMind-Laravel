<?php

namespace App\Modules\Traslados\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroTrasladoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ambiente_origen_id' => [
                'required',
                'integer',
                Rule::exists('environments', 'id'),
                'different:ambiente_destino_id',
            ],
            'ambiente_destino_id' => [
                'required',
                'integer',
                Rule::exists('environments', 'id'),
            ],
            'danteaccount_id' => [
                'required',
                'integer',
                Rule::exists('danteaccounts', 'id'),
            ],
            'motivo' => [
                'required',
                'string',
                'max:255',
            ],
            'fecha_solicitud' => [
                'sometimes',
                'required',
                'date',
            ],
            'fecha_traslado' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:fecha_solicitud',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['pendiente', 'aprobado', 'completado', 'rechazado']),
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
            'ambiente_origen_id.required' => 'El ambiente de origen es obligatorio.',
            'ambiente_origen_id.integer' => 'El ambiente de origen no es válido.',
            'ambiente_origen_id.exists' => 'El ambiente de origen no existe.',
            'ambiente_origen_id.different' => 'El ambiente de origen debe ser diferente al destino.',
            'ambiente_destino_id.required' => 'El ambiente de destino es obligatorio.',
            'ambiente_destino_id.integer' => 'El ambiente de destino no es válido.',
            'ambiente_destino_id.exists' => 'El ambiente de destino no existe.',
            'danteaccount_id.required' => 'La cuenta DANTE es obligatoria.',
            'danteaccount_id.integer' => 'La cuenta DANTE seleccionada no es válida.',
            'danteaccount_id.exists' => 'La cuenta DANTE seleccionada no existe.',
            'motivo.required' => 'El motivo del traslado es obligatorio.',
            'motivo.string' => 'El motivo debe ser un texto válido.',
            'motivo.max' => 'El motivo no puede superar los 255 caracteres.',
            'fecha_solicitud.required' => 'La fecha de solicitud es obligatoria.',
            'fecha_solicitud.date' => 'La fecha de solicitud no es válida.',
            'fecha_traslado.date' => 'La fecha del traslado no es válida.',
            'fecha_traslado.after_or_equal' => 'La fecha del traslado debe ser igual o posterior a la solicitud.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser pendiente, aprobado, completado o rechazado.',
            'element_ids.required' => 'Debes asociar al menos un elemento.',
            'element_ids.array' => 'Los elementos deben enviarse en una lista.',
            'element_ids.min' => 'Debes asociar al menos un elemento.',
            'element_ids.*.required' => 'Cada elemento asociado es obligatorio.',
            'element_ids.*.integer' => 'Un elemento seleccionado no es válido.',
            'element_ids.*.distinct' => 'No puedes repetir elementos en un traslado.',
            'element_ids.*.exists' => 'Uno de los elementos seleccionados no existe.',
        ];
    }
}
