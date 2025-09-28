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

                // Calculate hours between start and end time
                $hours = $start->diffInMinutes($end) / 60;

                // Validate max 4 hours between start and end time
                if ($hours > 4) {
                    $validator->errors()->add('end_time', 'The duration between start and end time must not exceed 4 hours.');
                }

                if ($end->lessThanOrEqualTo($start)) {
                    $validator->errors()->add('end_time', 'End time must be after start time.');
                }
            }

            // Validate max 4 hours between task creation and start time
            if ($this->start_time) {
                $start = Carbon::parse($this->start_time);
                $now = Carbon::now();

                // For new tasks, check against current time
                if (!$this->route('task')) {
                    $hoursFromCreation = $now->diffInMinutes($start, false) / 60;

                    // If start time is more than 4 hours in the future
                    if ($hoursFromCreation > 4) {
                        $validator->errors()->add('start_time', 'The start time must be within 4 hours from the current time.');
                    }

                    // If start time is in the past
                    if ($hoursFromCreation < 0) {
                        $validator->errors()->add('start_time', 'The start time cannot be in the past.');
                    }
                } 
                // For existing tasks, check against created_at time
                else {
                    $task = $this->route('task');
                    $createdAt = Carbon::parse($task->created_at);
                    $hoursFromCreation = $createdAt->diffInMinutes($start, false) / 60;

                    // If start time is more than 4 hours after creation
                    if ($hoursFromCreation > 4) {
                        $validator->errors()->add('start_time', 'The start time must be within 4 hours from the task creation time.');
                    }
                }
            }
        });
    }
}
