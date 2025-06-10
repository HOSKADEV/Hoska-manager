<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
        $rule_number = 'required|unique:invoices,invoice_number';
        $rule_amount = 'required|numeric|min:0';
        $rule_invoice_date = 'required|date';
        $rule_due_date = 'nullable|date|after_or_equal:invoice_date';

        if ($this->method() !== 'POST') {
            $rule_number = 'required|unique:invoices,invoice_number,' . $this->invoice->id;
        }

        return [
            'invoice_number' => $rule_number,
            // 'amount' => $rule_amount,
            'invoice_date' => $rule_invoice_date,
            'due_date' => $rule_due_date,
        ];
    }
}
