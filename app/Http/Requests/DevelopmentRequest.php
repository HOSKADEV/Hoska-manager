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
        $rules = 'required|unique:developments,description';
        $rule_rate = 'required|unique:developments,amount';
        $rule_payment_type = 'required|in:hourly,monthly,per_project';

        if ($this->method() != 'POST') {
            $rules = 'required|unique:developments,description,' . $this->development->id;
            $rule_rate = 'required|unique:developments,amount,' . $this->development->id;
        }
        return [
            'description' => $rules,
            'amount' => $rule_rate,
        ];
    }
}
