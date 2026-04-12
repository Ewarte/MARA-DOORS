<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $producto = $this->route('producto');

        return [
            // ❌ NO validar código porque no se edita
            // 'codigo' => ...

            'nombre' => [
                'required',
                'max:80',
                Rule::unique('productos', 'nombre')->ignore($producto->id),
            ],

            'descripcion'   => 'nullable|string|max:255',

            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',

            'marca_id'      => 'required|exists:marcas,id',
            'tipounidad_id' => 'required|exists:tipo_unidades,id',
            'categoria_id'  => 'required|exists:categorias,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'         => 'nombre del producto',
            'descripcion'    => 'descripción',
            'precio_compra'  => 'precio de compra',
            'precio_venta'   => 'precio de venta',
            'marca_id'       => 'marca',
            'tipounidad_id'  => 'tipo de unidad',
            'categoria_id'   => 'categoría',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.unique'   => 'Ya existe un producto con este nombre.',

            'precio_compra.required' => 'El precio de compra es obligatorio.',
            'precio_compra.min'      => 'El precio de compra no puede ser negativo.',

            'precio_venta.required'  => 'El precio de venta es obligatorio.',
            'precio_venta.min'       => 'El precio de venta no puede ser negativo.',

            'marca_id.required'      => 'Debe seleccionar una marca.',
            'tipounidad_id.required' => 'Debe seleccionar un tipo de unidad.',
            'categoria_id.required'  => 'Debe seleccionar una categoría.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (
                $this->precio_compra !== null &&
                $this->precio_venta !== null &&
                $this->precio_venta < $this->precio_compra
            ) {
                $validator->errors()->add(
                    'precio_venta',
                    'El precio de venta no puede ser menor al precio de compra.'
                );
            }
        });
    }
}
