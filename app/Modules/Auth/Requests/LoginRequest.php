<?php

namespace App\Modules\Auth\Requests;

use App\Modules\Shared\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
            'codigo' => ['nullable', 'string', 'digits:6'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no es vÃ¡lido.',
            'password.required' => 'La contraseÃ±a es obligatoria.',
            'codigo.digits' => 'El código debe tener 6 dígitos.',
        ];
    }
}
