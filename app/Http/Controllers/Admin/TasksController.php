<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->type === 'admin') {
            // المستخدم أدمن، نعرض كل المهام
            $tasks = Task::with(['employee', 'project'])->latest()->get();
        } else {
            // المستخدم موظف، نعرض فقط مهامه
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'You are not linked to an employee.');
            }

            $tasks = Task::with(['employee', 'project'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->get();
        }

        return view('admin.tasks.index', compact('tasks'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $task = new Task();
        $employees = Employee::all();
        $projects = Project::all();

        return view('admin.tasks.create', compact('task', 'projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        $user = Auth::user();

        // إذا المستخدم موظف، نربطه بنفسه
        if ($user->type === 'employee') {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'You are not linked to an employee.');
            }

            $data['employee_id'] = $employee->id;
        } else {
            // إذا أدمن نأخذ القيمة من الفورم
            $data['employee_id'] = $request->employee_id;
        }

        $data['project_id'] = $request->project_id;
        $data['start_time'] = now();

        Task::create($data);

        flash()->success('Task created successfully');
        return redirect()->route('admin.tasks.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::with([
            'employee.projects.client',
            'project.client',
        ])->findOrFail($id);

        return view('admin.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $user = Auth::user();

        // الموظف يقدر يعدل فقط على مهامه
        if ($user->type === 'employee') {
            $employee = $user->employee;

            if (!$employee || $task->employee_id !== $employee->id) {
                abort(403, 'You are not authorized to edit this task.');
            }

            // لا نحتاج جلب كل الموظفين، فقط المشاريع
            $projects = Project::all();
            return view('admin.tasks.edit', compact('task', 'projects'));
        }

        // إذا كان أدمن
        $employees = Employee::all();
        $projects = Project::all();
        return view('admin.tasks.edit', compact('task', 'projects', 'employees'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($user->type === 'employee') {
            $employee = $user->employee;

            if (!$employee || $task->employee_id !== $employee->id) {
                abort(403, 'You are not authorized to update this task.');
            }

            // لا يمكنه تغيير الموظف
            $data['employee_id'] = $employee->id;
        } else {
            // أدمن يقدر يحدد الموظف من الفورم
            $data['employee_id'] = $request->employee_id;
        }

        $data['project_id'] = $request->project_id;

        $task->update($data);

        flash()->success('Task updated successfully');
        return redirect()->route('admin.tasks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        flash()->success('Task deleted successfully');
        return redirect()->route('admin.tasks.index');
    }
}
