<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
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
        //     $rule_title = 'required|unique:tasks,title';
        //     $rule_description = 'required|unique:tasks,description';
        //     $rule_status = 'required|in:pending,in_progress,completed';
        //     $rule_due_date = 'required|date';
        //     $rule_budget_amount = 'required';
        // if ($this->method() != 'POST') {
        //     $rule_title = 'required|unique:tasks,title,'  . $this->task->id;
        //     $rule_description = 'required|unique:tasks,description,'  . $this->task->id;
        //     $rule_status = 'required|in:pending,in_progress,completed,'  . $this->task->id;
        //     $rule_due_date = 'required|date,'  . $this->task->id;
        //     $rule_budget_amount = 'required,'  . $this->task->id;
        // }
        // return [
        //     'title' => $rule_title,
        //     'description' => $rule_description,
        //     "status" => $rule_status,
        //     'due_date' => $rule_due_date,
        //     'budget_amount' => $rule_budget_amount,
        // ];

        $taskId = $this->task->id ?? null;

        return [
            'title' => [
                'required',
                Rule::unique('tasks', 'title')->ignore($taskId),
            ],
            'description' => [
                'required',
                Rule::unique('tasks', 'description')->ignore($taskId),
            ],
            'status' => [
                'required',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            'due_date' => ['required', 'date'],
            'budget_amount' => ['required'],
        ];
    }
}
