<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
        $rules = 'nullable|unique:employees,name';
        $rule_rate = 'nullable|numeric|min:0';
        $rule_payment_type = 'nullable|in:hourly,monthly,per_project';

        if ($this->method() != 'POST') {
            $rules = 'nullable|unique:employees,name,' . $this->employee->id;
            // معدل الأجر يبقى شرطه كما هو (ليس unique)
            $rule_rate = 'nullable|numeric|min:0';
            // شرط نوع الدفع لا يتغير
            $rule_payment_type = 'nullable|in:hourly,monthly,per_project';
        }

        return [
            'name' => $rules,
            'rate' => $rule_rate,
            'payment_type' => $rule_payment_type,
        ];
    }
}
