<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
        $paymentId = $this->payment->id ?? null;

        return [
            // 'amount' => ['required'],
            'payment_date' => ['nullable', 'date'],
            'note' => [
                'nullable'
            ],
            'invoice_id' => ['required', 'exists:invoices,id'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
        ];
    }
}
