<?php

namespace App\Modules\Categorias\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;

class RegistroCategoriaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categorÃ­a es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto vÃ¡lido.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
            'descripcion.required' => 'La descripciÃ³n es obligatoria.',
            'descripcion.string' => 'La descripciÃ³n debe ser un texto vÃ¡lido.',
        ];
    }
}
