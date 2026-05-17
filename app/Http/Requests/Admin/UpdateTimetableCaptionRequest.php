<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableCaptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('timetables.manage');
    }

    public function rules(): array
    {
        return [
            'caption.en' => 'nullable|string|max:500',
            'caption.th' => 'nullable|string|max:500',
            'caption.zh' => 'nullable|string|max:500',
            'caption.ru' => 'nullable|string|max:500',
        ];
    }
}
