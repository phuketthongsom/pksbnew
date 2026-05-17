<?php

namespace App\Http\Requests\Admin\Concerns;

/**
 * Pream's note for the team:
 * Shared validation snippet for the per-locale translation tabs on Posts.
 * EN required (it's the fallback), the rest optional. Keeps Store/Update
 * requests in sync — change the translatable fields here, both inherit it.
 */
trait HasTranslationRules
{
    protected function translationRules(): array
    {
        return [
            'translations.en.title' => 'required|string|max:200',
            'translations.en.excerpt' => 'required|string|max:300',
            'translations.en.body' => 'required|string',
            'translations.en.nearest_stop' => 'required|string|max:200',
            'translations.th.title' => 'nullable|string|max:200',
            'translations.th.excerpt' => 'nullable|string|max:300',
            'translations.th.body' => 'nullable|string',
            'translations.th.nearest_stop' => 'nullable|string|max:200',
            'translations.zh.title' => 'nullable|string|max:200',
            'translations.zh.excerpt' => 'nullable|string|max:300',
            'translations.zh.body' => 'nullable|string',
            'translations.zh.nearest_stop' => 'nullable|string|max:200',
            'translations.ru.title' => 'nullable|string|max:200',
            'translations.ru.excerpt' => 'nullable|string|max:300',
            'translations.ru.body' => 'nullable|string',
            'translations.ru.nearest_stop' => 'nullable|string|max:200',
        ];
    }
}
