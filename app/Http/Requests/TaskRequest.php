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

        $taskId = $this->task->id ?? null;

        return [
            'title' => [
                'nullable',
                Rule::unique('tasks', 'title')->ignore($taskId),
            ],
            'description' => [
                'nullable',
                // Rule::unique('tasks', 'description')->ignore($taskId),
            ],
            'status' => [
                'nullable',
                Rule::in(['pending', 'in_progress', 'completed']),
            ],
            // 'due_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            // 'budget_amount' => ['required'],
        ];
    }
}
