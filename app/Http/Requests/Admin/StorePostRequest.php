<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasTranslationRules;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    use HasTranslationRules;

    public function authorize(): bool
    {
        return current_admin_can('posts.manage');
    }

    public function rules(): array
    {
        return $this->translationRules() + [
            'slug' => 'nullable|string|max:200',
            'area' => 'required|string|max:100',
            'route_recommendation' => 'required|in:all,rawai,patong,dragon',
            'reading_minutes' => 'required|integer|min:1|max:60',
            'published_at' => 'required|date',
            'category' => 'nullable|string|max:100',
            'photos.*' => 'nullable|image|max:8192',
        ];
    }
}
