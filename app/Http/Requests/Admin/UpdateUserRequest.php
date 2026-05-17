<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('users.manage');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
            'role' => 'required|in:owner,editor,translator',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:10|max:120',
        ];
    }
}
