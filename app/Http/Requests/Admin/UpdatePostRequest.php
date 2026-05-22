<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\HasTranslationRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Translators get the same form, but only translation fields stick. We loosen
 * the metadata rules to nullable for them — the controller then ignores those
 * fields on save (defence in depth).
 */
class UpdatePostRequest extends FormRequest
{
    use HasTranslationRules;

    public function authorize(): bool
    {
        // Anyone with translations.edit can hit this — controller scrubs
        // non-translation fields for translator-only users.
        return current_admin_can('translations.edit') || current_admin_can('posts.manage');
    }

    public function rules(): array
    {
        $slug = $this->route('slug');
        $isTranslatorOnly = !current_admin_can('posts.manage') && current_admin_can('translations.edit');

        return $this->translationRules() + [
            'area' => $isTranslatorOnly ? 'nullable' : 'required|string|max:100',
            'route_recommendation' => $isTranslatorOnly ? 'nullable' : 'required|in:all,rawai,patong,dragon',
            'reading_minutes' => $isTranslatorOnly ? 'nullable' : 'required|integer|min:1|max:60',
            'published_at' => $isTranslatorOnly ? 'nullable' : 'required|date',
            // SECURITY: cover must be one of (a) the bundled hero, or
            // (b) a file inside this slug's gallery folder.
            'category' => 'nullable|string|max:100',
            'cover' => ['nullable', 'string', 'regex:#^(images/[a-z0-9_\-./]+|storage/destinations/'.preg_quote($slug, '#').'/[a-zA-Z0-9._\-/]+)$#'],
            'photos.*' => 'nullable|image|max:8192',
        ];
    }
}
