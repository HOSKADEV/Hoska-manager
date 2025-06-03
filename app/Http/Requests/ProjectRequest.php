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
        // $rule_amount = 'required|unique:projects,total_amount';
        if ($this->method() != 'POST') {
            $rules = 'required|unique:projects,name,' . $this->project->id;
            $rule_desc = 'required|unique:projects,description,' . $this->project->id;
            // $rule_amount = 'required|unique:projects,total_amount,' . $this->project->id;
        }
        return [
            'name' => $rules,
            'description'=> $rule_desc,
            'total_amount' => 'required',
            'attachment' => 'required|file',
        ];
    }
}
