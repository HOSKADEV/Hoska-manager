<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
        $rules = 'nullable|unique:clients,name';
        $rule = 'nullable|unique:clients,notes';

        if ($this->method() != 'POST') {
            $rules = 'nullable|unique:clients,name,' . $this->client->id;
            $rule = 'nullable|unique:clients,notes,' . $this->client->id;
        }
        return [
            'name' => $rules,
            'notes' => $rule,
        ];
    }
}
