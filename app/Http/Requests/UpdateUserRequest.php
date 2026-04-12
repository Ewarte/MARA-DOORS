<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
            'role' => 'required|exists:roles,name',
            'almacen_id' => 'nullable|exists:almacenes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email ya está registrado',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password_confirmation.required_with' => 'La confirmación de contraseña es requerida',
            'role.required' => 'El rol es requerido',
            'almacen_id.exists' => 'El almacén seleccionado no existe',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        // Remover password_confirmation y role del resultado validado
        unset($validated['password_confirmation']);
        unset($validated['role']);
        return $validated;
    }
}
