<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevelopmentRequest extends FormRequest
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
        // $rules = 'nullable|unique:developments,description';
        // $rule_rate = 'nullable|numeric|min:0';

        // if ($this->method() != 'POST') {
        //     $rules = 'nullable|unique:developments,description,' . $this->development->id;
        //     // فقط تحقق من الرقم ولا تجبره أن يكون فريدًا
        //     $rule_rate = 'nullable|numeric|min:0';
        // }

        return [
            'description' => 'nullable',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:EUR,USD,DZD',
            'start_date' => 'nullable|date',
            'duration_days' => 'nullable|integer|min:0',
            'delivery_date' => 'nullable|date|after_or_equal:start_date',
            'project_id' => 'required|exists:projects,id',
        ];
    }
}
