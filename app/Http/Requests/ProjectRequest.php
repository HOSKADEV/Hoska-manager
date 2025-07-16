<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
        $rules = 'nullable|unique:projects,name';

        if ($this->method() != 'POST') {
            $rules = 'nullable|unique:projects,name,' . $this->project->id;
        }

        return [
            'name' => $rules,
            'description' => 'nullable|string',
            'total_amount' => 'nullable|numeric',
            'currency' => 'required|in:EUR,USD,DZD',

            // Attachments
            'attachment' => 'nullable|array',
            'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:10240',

            // Employees
            'employee_id' => 'nullable|array',
            'employee_id.*' => 'exists:employees,id',

            // New fields
            'start_date' => 'nullable|date',
            'duration_days' => 'nullable|integer|min:1',
            'delivery_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
