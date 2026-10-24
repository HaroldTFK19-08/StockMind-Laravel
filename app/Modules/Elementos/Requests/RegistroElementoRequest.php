<?php

namespace App\Modules\Elementos\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroElementoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'environment_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('environments', 'id'),
            ],
            'warehouse_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id'),
            ],
            'codigo_inventario' => [
                'required',
                'string',
                'max:30',
                Rule::unique('elements', 'codigo_inventario'),
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'img_elemento' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'descripcion' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
            'serial' => [
                'sometimes',
                'nullable',
                'string',
                'max:60',
                Rule::unique('elements', 'serial'),
            ],
            'marca' => [
                'sometimes',
                'nullable',
                'string',
                'max:60',
            ],
            'modelo' => [
                'sometimes',
                'nullable',
                'string',
                'max:60',
            ],
            'estado' => [
                'sometimes',
                'required',
                Rule::in([
                    'disponible',
                    'asignado',
                    'prestado',
                    'en traslado',
                    'en mantenimiento',
                    'dado de baja',
                ]),
            ],
            'condicion' => [
                'sometimes',
                'required',
                Rule::in(['bueno', 'regular', 'malo']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.integer' => 'La categoría seleccionada no es válida.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'environment_id.integer' => 'El ambiente seleccionado no es válido.',
            'environment_id.exists' => 'El ambiente seleccionado no existe.',
            'warehouse_id.integer' => 'El almacén seleccionado no es válido.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',
            'codigo_inventario.required' => 'El código de inventario es obligatorio.',
            'codigo_inventario.string' => 'El código de inventario debe ser un texto válido.',
            'codigo_inventario.max' => 'El código no puede superar los 30 caracteres.',
            'codigo_inventario.unique' => 'El código de inventario ya está registrado.',
            'nombre.required' => 'El nombre del elemento es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto válido.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'img_elemento.required' => 'Debes seleccionar una imagen del elemento.',
            'img_elemento.image' => 'El archivo debe ser una imagen válida.',
            'img_elemento.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'img_elemento.max' => 'La imagen no puede superar los 5 MB.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
            'serial.string' => 'El serial debe ser un texto válido.',
            'serial.max' => 'El serial no puede superar los 60 caracteres.',
            'serial.unique' => 'El serial ya está registrado.',
            'marca.string' => 'La marca debe ser un texto válido.',
            'marca.max' => 'La marca no puede superar los 60 caracteres.',
            'modelo.string' => 'El modelo debe ser un texto válido.',
            'modelo.max' => 'El modelo no puede superar los 60 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'condicion.required' => 'La condición es obligatoria.',
            'condicion.in' => 'La condición debe ser bueno, regular o malo.',
        ];
    }
}
