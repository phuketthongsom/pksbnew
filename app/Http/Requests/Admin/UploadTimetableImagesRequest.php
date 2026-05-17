<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadTimetableImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return current_admin_can('timetables.manage');
    }

    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:8192',
            'caption' => 'nullable|string|max:500',
        ];
    }
}
