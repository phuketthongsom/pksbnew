<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('passes.manage');
    }

    public function rules(): array
    {
        return [
            'translations.en.name' => 'required|string|max:120',
            'translations.en.description' => 'nullable|string|max:300',
            'translations.th.name' => 'nullable|string|max:120',
            'translations.th.description' => 'nullable|string|max:300',
            'translations.zh.name' => 'nullable|string|max:120',
            'translations.zh.description' => 'nullable|string|max:300',
            'translations.ru.name' => 'nullable|string|max:120',
            'translations.ru.description' => 'nullable|string|max:300',
            'price' => 'required|integer|min:0|max:1000000',
            'duration_days' => 'required|integer|min:1|max:365',
            'cover_action' => 'nullable|in:keep,clear',
            'cover' => 'nullable|image|max:8192',
        ];
    }
}
