<?php

namespace App\Modules\Centros\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;

class RegistroCentroRequest extends BaseFormRequest
{    public function rules(): array
    {
        return [
            'foto_centro' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'nombre' => 'required|string|max:150',
            'regional' => 'required|string|max:150',
            'ciudad' => 'required|string|max:100',
        ];
    }
    public function messages(): array
    {
        return [
            'foto_centro.required' => 'Debes seleccionar una foto del centro.',
            'foto_centro.image' => 'El archivo debe ser una imagen vÃ¡lida.',
            'foto_centro.mimes' => 'La foto debe ser JPG, JPEG, PNG o WEBP.',
            'foto_centro.max' => 'La foto no puede superar los 5 MB.',
            'nombre.required' => 'El nombre del centro es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'regional.required' => 'La regional es obligatoria.',
            'regional.string' => 'La regional debe ser un texto vÃ¡lido.',
            'regional.max' => 'La regional no puede superar los 150 caracteres.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.string' => 'La ciudad debe ser un texto vÃ¡lido.',
            'ciudad.max' => 'La ciudad no puede superar los 100 caracteres.',
        ];
    }
}
