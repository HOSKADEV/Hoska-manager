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
use Carbon\Carbon;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->type === 'admin') {
            $tasks = Task::with(['employee', 'project'])->latest()->get();
        } elseif (
            $user->type === 'employee' && optional($user->role)->name
            != 'accountant'
        ) {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'لا يوجد حساب موظف مرتبط بهذا المستخدم.');
            }

            $tasks = Task::with(['employee', 'project'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->get();
        } else {
            abort(403, 'نوع المستخدم غير مدعوم.');
        }

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        // لحساب الساعات نقوم بجلب المهام أولاً ثم استخدام sum مع حساب الفرق بين الوقتين
        $totalTodayHours = Task::whereDate('start_time', $today)->get()->sum(function ($task) {
            return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
        });

        $totalWeekHours = Task::whereDate('start_time', '>=', $weekStart)->get()->sum(function ($task) {
            return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
        });

        $totalMonthHours = Task::whereDate('start_time', '>=', $monthStart)->get()->sum(function ($task) {
            return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
        });

        $totalYearHours = Task::whereDate('start_time', '>=', $yearStart)->get()->sum(function ($task) {
            return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
        });

        return view('admin.tasks.index', compact(
            'tasks',
            'totalTodayHours',
            'totalWeekHours',
            'totalMonthHours',
            'totalYearHours'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->type === 'employee') {
            $employee = $user->employee;

            if (!$employee) {
                flash()->error('You are not linked to any employee.');
                return redirect()->route('admin.tasks.index');
            }

            // تحديد اسم الجدول للأعمدة لتجنب الغموض
            $projects = $employee->projects()
                ->select('projects.id', 'projects.name')
                ->pluck('name', 'id');

            if ($projects->isEmpty()) {
                flash()->error('You have no projects assigned. You cannot add tasks.');
                return redirect()->route('admin.tasks.index');
            }

            $employees = collect();
        } else {
            $projects = Project::pluck('name', 'id');
            $employees = Employee::pluck('name', 'id');
        }

        $task = new Task();

        return view('admin.tasks.create', compact('task', 'projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        $data = $request->validated();

        // فرض حالة المهمة كمبليتد عند الإنشاء
        $data['status'] = 'completed';

        $user = Auth::user();

        if ($user->type === 'employee') {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'You are not linked to an employee.');
            }

            $data['employee_id'] = $employee->id;
        } else {
            $data['employee_id'] = $request->employee_id;
        }

        $data['project_id'] = $request->project_id;

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

        if ($user->type === 'employee') {
            $employee = $user->employee;

            // تأكد أن الموظف مرتبط ومسموح له تعديل هذه المهمة فقط
            if (!$employee || $task->employee_id !== $employee->id) {
                abort(403, 'You are not authorized to edit this task.');
            }

            // جلب المشاريع المرتبطة بالموظف فقط كـ [id => name]
            $projects = $employee->projects()
                ->select('projects.id', 'projects.name')
                ->pluck('projects.name', 'projects.id');

            return view('admin.tasks.edit', compact('task', 'projects'));
        }

        // للأدمن، جلب كل الموظفين والمشاريع على شكل [id => name]
        $employees = Employee::pluck('name', 'id');
        $projects = Project::pluck('name', 'id');

        return view('admin.tasks.edit', compact('task', 'projects', 'employees'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $user = Auth::user();
        $data = $request->validated();

        // فرض حالة المهمة كمبليتد عند الإنشاء
        $data['status'] = 'completed';

        if ($user->type === 'employee') {
            $employee = $user->employee;

            // تحقق من صلاحية التعديل
            if (!$employee || $task->employee_id !== $employee->id) {
                abort(403, 'You are not authorized to update this task.');
            }

            // الموظف لا يستطيع تغيير employee_id حتى لو حاول إرسالها
            unset($data['employee_id']);
            $data['employee_id'] = $employee->id;
        } elseif ($user->type === 'admin') {
            // للأدمن يسمح بتحديد employee_id من الفورم
            $data['employee_id'] = $request->employee_id;
        } else {
            abort(403, 'User type not allowed.');
        }

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
