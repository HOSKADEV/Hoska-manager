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
            'attachment' => $this->isMethod('post') ? 'nullable|array' : 'nullable|array',
            'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:10240',
            'employee_id' => 'nullable|array',
            'employee_id.*' => 'exists:employees,id',
        ];
    }
}
