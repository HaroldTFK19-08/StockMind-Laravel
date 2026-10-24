<?php

namespace App\Modules\Categorias\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'descripcion' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ];
    }
}
