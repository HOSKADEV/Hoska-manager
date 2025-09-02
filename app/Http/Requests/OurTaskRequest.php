<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class OurTaskRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'title' => [
                'required',
            ],
            'cost' => [
                'required',
            ],
            'duration' => [
                'required',
            ],
            'description' => [
                'nullable',
            ],
            'project_id' => ['required', 'exists:projects,id'],
        ];
    }
}
