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
        $rules = 'required|unique:employees,name';
        $rule_rate = 'required|unique:employees,rate';
        $rule_payment_type = 'required|in:hourly,monthly,per_project';

        if ($this->method() != 'POST') {
            $rules = 'required|unique:employees,name,' . $this->employee->id;
            $rule_rate = 'required|unique:employees,rate,' . $this->employee->id;
            $rule_payment_type = 'required|in:hourly,monthly,per_project,' . $this->employee->id;
        }
        return [
            'name' => $rules,
            'rate' => $rule_rate,
            "payment_type"=> $rule_payment_type,
        ];
    }
}
