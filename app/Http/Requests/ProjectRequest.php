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

        $rules = 'required|unique:projects,name';
        $rule_desc = 'required|unique:projects,description';

        if ($this->method() != 'POST') {
            $rules = 'required|unique:projects,name,' . $this->project->id;
            $rule_desc = 'required|unique:projects,description,' . $this->project->id;
        }

        return [
            'name' => $rules,
            'description' => $rule_desc,
            'total_amount' => 'required|numeric',
            'attachment' => $this->isMethod('post') ? 'required|array' : 'nullable|array',
            'attachment.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:10240',
            'employee_id' => 'required|array',
            'employee_id.*' => 'exists:employees,id',
        ];
    }
}
