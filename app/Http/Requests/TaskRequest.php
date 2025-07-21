<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

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

        // $taskId = $this->task->id ?? null;

        return [
            'title' => [
                'required',
                // Rule::unique('tasks', 'title')->ignore($taskId),
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
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            // 'budget_amount' => ['required'],
            'employee_id' => ['required', 'exists:employees,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->start_time && $this->end_time) {
                $start = Carbon::parse($this->start_time);
                $end = Carbon::parse($this->end_time);

                $hours = $start->diffInMinutes($end) / 60;

                if ($hours > 24) {
                    $validator->errors()->add('end_time', 'The duration between start and end time must not exceed 24 hours.');
                }

                if ($end->lessThanOrEqualTo($start)) {
                    $validator->errors()->add('end_time', 'End time must be after start time.');
                }
            }
        });
    }
}
