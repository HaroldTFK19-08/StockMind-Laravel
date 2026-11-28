<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificarCodigoAccesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:150'],
            'codigo' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato v?lido.',
            'codigo.required' => 'El c?digo es obligatorio.',
            'codigo.digits' => 'El c?digo debe tener 6 d?gitos.',
        ];
    }
}
