<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'foto_perfil' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'nombre_1' => ['required', 'string', 'max:100'],
            'nombre_2' => ['nullable', 'string', 'max:100'],

            'apellido_1' => ['required', 'string', 'max:100'],
            'apellido_2' => ['nullable', 'string', 'max:100'],

            'tipo_identidad' => [
                'required',
                'in:CC,TI,CE',
            ],

            'numero_identidad' => [
                'required',
                'string',
                'max:30',
                'unique:users,numero_identidad',
            ],

            'telefono' => [
                'required',
                'string',
                'max:20',
                'unique:users,telefono',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'foto_perfil.image' => 'La foto de perfil debe ser una imagen válida.',
            'foto_perfil.mimes' => 'La foto de perfil solo puede estar en formato PNG.',
            'foto_perfil.max' => 'La foto de perfil no puede superar los 2 MB.',
            'nombre_1.required' => 'El primer nombre es obligatorio.',
            'nombre_2.string' => 'El segundo nombre debe ser texto.',
            'apellido_1.required' => 'El primer apellido es obligatorio.',
            'apellido_2.string' => 'El segundo apellido debe ser texto.',
            'tipo_identidad.required' => 'El tipo de identidad es obligatorio.',
            'tipo_identidad.in' => 'El tipo de identidad debe ser CC, TI o CE.',
            'numero_identidad.required' => 'El número de identidad es obligatorio.',
            'numero_identidad.unique' => 'Este número de identidad ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'Este teléfono ya está registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
