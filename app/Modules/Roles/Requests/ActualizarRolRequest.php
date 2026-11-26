<?php

namespace App\Modules\Roles\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ActualizarRolRequest extends BaseFormRequest
{
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
            'descripcion' => 'sometimes|required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'rol.required' => 'El rol es obligatorio.',
            'rol.string' => 'El rol debe ser un texto vÃ¡lido.',
            'rol.in' => 'El rol debe ser Aprendiz, Instructor o Cuenta-dante.',
            'rol.unique' => 'Este rol ya estÃ¡ registrado.',
            'descripcion.required' => 'La descripciÃ³n es obligatoria.',
            'descripcion.string' => 'La descripciÃ³n debe ser un texto vÃ¡lido.',
        ];
    }
}
