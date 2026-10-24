<?php

namespace App\Modules\Usuarios\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto_perfil' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'nombre_1' => [
                'required',
                'string',
                'max:255',
            ],
            'nombre_2' => [
                'required',
                'string',
                'max:255',
            ],
            'apellido_1' => [
                'required',
                'string',
                'max:255',
            ],
            'apellido_2' => [
                'required',
                'string',
                'max:255',
            ],
            'tipo_identidad' => [
                'required',
                Rule::in(['CC', 'TI', 'CE']),
            ],
            'numero_identidad' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'numero_identidad'),
            ],
            'telefono' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'telefono'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in(['Activo', 'Inactivo']),
            ],
            'role_ids' => [
                'required',
                'array',
                'min:1',
                'max:2',
            ],
            'role_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('roles', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'foto_perfil.required' => 'Debes seleccionar una foto de perfil.',
            'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
            'foto_perfil.mimes' => 'La foto debe ser JPG, JPEG, PNG o WEBP.',
            'foto_perfil.max' => 'La foto no puede superar los 5 MB.',
            'nombre_1.required' => 'El primer nombre es obligatorio.',
            'nombre_1.string' => 'El primer nombre debe ser un texto válido.',
            'nombre_1.max' => 'El primer nombre no puede superar los 255 caracteres.',
            'nombre_2.required' => 'El segundo nombre es obligatorio.',
            'nombre_2.string' => 'El segundo nombre debe ser un texto válido.',
            'nombre_2.max' => 'El segundo nombre no puede superar los 255 caracteres.',
            'apellido_1.required' => 'El primer apellido es obligatorio.',
            'apellido_1.string' => 'El primer apellido debe ser un texto válido.',
            'apellido_1.max' => 'El primer apellido no puede superar los 255 caracteres.',
            'apellido_2.required' => 'El segundo apellido es obligatorio.',
            'apellido_2.string' => 'El segundo apellido debe ser un texto válido.',
            'apellido_2.max' => 'El segundo apellido no puede superar los 255 caracteres.',
            'tipo_identidad.required' => 'El tipo de identidad es obligatorio.',
            'tipo_identidad.in' => 'El tipo de identidad debe ser CC, TI o CE.',
            'numero_identidad.required' => 'El número de identidad es obligatorio.',
            'numero_identidad.unique' => 'El número de identidad ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'El teléfono ya está registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
            'role_ids.required' => 'Debes asignar al menos un rol.',
            'role_ids.array' => 'Los roles deben enviarse en una lista.',
            'role_ids.min' => 'Debes asignar al menos un rol.',
            'role_ids.max' => 'Un usuario puede tener máximo dos roles.',
            'role_ids.*.required' => 'Cada rol asignado es obligatorio.',
            'role_ids.*.integer' => 'Uno de los roles seleccionados no es válido.',
            'role_ids.*.distinct' => 'No puedes repetir roles.',
            'role_ids.*.exists' => 'Uno de los roles seleccionados no existe.',
        ];
    }
}
