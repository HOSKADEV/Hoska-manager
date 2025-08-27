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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->type === 'admin') {
            // Filter by project, employee, and date range
            $project_id = $request->input('project_id', 'all'); // Default to 'all'
            $employee_id = $request->input('employee_id', 'all'); // Default to 'all'
            $start_date = $request->input('start_date', now()->startOfMonth()->format('Y-m-d')); // Default to start of current month
            $end_date = $request->input('end_date', now()->endOfMonth()->format('Y-m-d')); // Default to end of current month

            $query = Task::query();

            if ($project_id !== 'all') {
                $query->where('project_id', $project_id);
            }

            if ($employee_id !== 'all') {
                $query->where('employee_id', $employee_id);
            }

            // Add date range filtering
            if ($start_date) {
                $query->whereDate('created_at', '>=', $start_date);
            }

            if ($end_date) {
                $query->whereDate('created_at', '<=', $end_date);
            }

            $tasks = $query->with(['employee', 'project'])->latest()->get();

            // statistics - applying filters to cards statistics
            $todayQuery = Task::whereDate('start_time', Carbon::today());
            $weekQuery = Task::whereDate('start_time', '>=', Carbon::now()->startOfWeek());
            $monthQuery = Task::whereDate('start_time', '>=', Carbon::now()->startOfMonth());
            $yearQuery = Task::whereDate('start_time', '>=', Carbon::now()->startOfYear());

            // Apply the same filters to statistics queries
            if ($project_id !== 'all') {
                $todayQuery->where('project_id', $project_id);
                $weekQuery->where('project_id', $project_id);
                $monthQuery->where('project_id', $project_id);
                $yearQuery->where('project_id', $project_id);
            }

            if ($employee_id !== 'all') {
                $todayQuery->where('employee_id', $employee_id);
                $weekQuery->where('employee_id', $employee_id);
                $monthQuery->where('employee_id', $employee_id);
                $yearQuery->where('employee_id', $employee_id);
            }

            // Apply date range filtering to statistics
            if ($start_date) {
                $todayQuery->whereDate('created_at', '>=', $start_date);
                $weekQuery->whereDate('created_at', '>=', $start_date);
                $monthQuery->whereDate('created_at', '>=', $start_date);
                $yearQuery->whereDate('created_at', '>=', $start_date);
            }

            if ($end_date) {
                $todayQuery->whereDate('created_at', '<=', $end_date);
                $weekQuery->whereDate('created_at', '<=', $end_date);
                $monthQuery->whereDate('created_at', '<=', $end_date);
                $yearQuery->whereDate('created_at', '<=', $end_date);
            }

            $totalTodayHours = $todayQuery->get()->sum(function ($task) {
                return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
            });

            $totalWeekHours = $weekQuery->get()->sum(function ($task) {
                return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
            });

            $totalMonthHours = $monthQuery->get()->sum(function ($task) {
                return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
            });

            $totalYearHours = $yearQuery->get()->sum(function ($task) {
                return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
            });
        } elseif ($user->type === 'employee' && optional($user->role)->name != 'accountant') {
            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'لا يوجد حساب موظف مرتبط بهذا المستخدم.');
            }

            $tasks = Task::with(['employee', 'project'])
                ->where('employee_id', $employee->id)
                ->latest('start_time')
                ->get();

            $totalTodayHours = Task::where('employee_id', $employee->id)
                ->whereDate('start_time', Carbon::today())
                ->get()
                ->sum(function ($task) {
                    return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
                });

            $totalWeekHours = Task::where('employee_id', $employee->id)
                ->whereDate('start_time', '>=', Carbon::now()->startOfWeek())
                ->get()
                ->sum(function ($task) {
                    return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
                });

            $totalMonthHours = Task::where('employee_id', $employee->id)
                ->whereDate('start_time', '>=', Carbon::now()->startOfMonth())
                ->get()
                ->sum(function ($task) {
                    return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
                });

            $totalYearHours = Task::where('employee_id', $employee->id)
                ->whereDate('start_time', '>=', Carbon::now()->startOfYear())
                ->get()
                ->sum(function ($task) {
                    return $task->end_time ? Carbon::parse($task->start_time)->floatDiffInHours(Carbon::parse($task->end_time)) : 0;
                });
        } else {
            abort(403, 'نوع المستخدم غير مدعوم.');
        }

        $projects = Project::all();
        $employees = Employee::all();

        return view('admin.tasks.index', compact(
            'tasks',
            'projects',
            'employees',
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
