<?php

namespace App\Modules\Roles\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('role');

        return [
            'rol' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['Aprendiz', 'Instructor', 'Cuenta-dante']),
                Rule::unique('roles', 'rol')->ignore($id),
            ],
            'descripcion' => [
                'sometimes',
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rol.required' => 'El rol es obligatorio.',
            'rol.string' => 'El rol debe ser un texto válido.',
            'rol.in' => 'El rol debe ser Aprendiz, Instructor o Cuenta-dante.',
            'rol.unique' => 'Este rol ya está registrado.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
        ];
    }
}
