<?php

namespace App\Modules\Centros\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarCentroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'foto_centro' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'regional' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'ciudad' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'foto_centro.image' => 'El archivo debe ser una imagen válida.',
            'foto_centro.mimes' => 'La foto debe ser JPG, JPEG, PNG o WEBP.',
            'foto_centro.max' => 'La foto no puede superar los 5 MB.',

            'nombre.required' => 'El nombre del centro es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',

            'regional.required' => 'La regional es obligatoria.',
            'regional.string' => 'La regional debe ser un texto válido.',
            'regional.max' => 'La regional no puede superar los 150 caracteres.',

            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.string' => 'La ciudad debe ser un texto válido.',
            'ciudad.max' => 'La ciudad no puede superar los 100 caracteres.',
        ];
    }
}
