<?php

namespace App\Modules\Categorias\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ActualizarRequest extends FormRequest
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
        $id = $this->route('categoria');
        return [
            //
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'nombre')->ignore($id),
            ],
            'descripcion' => [
                'sometimes',
                'required',
                'string',
            ],
        ];
    }
}
