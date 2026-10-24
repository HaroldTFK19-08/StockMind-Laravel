<?php

namespace App\Modules\Reportes\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'apprentice_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('apprentices', 'id'),
            ],
            'tipo' => [
                'sometimes',
                'required',
                Rule::in(['daño', 'perdida', 'falla', 'otro']),
            ],
            'descripcion' => [
                'sometimes',
                'required',
                'string',
                'max:500',
            ],
            'fecha_reporte' => [
                'sometimes',
                'required',
                'date',
            ],
            'foto_probatorio' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['pendiente', 'en_revision', 'resuelto']),
            ],
            'element_ids' => [
                'sometimes',
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
            'apprentice_id.required' => 'El aprendiz es obligatorio.',
            'apprentice_id.integer' => 'El aprendiz seleccionado no es válido.',
            'apprentice_id.exists' => 'El aprendiz seleccionado no existe.',
            'tipo.required' => 'El tipo de reporte es obligatorio.',
            'tipo.in' => 'El tipo debe ser daño, pérdida, falla u otro.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'fecha_reporte.required' => 'La fecha del reporte es obligatoria.',
            'fecha_reporte.date' => 'La fecha del reporte no es válida.',
            'foto_probatorio.image' => 'El archivo debe ser una imagen válida.',
            'foto_probatorio.mimes' => 'La foto debe ser JPG, JPEG, PNG o WEBP.',
            'foto_probatorio.max' => 'La foto no puede superar los 5 MB.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser pendiente, en_revision o resuelto.',
            'element_ids.required' => 'Debes asociar al menos un elemento.',
            'element_ids.array' => 'Los elementos deben enviarse en una lista.',
            'element_ids.min' => 'Debes asociar al menos un elemento.',
            'element_ids.*.required' => 'Cada elemento asociado es obligatorio.',
            'element_ids.*.integer' => 'Un elemento seleccionado no es válido.',
            'element_ids.*.distinct' => 'No puedes repetir elementos en un reporte.',
            'element_ids.*.exists' => 'Uno de los elementos seleccionados no existe.',
        ];
    }
}
