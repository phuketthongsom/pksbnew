<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('passes.manage');
    }

    public function rules(): array
    {
        return [
            'translations.en.name' => 'required|string|max:120',
            'price' => 'required|integer|min:0|max:1000000',
            'duration_days' => 'required|integer|min:1|max:365',
        ];
    }
}
