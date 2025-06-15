<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
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
        $tasks = Task::with(['employee', 'project'])->get();

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
        // $data['project_id'] = $request->project_id;
        $data['employee_id'] = $request->employee_id;

        // استخراج المشاريع المرتبطة بالموظف
        $employee = Employee::with('projects')->findOrFail($request->employee_id);

        // تحقق من وجود مشاريع مرتبطة
        if ($employee->projects->isEmpty()) {
            return back()->withErrors(['employee_id' => 'هذا الموظف لا يحتوي على مشروع مرتبط.']);
        }

        // إذا كنت تريد أول مشروع فقط
        $data['project_id'] = $employee->projects->first()->id;

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
        $employees = Employee::all();
        $projects = Project::all();
        return view('admin.tasks.edit', compact('task', 'projects', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $data = $request->validated();
        // $data['project_id'] = $request->project_id;
        $data['employee_id'] = $request->employee_id;

        // استخراج المشاريع المرتبطة بالموظف
        $employee = Employee::with('projects')->findOrFail($request->employee_id);

        // تحقق من وجود مشاريع مرتبطة
        if ($employee->projects->isEmpty()) {
            return back()->withErrors(['employee_id' => 'هذا الموظف لا يحتوي على مشروع مرتبط.']);
        }

        // إذا كنت تريد أول مشروع فقط
        $data['project_id'] = $employee->projects->first()->id;


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
