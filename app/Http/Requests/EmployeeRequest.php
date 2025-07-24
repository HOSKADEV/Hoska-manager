<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $employeeId = $this->employee->id ?? null;

        return [
            'name' => [
                'required',
                Rule::unique('employees', 'name')->ignore($employeeId),
            ],
            'rate' => ['required', 'numeric', 'min:0'],
            'payment_type' => ['required', Rule::in(['hourly', 'monthly', 'per_project'])],
            'currency' => 'required|in:EUR,USD,DZD',
            // معلومات الدفع البنكية - اختيارية
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:255'],
            'bank_code' => ['nullable', 'string', 'max:255'],

            // بيانات حساب الدخول للمستخدم (user)
            'user.name' => ['nullable', 'string', 'max:255'],
            'user.email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($this->employee->user->id ?? null),
            ],
            'user.password' => [
                // كلمة السر اختيارية، لكن إذا دخلت يجب أن تكون قوية على الأقل 6 حروف
                'nullable',
                'string',
                'min:6',
                // 'confirmed', // إذا كنت تستخدم حقل password_confirmation
            ],
            'user.is_marketer' => ['nullable', 'boolean'], // حالة التسويق اختيارية
            'user.is_accountant' => ['nullable', 'boolean'], // حالة المحاسبة اختيارية
        ];
    }
}
