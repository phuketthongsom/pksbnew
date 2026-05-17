<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('users.manage');
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:60|regex:/^[a-zA-Z0-9._-]+$/',
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
            'password' => 'required|string|min:10|max:120',
            'role' => 'required|in:owner,editor,translator',
            'is_active' => 'nullable|boolean',
        ];
    }
}
